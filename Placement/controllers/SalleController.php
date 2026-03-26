<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/SalleModel.php';
require_once __DIR__ . '/../models/BatimentModel.php';
require_once __DIR__ . '/../models/DepartementModel.php';

class SalleController extends Controller
{
    // ------------------------------------------------------------------
    // LISTE
    // ------------------------------------------------------------------

    /**
     * Affiche la liste des salles avec les contrôles de gestion (admin).
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $salles = SalleModel::findAll($pdo);

        $this->render('salle/gest_salle.php', 'Gestion des salles', [
            'salles' => $salles,
        ]);
    }

    // ------------------------------------------------------------------
    // CRÉATION (wizard 4 étapes)
    // ------------------------------------------------------------------

    /**
     * Wizard de création de salle.
     * ?etape=1|2|3|4  (défaut : 1)
     *
     * Étape 1 : Saisie nom, étage, bâtiment, département, intercal
     * Étape 2 : Saisie des dimensions (nb_col, nb_rang)
     * Étape 3 : Dessin de la grille (type de chaque case)
     * Étape 4 : Confirmation + sauvegarde en BDD
     */
    public function creer(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $etape  = max(1, (int) ($_GET['etape'] ?? 1));
        $erreur = null;

        // --- ÉTAPE 1 → mémorise les métadonnées en session ---
        if ($etape === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $nomSalle = trim($_POST['nom_salle'] ?? '');
            $etage    = (int) ($_POST['etage']   ?? 0);
            $idBat    = (int) ($_POST['id_bat']  ?? 0);
            $idDpt    = (int) ($_POST['id_dpt']  ?? 0);
            $intercal = (int) ($_POST['intercal'] ?? 1);

            if ($nomSalle === '' || $idBat === 0 || $idDpt === 0) {
                $erreur = 'Tous les champs sont obligatoires.';
            } else {
                $_SESSION['cs_salle'] = [
                    'nom_salle' => $nomSalle,
                    'etage'     => $etage,
                    'id_bat'    => $idBat,
                    'id_dpt'    => $idDpt,
                    'intercal'  => $intercal,
                ];
                header('Location: index.php?action=crea_salle&etape=2');
                exit();
            }
        }

        // --- ÉTAPE 2 → mémorise les dimensions en session ---
        if ($etape === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $nbCol  = max(1, (int) ($_POST['nb_col']  ?? 0));
            $nbRang = max(1, (int) ($_POST['nb_rang'] ?? 0));

            if ($nbCol === 0 || $nbRang === 0) {
                $erreur = 'Les dimensions doivent être supérieures à 0.';
            } else {
                $_SESSION['cs_salle']['nb_col']  = $nbCol;
                $_SESSION['cs_salle']['nb_rang'] = $nbRang;
                header('Location: index.php?action=crea_salle&etape=3');
                exit();
            }
        }

        // --- ÉTAPE 3 → mémorise la grille (donnee) en session ---
        if ($etape === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $donnee = trim($_POST['donnee'] ?? '');
            if ($donnee === '') {
                $erreur = 'La grille ne peut pas être vide.';
            } else {
                $_SESSION['cs_salle']['donnee'] = $donnee;
                // Calcule la capacité = nombre de cases "1" ou "2"
                $capacite = substr_count($donnee, '1') + substr_count($donnee, '2');
                $_SESSION['cs_salle']['capacite'] = $capacite;
                header('Location: index.php?action=crea_salle&etape=4');
                exit();
            }
        }

        // --- ÉTAPE 4 → sauvegarde définitive en BDD ---
        if ($etape === 4 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_SESSION['cs_salle'] ?? [];
            if (empty($data)) {
                header('Location: index.php?action=crea_salle&etape=1');
                exit();
            }
            SalleModel::create(
                $pdo,
                $data['nom_salle'],
                $data['etage'],
                $data['id_bat'],
                $data['id_dpt'],
                $data['donnee'],
                $data['capacite'],
                $data['intercal']
            );
            unset($_SESSION['cs_salle']);
            header('Location: index.php?action=gest_salle');
            exit();
        }

        // Données nécessaires aux vues du wizard
        $batiments    = BatimentModel::findAll($pdo);
        $departements = DepartementModel::findAll($pdo);
        $sessionSalle = $_SESSION['cs_salle'] ?? [];

        $vues = [
            1 => 'salle/cs_stage1.php',
            2 => 'salle/cs_stage2.php',
            3 => 'salle/cs_stage3.php',
            4 => 'salle/cs_stage4.php',
        ];

        $this->render($vues[$etape], "Créer une salle — Étape {$etape}", [
            'etape'        => $etape,
            'batiments'    => $batiments,
            'departements' => $departements,
            'sessionSalle' => $sessionSalle,
            'erreur'       => $erreur,
        ]);
    }

    // ------------------------------------------------------------------
    // MODIFICATION (wizard 6 étapes)
    // ------------------------------------------------------------------

