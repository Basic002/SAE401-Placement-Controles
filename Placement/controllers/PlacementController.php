<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/SalleModel.php';
require_once __DIR__ . '/../models/PromotionModel.php';
require_once __DIR__ . '/../models/GroupeModel.php';
require_once __DIR__ . '/../models/MatiereModel.php';
require_once __DIR__ . '/../models/EtudiantModel.php';
require_once __DIR__ . '/../models/DevoirModel.php';
require_once __DIR__ . '/../models/PlacementModel.php';

class PlacementController extends Controller
{
    // ==================================================================
    // PAGES DU WIZARD
    // ==================================================================

    // ------------------------------------------------------------------
    // ACCUEIL PLACEMENT
    // ------------------------------------------------------------------

    /**
     * Page d'accueil du wizard de placement.
     * Réinitialise la session `$_SESSION['up']` et affiche la page principale.
     */
    public function index(): void
    {
        global $pdo;

        // Réinitialisation complète de la session de placement
        $_SESSION['up'] = [
            'date_devoir'  => '',
            'heure_debut'  => '',
            'duree'        => '',
            'combinaisons' => [],
            'placements'   => [],
        ];

        $promotions = PromotionModel::findAll($pdo);
        $salles     = SalleModel::findAllForSelect($pdo);

        $this->render('placement/util_placement.php', 'Placement des étudiants', [
            'promotions' => $promotions,
            'salles'     => $salles,
        ]);
    }

    // ------------------------------------------------------------------
    // ÉTAPE 1 — Paramètres du devoir + combinaisons promo/groupe/salle
    // ------------------------------------------------------------------

    /**
     * Étape 1 : saisie date/heure/durée du devoir.
     * Les combinaisons promo/groupe/salle/matière sont ajoutées via AJAX.
     *
     * POST (optionnel) : date_devoir, heure_debut, duree — mémorisés en session.
     */
    public function stage1(): void
    {
        global $pdo;

        // Mémorise les paramètres généraux si soumis (retour depuis étape 2)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['date_devoir'])) {
            $_SESSION['up']['date_devoir'] = trim($_POST['date_devoir'] ?? '');
            $_SESSION['up']['heure_debut'] = trim($_POST['heure_debut'] ?? '');
            $_SESSION['up']['duree']       = trim($_POST['duree']       ?? '');
        }

        $promotions = PromotionModel::findAll($pdo);
        $salles     = SalleModel::findAllForSelect($pdo);
        $sessionUp  = $_SESSION['up'] ?? [];
        $lastSelection = $sessionUp['last_selection'] ?? null;
        if (!$lastSelection && !empty($sessionUp['combinaisons'])) {
            $lastCombi = end($sessionUp['combinaisons']);
            if (is_array($lastCombi)) {
                $lastSelection = [
                    'id_promo'  => (int) ($lastCombi['id_promo'] ?? 0),
                    'id_groupe' => (int) ($lastCombi['id_groupe'] ?? 0),
                    'id_salle'  => (int) ($lastCombi['id_salle'] ?? 0),
                    'id_mat'    => (int) ($lastCombi['id_mat'] ?? 0),
                ];
            }
        }

        $erreur = null;
        if (!empty($_SESSION['up']['placement_flash_erreur'])) {
            $erreur = (string) $_SESSION['up']['placement_flash_erreur'];
            unset($_SESSION['up']['placement_flash_erreur']);
        }

