<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/PromotionModel.php';
require_once __DIR__ . '/../models/GroupeModel.php';
require_once __DIR__ . '/../models/EtudiantModel.php';
require_once __DIR__ . '/../models/DepartementModel.php';

class PromotionController extends Controller
{
    /**
     * Point d'entrée unique : liste des promos + CRUD + import CSV.
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $erreur = null;
        $succes = null;

        // --- SUPPRESSION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'delete') {
            $idPromo = (int) ($_POST['id_promo'] ?? 0);
            if ($idPromo > 0) {
                PromotionModel::delete($pdo, $idPromo);
                $succes = 'Promotion supprimée (groupes et étudiants associés supprimés).';
            }
        }

        // --- CRÉATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'create') {
            $nomPromo = trim($_POST['nom_promo'] ?? '');
            $annee    = $_POST['annee'] !== '' ? (int) $_POST['annee'] : null;
            $idDpt    = (int) ($_POST['id_dpt'] ?? 0);

            if ($nomPromo === '' || $idDpt === 0) {
                $erreur = 'Le nom et le département sont obligatoires.';
            } else {
                $result = PromotionModel::create($pdo, $nomPromo, $annee, $idDpt);
                if ($result === false) {
                    $erreur = 'Cette promotion existe déjà.';
                } else {
                    $succes = 'Promotion créée.';
                }
            }
        }

        // --- MODIFICATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'update') {
            $idPromo  = (int) ($_POST['id_promo'] ?? 0);
            $nomPromo = trim($_POST['nom_promo'] ?? '');
            $annee    = $_POST['annee'] !== '' ? (int) $_POST['annee'] : null;
            $idDpt    = (int) ($_POST['id_dpt'] ?? 0);

            if ($idPromo > 0 && $nomPromo !== '' && $idDpt > 0) {
                $result = PromotionModel::update($pdo, $idPromo, $nomPromo, $annee, $idDpt);
                if ($result === false) {
                    $erreur = 'Cette promotion existe déjà.';
                } else {
                    $succes = 'Promotion modifiée.';
                }
            }
        }

        // --- IMPORT CSV ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'import') {
            [$erreur, $succes] = $this->traiterImportCsv($pdo);
        }

        $promotions   = PromotionModel::findAll($pdo);
        $departements = DepartementModel::findAll($pdo);

        $this->render('promotion/gest_promo.php', 'Gestion des promotions', [
            'promotions'   => $promotions,
            'departements' => $departements,
            'erreur'       => $erreur,
            'succes'       => $succes,
        ]);
    }

    /**
     * Traite l'import CSV d'une liste d'étudiants.
     *
     * Format CSV attendu (à partir de la ligne 2) :
     *   Num;Groupe;Nom;Prenom
     *   1;1;ALLARD;Martin
     *
     * @param PDO $pdo
     * @return array{0: string|null, 1: string|null}  [$erreur, $succes]
     */
    private function traiterImportCsv(PDO $pdo): array
    {
        $idPromo = (int) ($_POST['id_promo'] ?? 0);
        if ($idPromo === 0) {
            return ['Veuillez sélectionner une promotion.', null];
        }

        // Vérifie que la promo a des groupes
        $groupes = GroupeModel::findByPromo($pdo, $idPromo);
        if (empty($groupes)) {
            return ['Cette promotion n\'a aucun groupe. Créez d\'abord les groupes.', null];
        }

        // Construit un index nom_groupe → id_groupe
        // Le CSV identifie le groupe par le dernier caractère de son nom (ex: "G1" → "1")
        $indexGroupes = [];
        foreach ($groupes as $g) {
            $cle = substr($g['nom_groupe'], -1);
            $indexGroupes[$cle] = (int) $g['id_groupe'];
        }

        // Validation du fichier uploadé
        if (empty($_FILES['myFile']['tmp_name'])) {
            return ['Aucun fichier sélectionné.', null];
        }

        $fp = fopen($_FILES['myFile']['tmp_name'], 'r');
        if ($fp === false) {
            return ['Impossible de lire le fichier.', null];
        }

        // Supprime les étudiants existants de la promo avant l'import
        EtudiantModel::deleteByPromo($pdo, $idPromo);

        $nbImportes = 0;
        $ligne = 0;

        while ($tab = fgetcsv($fp, 200, ';')) {
            $ligne++;
            // Ignore la ligne d'en-tête (ligne 1)
            if ($ligne === 1) {
                continue;
            }
            $champs = count($tab);
            // Colonnes : Num(0) ; Groupe(1) ; Nom(2) ; Prenom(3)
            // Le CSV peut avoir plusieurs groupes en colonnes (1, 6, 11…)
            for ($i = 1; $i < $champs; $i++) {
                if ($i % 5 === 1 && $tab[$i] !== '') {
                    $cleGroupe = substr($tab[$i], 0, 1);
                    $idGroupe  = $indexGroupes[$cleGroupe] ?? null;
                } elseif ($i % 5 === 2 && $tab[$i] !== '') {
                    $nom = mb_convert_encoding($tab[$i], 'UTF-8', 'ISO-8859-1');
                } elseif ($i % 5 === 3 && isset($idGroupe, $nom) && $tab[$i] !== '') {
                    $prenom = mb_convert_encoding($tab[$i], 'UTF-8', 'ISO-8859-1');
                    if ($idGroupe !== null) {
                        EtudiantModel::create($pdo, $nom, $prenom, $idGroupe);
                        $nbImportes++;
                    }
                    unset($nom, $prenom);
                }
            }
        }

        fclose($fp);

        if ($nbImportes === 0) {
            return ['Aucun étudiant importé : fichier non conforme.', null];
        }

        return [null, "{$nbImportes} étudiant(s) importé(s) avec succès."];
    }
}