    /**
     * Wizard de modification de salle.
     * ?id_salle=<id>&etape=1|2|...|6  (défaut : 1)
     *
     * Étape 1 : Sélection de la salle à modifier
     * Étape 2 : Modification des métadonnées
     * Étape 3 : Modification des dimensions
     * Étape 4 : Modification de la grille
     * Étape 5 : Prévisualisation
     * Étape 6 : Confirmation + sauvegarde
     */
    public function modifier(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $etape    = max(1, (int) ($_GET['etape']    ?? 1));
        $idSalle  = (int) ($_GET['id_salle'] ?? ($_SESSION['ms_salle']['id_salle'] ?? 0));
        $erreur   = null;

        // --- ÉTAPE 1 → sélection de la salle ---
        if ($etape === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $idSalle = (int) ($_POST['id_salle'] ?? 0);
            if ($idSalle === 0) {
                $erreur = 'Veuillez sélectionner une salle.';
            } else {
                $salle = SalleModel::getPlanBySalleId($pdo, $idSalle);
                $_SESSION['ms_salle'] = $salle;
                header('Location: index.php?action=modif_salle&etape=2');
                exit();
            }
        }

        // --- ÉTAPE 2 → modification des métadonnées ---
        if ($etape === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $nomSalle = trim($_POST['nom_salle'] ?? '');
            $etageVal = (int) ($_POST['etage']   ?? 0);
            $idBat    = (int) ($_POST['id_bat']  ?? 0);
            $idDpt    = (int) ($_POST['id_dpt']  ?? 0);
            $intercal = (int) ($_POST['intercal'] ?? 1);

            if ($nomSalle === '' || $idBat === 0 || $idDpt === 0) {
                $erreur = 'Tous les champs sont obligatoires.';
            } else {
                $_SESSION['ms_salle'] = array_merge($_SESSION['ms_salle'] ?? [], [
                    'nom_salle' => $nomSalle,
                    'etage'     => $etageVal,
                    'id_bat'    => $idBat,
                    'id_dpt'    => $idDpt,
                    'intercal'  => $intercal,
                ]);
                header('Location: index.php?action=modif_salle&etape=3');
                exit();
            }
        }

        // --- ÉTAPES 3-5 : modification grille (voir vues ms_stage3 à ms_stage5) ---
        if (in_array($etape, [3, 4, 5], true) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // On transmet les données de grille en session via la vue
            $_SESSION['ms_salle'] = array_merge(
                $_SESSION['ms_salle'] ?? [],
                array_filter($_POST, fn($k) => in_array($k, ['nb_col', 'nb_rang', 'donnee']), ARRAY_FILTER_USE_KEY)
            );
            $prochaine = $etape + 1;
            header("Location: index.php?action=modif_salle&etape={$prochaine}");
            exit();
        }

        // --- ÉTAPE 6 → sauvegarde définitive ---
        if ($etape === 6 && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_SESSION['ms_salle'] ?? [];
            if (empty($data) || empty($data['id_salle'])) {
                header('Location: index.php?action=modif_salle&etape=1');
                exit();
            }
            $capacite = substr_count($data['donnee'] ?? '', '1')
                      + substr_count($data['donnee'] ?? '', '2');

            SalleModel::update(
                $pdo,
                (int) $data['id_salle'],
                $data['nom_salle'],
                (int) $data['etage'],
                (int) $data['id_bat'],
                (int) $data['id_dpt'],
                $capacite,
                (int) $data['intercal']
            );
            if (!empty($data['donnee'])) {
                SalleModel::updatePlan($pdo, (int) $data['id_plan'], $data['donnee']);
            }
            unset($_SESSION['ms_salle']);
            header('Location: index.php?action=gest_salle');
            exit();
        }

        $salles       = SalleModel::findAll($pdo);
        $batiments    = BatimentModel::findAll($pdo);
        $departements = DepartementModel::findAll($pdo);
        $sessionSalle = $_SESSION['ms_salle'] ?? [];

        $vues = [
            1 => 'salle/ms_stage1.php',
            2 => 'salle/ms_stage2.php',
            3 => 'salle/ms_stage3.php',
            4 => 'salle/ms_stage4.php',
            5 => 'salle/ms_stage5.php',
            6 => 'salle/ms_stage6.php',
        ];

        $this->render($vues[$etape], "Modifier une salle — Étape {$etape}", [
            'etape'        => $etape,
            'salles'       => $salles,
            'batiments'    => $batiments,
            'departements' => $departements,
            'sessionSalle' => $sessionSalle,
            'erreur'       => $erreur,
        ]);
    }

    // ------------------------------------------------------------------
    // VISUALISATION
    // ------------------------------------------------------------------

    /**
     * Affiche le plan d'une salle (avec ou sans placement associé à un devoir).
     * GET: ?id_salle=<id>[&id_devoir=<id>]
     */
    public function visualiser(): void
    {
        global $pdo;

        $idSalle  = (int) ($_GET['id_salle']  ?? 0);
        $idDevoir = (int) ($_GET['id_devoir'] ?? 0);

        if ($idSalle === 0) {
            header('Location: index.php?action=gest_salle');
            exit();
        }

        $salle = SalleModel::getPlanBySalleId($pdo, $idSalle);
        if (!$salle) {
            header('Location: index.php?action=gest_salle');
            exit();
        }

        // Placements existants pour ce devoir (vide si pas de devoir sélectionné)
        $placements = [];
        if ($idDevoir > 0) {
            require_once __DIR__ . '/../models/PlacementModel.php';
            $placements = PlacementModel::findByDevoirAndSalle($pdo, $idDevoir, $idSalle);
        }

        // Parse la grille pour la vue
        $grille = $this->parserGrille($salle['donnee']);

        $this->render('salle/visu_salle.php', 'Visualisation — ' . $salle['nom_salle'], [
            'salle'      => $salle,
            'grille'     => $grille,
            'placements' => $placements,
            'idDevoir'   => $idDevoir,
        ]);
    }

    /**
     * Parse la chaîne `donnee` du plan en tableau 2D.
     * Valeurs : 0=couloir, 1=place normale, 2=place PMR, 3=inexistante
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
}