        $this->render('placement/up_stage1.php', 'Étape 1 — Paramètres du devoir', [
            'promotions' => $promotions,
            'salles'     => $salles,
            'sessionUp'  => $sessionUp,
            'erreur'     => $erreur,
            'lastSelection' => $lastSelection,
        ]);
    }

    // ------------------------------------------------------------------
    // ÉTAPE 2 — Grille de placement
    // ------------------------------------------------------------------

    /**
     * Étape 2 : calcule le placement aléatoire à partir des combinaisons
     * enregistrées en session, puis affiche la grille par salle.
     *
     * POST : date_devoir, heure_debut, duree (finalisent les paramètres).
     * Le résultat est stocké dans $_SESSION['up']['placements'] pour
     * permettre les interversions avant sauvegarde.
     */
    public function stage2(): void
    {
        global $pdo;

        // Mémorise les paramètres du devoir soumis depuis l'étape 1
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['up']['date_devoir'] = trim($_POST['date_devoir'] ?? '');
            $_SESSION['up']['heure_debut'] = trim($_POST['heure_debut'] ?? '');
            $_SESSION['up']['duree']       = trim($_POST['duree']       ?? '');

            // Fallback robuste : si aucune combinaison n'est en session,
            // on la construit depuis les champs du formulaire étape 1.
            if (empty($_SESSION['up']['combinaisons'])) {
                $idPromo  = (int) ($_POST['id_promo']  ?? 0);
                $idGroupe = (int) ($_POST['id_groupe'] ?? 0);
                $idSalle  = (int) ($_POST['id_salle']  ?? 0);
                $idMat    = (int) ($_POST['id_mat']    ?? 0);

                if ($idPromo > 0 && $idSalle > 0 && $idMat > 0) {
                    $salleData = SalleModel::getPlanBySalleId($pdo, $idSalle);
                    $promo     = PromotionModel::findById($pdo, $idPromo);
                    $salle     = SalleModel::findById($pdo, $idSalle);
                    $matiere   = MatiereModel::findById($pdo, $idMat);

                    if ($salleData && $promo && $salle && $matiere) {
                        $placesEffectives = self::placesEffectivesPourSalle($salleData);
                        $nbEtudiants      = EtudiantModel::countByPromoOrGroupe($pdo, $idPromo, $idGroupe);

                        if ($nbEtudiants > 0 && $nbEtudiants <= $placesEffectives) {
                            $intercal = self::placementStepFromSalle($salleData) === 2 ? 1 : 0;
                            $labelPromo = ($promo['nom_dpt'] ?? '') . ' ' . ($promo['nom_promo'] ?? '');
                            if ($idGroupe > 0) {
                                $groupe      = GroupeModel::findById($pdo, $idGroupe);
                                $labelPromo .= ' — ' . ($groupe['nom_groupe'] ?? "Groupe {$idGroupe}");
                            }

                            $_SESSION['up']['combinaisons'] = [[
                                'id_promo'    => $idPromo,
                                'id_groupe'   => $idGroupe,
                                'id_salle'    => $idSalle,
                                'id_mat'      => $idMat,
                                'intercal'    => $intercal,
                                'label_promo' => $labelPromo,
                                'nom_salle'   => $salle['nom_salle'] ?? "Salle {$idSalle}",
                                'nom_mat'     => $matiere['nom_mat'] ?? "Matière {$idMat}",
                                'nb_etud'     => $nbEtudiants,
                            ]];
                        } elseif ($nbEtudiants > $placesEffectives) {
                            $_SESSION['up']['placement_flash_erreur'] = sprintf(
                                'Capacité insuffisante pour cette salle : %d étudiant(s) et seulement %d place(s) utilisables (plan + espacement). Choisissez une autre salle ou réduisez le groupe.',
                                $nbEtudiants,
                                $placesEffectives
                            );
                            header('Location: index.php?action=placement_stage1');
                            exit();
                        }
                    }
                }
            }
        }

        $combinaisons = $_SESSION['up']['combinaisons'] ?? [];
        if (empty($combinaisons)) {
            header('Location: index.php?action=placement_stage1');
            exit();
        }

        // Collecte les identifiants de salles uniques
        $sallesUniques = array_unique(array_column($combinaisons, 'id_salle'));

        // Calcule le placement pour chaque salle
        $placements = [];
        foreach ($sallesUniques as $idSalle) {
            $idsEtudiants = [];
            $salleData    = SalleModel::getPlanBySalleId($pdo, (int) $idSalle);
            if (!$salleData) {
                continue;
            }

            foreach ($combinaisons as $combi) {
                if ((int) $combi['id_salle'] === (int) $idSalle) {
                    $ids = EtudiantModel::getIdsByPromoOrGroupe(
                        $pdo,
                        (int) $combi['id_promo'],
                        (int) $combi['id_groupe']
                    );
                    $idsEtudiants = array_merge($idsEtudiants, $ids);
                }
            }

            $placesMax = self::placesEffectivesPourSalle($salleData);
            if (count($idsEtudiants) > $placesMax) {
                $_SESSION['up']['placement_flash_erreur'] = sprintf(
                    'Capacité insuffisante pour la salle « %s » : %d étudiant(s) pour %d place(s) avec espacement. Revenez à l’étape 1 pour changer de salle ou de groupe.',
                    $salleData['nom_salle'] ?? '',
                    count($idsEtudiants),
                    $placesMax
                );
                header('Location: index.php?action=placement_stage1');
                exit();
            }

            // step = 2 : une place sur deux en colonne (amphi / intercalage)
            $step   = self::placementStepFromSalle($salleData);
            $result = self::calculerPlacement($pdo, (int) $idSalle, $idsEtudiants, $step);

            $placements[$idSalle] = array_map(fn($p) => [
                'place_x'     => $p[0],
                'place_y'     => $p[1],
                'id_etudiant' => $p[2],
            ], $result);
        }

        $_SESSION['up']['placements'] = $placements;

        // Construit les données de grille pour la vue
        $grilles = [];
        foreach ($sallesUniques as $idSalle) {
            $salleData = SalleModel::getPlanBySalleId($pdo, (int) $idSalle);
            if ($salleData) {
                $grilles[$idSalle] = [
                    'salle'     => $salleData,
                    'grille'    => $this->parserGrille($salleData['donnee']),
                    'placement' => $placements[$idSalle] ?? [],
                ];
            }
        }

        // Charge les noms des étudiants pour l'affichage de la grille
        $nomsEtudiants = [];
        foreach ($placements as $places) {
            foreach ($places as $p) {
                $id = (int) $p['id_etudiant'];
                if (!isset($nomsEtudiants[$id])) {
                    $nomsEtudiants[$id] = EtudiantModel::getNomPrenomById($pdo, $id);
                }
            }
        }

        $this->render('placement/up_stage2.php', 'Étape 2 — Grille de placement', [
            'grilles'       => $grilles,
            'nomsEtudiants' => $nomsEtudiants,
            'combinaisons'  => $combinaisons,
            'sessionUp'     => $_SESSION['up'],
        ]);
    }

    // ------------------------------------------------------------------
    // ÉTAPE 3 — Sauvegarde en BDD + liens d'export
    // ------------------------------------------------------------------

    /**
     * Étape 3 : enregistre le devoir, les associations et les placements
     * en base de données, puis affiche les liens d'export PDF.
     */
    public function stage3(): void
    {
        global $pdo;

        $sessionUp = $_SESSION['up'] ?? [];

        if (empty($sessionUp['combinaisons']) || empty($sessionUp['placements'])) {
            header('Location: index.php?action=placement_stage1');
            exit();
        }

        // --- Nom du devoir : matière(s) + date ---
        $nomsMatiere = array_unique(array_column($sessionUp['combinaisons'], 'nom_mat'));
        $nomDevoir   = implode('/', $nomsMatiere) . ' ' . ($sessionUp['date_devoir'] ?? '');

        // --- Identifiant de la première matière (champ BDD unique) ---
        $idMat = (int) ($sessionUp['combinaisons'][0]['id_mat'] ?? 0) ?: null;

        // Formats attendus par DevoirModel::create : 'HH:MM:SS'
        $heureDevoir = ($sessionUp['heure_debut'] ?? '00:00') . ':00';
        $dureeDevoir = ($sessionUp['duree']       ?? '00:00') . ':00';

        // --- Création du devoir en BDD ---
        $idDevoir = DevoirModel::create(
            $pdo,
            $nomDevoir,
            $sessionUp['date_devoir'] ?? date('Y-m-d'),
            $heureDevoir,
            $dureeDevoir,
            $idMat
        );

        // --- Associations salle / groupe / promo ---
        $sallesSaved = [];
        foreach ($sessionUp['combinaisons'] as $combi) {
            $idSalle  = (int) $combi['id_salle'];
            $idPromo  = (int) $combi['id_promo'];
            $idGroupe = (int) $combi['id_groupe'];

            if (!in_array($idSalle, $sallesSaved, true)) {
                DevoirModel::addSalle($pdo, $idDevoir, $idSalle);
                $sallesSaved[] = $idSalle;
            }

            if ($idGroupe === 0) {
                DevoirModel::addPromo($pdo, $idDevoir, $idPromo);
            } else {
                DevoirModel::addGroupe($pdo, $idDevoir, $idGroupe);
            }
        }

        // --- Enregistrement des placements ---
        foreach ($sessionUp['placements'] as $idSalle => $places) {
            PlacementModel::saveBatch($pdo, $idDevoir, (int) $idSalle, $places);
        }

        // Stocke l'id pour l'étape 4
        $_SESSION['up']['id_devoir'] = $idDevoir;

        // Données des salles pour la vue (noms + id)
        $sallesInfo = [];
        foreach ($sallesSaved as $idSalle) {
            $s = SalleModel::findById($pdo, $idSalle);
            if ($s) {
                $sallesInfo[] = $s;
            }
        }

        $this->render('placement/up_stage3.php', 'Étape 3 — Export', [
            'idDevoir'     => $idDevoir,
            'sallesInfo'   => $sallesInfo,
            'combinaisons' => $sessionUp['combinaisons'],
            'nomDevoir'    => $nomDevoir,
        ]);
    }

    // ------------------------------------------------------------------
    // ÉTAPE 4 — Confirmation finale
    // ------------------------------------------------------------------

    /**
     * Étape 4 : page de confirmation après enregistrement.
     * Nettoie la session de placement.
     */
    public function stage4(): void
    {
        $idDevoir = (int) ($_SESSION['up']['id_devoir'] ?? 0);

        if ($idDevoir === 0) {
            header('Location: index.php?action=util_placement');
            exit();
        }

        unset($_SESSION['up']);

        $this->render('placement/up_stage4.php', 'Placement terminé', [
            'idDevoir' => $idDevoir,
        ]);
    }

    // ==================================================================
    // AJAX — COMBINAISONS (étape 1)
    // ==================================================================

    /**
     * AJAX POST — Ajoute une combinaison promo/groupe/salle/matière.
     *
     * POST : idPromo, idGroupe (0 = toute la promo), idSalle, idMat
     * JSON : { ok, message?, combinaisons }
     */
    public function ajaxAddCombi(): void
    {
        global $pdo;

        if (!isset($_SESSION['login'])) {
            $this->jsonResponse(['ok' => false, 'message' => 'Non authentifié'], 403);
        }

        $idPromo  = (int) ($_POST['idPromo']  ?? 0);
        $idGroupe = (int) ($_POST['idGroupe'] ?? 0);
        $idSalle  = (int) ($_POST['idSalle']  ?? 0);
        $idMat    = (int) ($_POST['idMat']    ?? 0);

        if ($idPromo === 0 || $idSalle === 0 || $idMat === 0) {
            $this->jsonResponse(['ok' => false, 'message' => 'Paramètres manquants.']);
        }

        if (!isset($_SESSION['up'])) {
            $_SESSION['up'] = ['combinaisons' => [], 'placements' => []];
        }

        $combinaisons = $_SESSION['up']['combinaisons'] ?? [];

        // Vérifie doublon : même promo ET (même groupe, ou l'un couvre l'autre)
        foreach ($combinaisons as $c) {
            if ((int) $c['id_promo'] === $idPromo &&
                ((int) $c['id_groupe'] === $idGroupe
                    || $idGroupe === 0
                    || (int) $c['id_groupe'] === 0)
            ) {
                $this->jsonResponse([
                    'ok'           => false,
                    'message'      => 'Cette promotion/groupe est déjà dans la liste.',
                    'combinaisons' => $combinaisons,
                ]);
            }
        }

        // Récupère les données de la salle
        $salleData = SalleModel::getPlanBySalleId($pdo, $idSalle);
        if (!$salleData) {
            $this->jsonResponse(['ok' => false, 'message' => 'Salle introuvable.']);
        }

        $placesEffectives = self::placesEffectivesPourSalle($salleData);
        $intercal         = self::placementStepFromSalle($salleData) === 2 ? 1 : 0;

        // Nombre d'étudiants déjà affectés à cette salle
        $nbDejaAffectes = 0;
        foreach ($combinaisons as $c) {
            if ((int) $c['id_salle'] === $idSalle) {
                $nbDejaAffectes += (int) ($c['nb_etud'] ?? 0);
            }
        }

        $nbEtudiants = EtudiantModel::countByPromoOrGroupe($pdo, $idPromo, $idGroupe);

        if (($nbDejaAffectes + $nbEtudiants) > $placesEffectives) {
            $this->jsonResponse([
                'ok'           => false,
                'message'      => sprintf(
                    'Capacité dépassée : %d étudiants pour %d places disponibles.',
                    $nbDejaAffectes + $nbEtudiants,
                    $placesEffectives
                ),
                'combinaisons' => $combinaisons,
            ]);
        }

        // Labels pour l'affichage
        $promo   = PromotionModel::findById($pdo, $idPromo);
        $salle   = SalleModel::findById($pdo, $idSalle);
        $matiere = MatiereModel::findById($pdo, $idMat);

        $labelPromo = $promo
            ? (($promo['nom_dpt'] ?? '') . ' ' . ($promo['nom_promo'] ?? ''))
            : "Promo {$idPromo}";

        if ($idGroupe > 0) {
            $groupe      = GroupeModel::findById($pdo, $idGroupe);
            $labelPromo .= ' — ' . ($groupe['nom_groupe'] ?? "Groupe {$idGroupe}");
        }

        $combinaisons[] = [
            'id_promo'    => $idPromo,
            'id_groupe'   => $idGroupe,
            'id_salle'    => $idSalle,
            'id_mat'      => $idMat,
            'intercal'    => $intercal,
            'label_promo' => $labelPromo,
            'nom_salle'   => $salle['nom_salle'] ?? "Salle {$idSalle}",
            'nom_mat'     => $matiere['nom_mat'] ?? "Matière {$idMat}",
            'nb_etud'     => $nbEtudiants,
        ];

        $_SESSION['up']['combinaisons'] = $combinaisons;
        $_SESSION['up']['last_selection'] = [
            'id_promo'  => $idPromo,
            'id_groupe' => $idGroupe,
            'id_salle'  => $idSalle,
            'id_mat'    => $idMat,
        ];

        $this->jsonResponse(['ok' => true, 'combinaisons' => $combinaisons]);
    }

    /**
     * AJAX POST — Supprime une combinaison par son index.
     *
     * POST : index (int)
     * JSON : { ok, combinaisons }
     */
    public function ajaxSupprCombi(): void
    {
        if (!isset($_SESSION['login'])) {
            $this->jsonResponse(['ok' => false], 403);
        }

        $index        = (int) ($_POST['index'] ?? -1);
        $combinaisons = $_SESSION['up']['combinaisons'] ?? [];

        if ($index >= 0 && isset($combinaisons[$index])) {
            array_splice($combinaisons, $index, 1);
            $_SESSION['up']['combinaisons'] = array_values($combinaisons);
            if (!empty($combinaisons)) {
                $last = end($combinaisons);
                $_SESSION['up']['last_selection'] = [
                    'id_promo'  => (int) ($last['id_promo'] ?? 0),
                    'id_groupe' => (int) ($last['id_groupe'] ?? 0),
                    'id_salle'  => (int) ($last['id_salle'] ?? 0),
                    'id_mat'    => (int) ($last['id_mat'] ?? 0),
                ];
            } else {
                unset($_SESSION['up']['last_selection']);
            }
        }

        $this->jsonResponse(['ok' => true, 'combinaisons' => array_values($combinaisons)]);
    }

    /**
     * AJAX GET/POST — Retourne la liste courante des combinaisons en session.
     *
     * JSON : { ok, combinaisons }
     */
    public function ajaxAfficheCombi(): void
    {
        if (!isset($_SESSION['login'])) {
            $this->jsonResponse(['ok' => false], 403);
        }

        $this->jsonResponse([
            'ok'           => true,
            'combinaisons' => $_SESSION['up']['combinaisons'] ?? [],
        ]);
    }

    /**
     * AJAX POST — Retourne les informations de capacité pour une salle/promo/groupe.
     * Utilisé dans l'étape 1 pour informer l'utilisateur avant d'ajouter une combinaison.
     *
     * POST : idSalle, idPromo, idGroupe
     * JSON : { ok, capacite, places_effectives, nb_etud, nb_deja_affectes, places_restantes }
     */
    public function ajaxInfoDevoir(): void
    {
        global $pdo;

        if (!isset($_SESSION['login'])) {
            $this->jsonResponse(['ok' => false], 403);
        }

        $idSalle  = (int) ($_POST['idSalle']  ?? 0);
        $idPromo  = (int) ($_POST['idPromo']  ?? 0);
        $idGroupe = (int) ($_POST['idGroupe'] ?? 0);

        if ($idSalle === 0 || $idPromo === 0) {
            $this->jsonResponse(['ok' => false, 'message' => 'Paramètres manquants.']);
        }

        $salleData = SalleModel::getPlanBySalleId($pdo, $idSalle);
        if (!$salleData) {
            $this->jsonResponse(['ok' => false, 'message' => 'Salle introuvable.']);
        }

        $capacite          = (int) ($salleData['capacite'] ?? 0);
        $placesEffectives  = self::placesEffectivesPourSalle($salleData);
        $nbEtudiants       = EtudiantModel::countByPromoOrGroupe($pdo, $idPromo, $idGroupe);

        $nbDejaAffectes = 0;
        foreach ($_SESSION['up']['combinaisons'] ?? [] as $c) {
            if ((int) $c['id_salle'] === $idSalle) {
                $nbDejaAffectes += (int) ($c['nb_etud'] ?? 0);
            }
        }

        $this->jsonResponse([
            'ok'               => true,
            'capacite'         => $capacite,
            'places_effectives' => $placesEffectives,
            'nb_etud'          => $nbEtudiants,
            'nb_deja_affectes' => $nbDejaAffectes,
            'places_restantes' => max(0, $placesEffectives - $nbDejaAffectes),
        ]);
    }

    // ==================================================================
    // AJAX — INTERVERSION (étape 2)
    // ==================================================================

    /**
     * AJAX POST — Intervertit deux étudiants dans la session de placement.
     * L'interversion est enregistrée en session ; la BDD n'est pas modifiée
     * avant la sauvegarde définitive (étape 3).
     *
     * POST : etu1, etu2  (id_etudiant)
     * JSON : { ok }
     */
    public function ajaxIntervertir(): void
    {
        if (!isset($_SESSION['login'])) {
            $this->jsonResponse(['ok' => false], 403);
        }

        $etu1 = (int) ($_POST['etu1'] ?? 0);
        $etu2 = (int) ($_POST['etu2'] ?? 0);

        if ($etu1 === 0 || $etu2 === 0 || $etu1 === $etu2) {
            $this->jsonResponse(['ok' => false, 'message' => 'Identifiants invalides.']);
        }

        $placements = $_SESSION['up']['placements'] ?? [];

        // Localise les deux étudiants (salle + indice dans le tableau)
        $loc1 = null;
        $loc2 = null;

        foreach ($placements as $idSalle => $places) {
            foreach ($places as $idx => $p) {
                if ((int) $p['id_etudiant'] === $etu1) {
                    $loc1 = ['salle' => $idSalle, 'idx' => $idx];
                }
                if ((int) $p['id_etudiant'] === $etu2) {
                    $loc2 = ['salle' => $idSalle, 'idx' => $idx];
                }
            }
        }

        if ($loc1 === null || $loc2 === null) {
            $this->jsonResponse(['ok' => false, 'message' => 'Étudiant(s) introuvable(s) dans le placement.']);
        }

        // Échange les positions (place_x, place_y) et les id_etudiant
        $p1 = $placements[$loc1['salle']][$loc1['idx']];
        $p2 = $placements[$loc2['salle']][$loc2['idx']];

        $placements[$loc1['salle']][$loc1['idx']] = [
            'place_x'     => $p2['place_x'],
            'place_y'     => $p2['place_y'],
            'id_etudiant' => $etu1,
        ];
        $placements[$loc2['salle']][$loc2['idx']] = [
            'place_x'     => $p1['place_x'],
            'place_y'     => $p1['place_y'],
            'id_etudiant' => $etu2,
        ];

        $_SESSION['up']['placements'] = $placements;

        $this->jsonResponse(['ok' => true]);
    }

    // ==================================================================
    // UTILITAIRES PRIVÉS
    // ==================================================================

    /**
     * Parse la chaîne `donnee` du plan en tableau 2D.
     * Valeurs : 0=couloir, 1=place normale, 2=place PMR, 3=inexistante.
     *
     * @param string $donnee
     * @return array<int, array<int, int>>
     */
    private function parserGrille(string $donnee): array
    {
        $grille = [];
        foreach (explode('-', $donnee) as $ligne) {
            if ($ligne !== '') {
                $grille[] = array_map('intval', str_split($ligne));
            }
        }
        return $grille;
    }

    // ==================================================================
    // ALGORITHME DE PLACEMENT (statiques — conservés de l'ancienne version)
    // ==================================================================

    /**
     * Parse la chaîne de caractères de la BDD pour recréer la grille 2D.
     *
     * @param string $donneePlan
     * @return array<int, array<int, string>>
     */
    private static function parsePlanSalle(string $donneePlan): array
    {
        $structure = [];
        foreach (explode('-', $donneePlan) as $ligne) {
            if ($ligne !== '') {
                $structure[] = str_split($ligne);
            }
        }
        return $structure;
    }

    /**
     * $step = 2 : une colonne sur deux (amphi ou salle avec intercalage en BDD).
     */
    private static function placementStepFromSalle(array $salleData): int
    {
        $type     = strtoupper((string) ($salleData['type_salle'] ?? ''));
        $intercal = (int) ($salleData['intercal'] ?? 1);
        if ($type === 'AMPHI' || $intercal === 1) {
            return 2;
        }
        return 1;
    }

    /**
     * Places utilisables pour le contrôle : grille + espacement, plafonnées par plan.capacite_max.
     */
    public static function placesEffectivesPourSalle(array $salleData): int
    {
        $step      = self::placementStepFromSalle($salleData);
        $structure = self::parsePlanSalle($salleData['donnee'] ?? '');
        $places    = self::getPlacesDisponibles($structure, $step);
        $n         = count($places['standard']) + count($places['pmr']);
        $capMax    = (int) ($salleData['capacite_max'] ?? 0);
        if ($capMax > 0) {
            $n = min($n, $capMax);
        }
        return $n;
    }

    /**
     * Retourne les places libres (en respectant l'espacement $step).
     *
     * @param array<int, array<int, string>> $structure
     * @param int                            $step       Intervalle entre deux places utilisées
     * @return array{ standard: list<array{int,int}>, pmr: list<array{int,int}> }
     */
    private static function getPlacesDisponibles(array $structure, int $step): array
    {
        $placesStandard = [];
        $placesPmr      = [];

        $nbRangees  = count($structure);
        if ($nbRangees === 0) {
            return ['standard' => [], 'pmr' => []];
        }
        $nbColonnes = count($structure[0]);
        $derniere   = $structure[$nbRangees - 1];

        // Colonnes utilisables : pour $step >= 2, pas de « décalage » vers une colonne
        // voisine (sinon deux étudiants se retrouvent côte à côte).
        $colonnesUtilisables = [];
        if ($step >= 2) {
            for ($c = 0; $c < $nbColonnes; $c += $step) {
                $cell = $derniere[$c] ?? '0';
                if ((int) $cell !== 0) {
                    $colonnesUtilisables[] = $c;
                }
            }
        } else {
            for ($c = 0; $c < $nbColonnes; $c += $step) {
                if (isset($derniere[$c]) && (int) $derniere[$c] !== 0) {
                    $colonnesUtilisables[] = $c;
                } else {
                    $cTemp = $c + 1;
                    while ($cTemp < $nbColonnes && (int) ($derniere[$cTemp] ?? '0') === 0) {
                        $cTemp++;
                    }
                    if ($cTemp < $nbColonnes) {
                        $colonnesUtilisables[] = $cTemp;
                        $c = $cTemp;
                    }
                }
            }
        }

        // Parcourt la salle du fond vers le bureau
        for ($r = $nbRangees - 1; $r >= 0; $r--) {
            foreach ($colonnesUtilisables as $c) {
                $cell = $structure[$r][$c] ?? '0';
                if ((int) $cell === 1) {
                    $placesStandard[] = [$r, $c];
                } elseif ((int) $cell === 2) {
                    $placesPmr[] = [$r, $c];
                }
            }
        }

        return ['standard' => $placesStandard, 'pmr' => $placesPmr];
    }

    /**
     * Calcule le placement final sans utiliser $_SESSION.
     * Retourne un tableau de [ place_x, place_y, id_etudiant ].
     *
     * Règles de priorité :
     *  1. PMR → places PMR (type 2) ; débordement → places standard de début
     *  2. Demi-groupe / tiers-temps → premières rangées (places triées par rang croissant)
     *  3. Standard → mélange aléatoire des places restantes
     *
     * @param PDO      $pdo
     * @param int      $idSalle
     * @param list<int> $ids_etudiants
     * @param int      $step   2=intercalation activée, 1=toutes les places
     * @return list<array{int, int, int}>
     */
    public static function calculerPlacement(PDO $pdo, int $idSalle, array $ids_etudiants, int $step = 2): array
    {
        if (empty($ids_etudiants)) {
            return [];
        }

        $salleData = SalleModel::getPlanBySalleId($pdo, $idSalle);
        $structure = self::parsePlanSalle($salleData['donnee']);

        $places          = self::getPlacesDisponibles($structure, $step);
        $placesStandard  = $places['standard'];
        $placesPmr       = $places['pmr'];

        // Catégorise les étudiants selon leurs contraintes
        $etuPmr        = [];
        $etuPremierRang = []; // demigr = 1 : placés en début de salle
        $etuStandard   = [];

        $etudiantsInfos = EtudiantModel::getCaracteristiques($pdo, $ids_etudiants);

        foreach ($etudiantsInfos as $etu) {
            if ($etu['mob_reduite'] == 1) {
                $etuPmr[] = $etu['id_etudiant'];
            } elseif ($etu['demigr'] == 1) {
                $etuPremierRang[] = $etu['id_etudiant'];
            } else {
                $etuStandard[] = $etu['id_etudiant'];
            }
        }

        shuffle($etuPmr);
        shuffle($etuPremierRang);
        shuffle($etuStandard);

        $placement = [];

        // 1. PMR → places PMR ; débordement → places standard (début)
        foreach ($etuPmr as $idEtu) {
            if (!empty($placesPmr)) {
                $place       = array_shift($placesPmr);
                $placement[] = [$place[0], $place[1], $idEtu];
            } elseif (!empty($placesStandard)) {
                $place       = array_shift($placesStandard);
                $placement[] = [$place[0], $place[1], $idEtu];
            }
        }

        // 2. Demi-groupe / tiers-temps → premières rangées (rang le plus petit = le plus proche du bureau)
        usort($placesStandard, fn($a, $b) => $a[0] <=> $b[0]);
        foreach ($etuPremierRang as $idEtu) {
            if (!empty($placesStandard)) {
                $place       = array_shift($placesStandard);
                $placement[] = [$place[0], $place[1], $idEtu];
            }
        }

        // 3. Standard → ordre aléatoire
        shuffle($placesStandard);
        foreach ($etuStandard as $idEtu) {
            if (!empty($placesStandard)) {
                $place       = array_shift($placesStandard);
                $placement[] = [$place[0], $place[1], $idEtu];
            }
        }

        return $placement;
    }
}
