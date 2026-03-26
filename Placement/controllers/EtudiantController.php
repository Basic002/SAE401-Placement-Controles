<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/EtudiantModel.php';
require_once __DIR__ . '/../models/GroupeModel.php';
require_once __DIR__ . '/../models/PromotionModel.php';

class EtudiantController extends Controller
{
    /**
     * Point d'entrée : liste des étudiants d'un groupe + CRUD.
     * Paramètres GET : ?promo=<id_promo> (requis), &groupe=<id_groupe> (optionnel)
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $idPromo  = (int) ($_GET['promo']  ?? 0);
        $idGroupe = (int) ($_GET['groupe'] ?? 0);

        if ($idPromo === 0) {
            header('Location: index.php?action=gest_promo');
            exit();
        }

        $erreur = null;
        $succes = null;

        // --- SUPPRESSION D'ÉTUDIANT ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'delete') {
            $idEtudiant = (int) ($_POST['id_etudiant'] ?? 0);
            if ($idEtudiant > 0) {
                EtudiantModel::delete($pdo, $idEtudiant);
                $succes = 'Étudiant supprimé.';
            }
        }

        // --- CRÉATION D'ÉTUDIANT ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'create') {
            $nom      = trim($_POST['nom_etudiant']    ?? '');
            $prenom   = trim($_POST['prenom_etudiant'] ?? '');
            $idGrp    = (int) ($_POST['id_groupe']   ?? 0);
            $tt       = (int) ($_POST['tiers_temps']  ?? 0);
            $mr       = (int) ($_POST['mob_reduite']  ?? 0);
            $demigr   = (int) ($_POST['demigr']       ?? 0);

            if ($nom === '' || $prenom === '' || $idGrp === 0) {
                $erreur = 'Nom, prénom et groupe sont obligatoires.';
            } else {
                $result = EtudiantModel::create($pdo, $nom, $prenom, $idGrp, $tt, $mr, $demigr);
                if ($result === false) {
                    $erreur = 'Cet étudiant existe déjà dans ce groupe.';
                } else {
                    $succes = 'Étudiant ajouté.';
                }
            }
        }

        // --- MODIFICATION D'ÉTUDIANT ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'update') {
            $idEtudiant = (int) ($_POST['id_etudiant']     ?? 0);
            $nom        = trim($_POST['nom_etudiant']    ?? '');
            $prenom     = trim($_POST['prenom_etudiant'] ?? '');
            $idGrp      = (int) ($_POST['id_groupe']     ?? 0);
            $tt         = (int) ($_POST['tiers_temps']   ?? 0);
            $mr         = (int) ($_POST['mob_reduite']   ?? 0);
            $demigr     = (int) ($_POST['demigr']        ?? 0);

            if ($idEtudiant > 0 && $nom !== '' && $prenom !== '' && $idGrp > 0) {
                $result = EtudiantModel::update(
                    $pdo, $idEtudiant, $nom, $prenom, $idGrp, $tt, $mr, $demigr
                );
                if ($result === false) {
                    $erreur = 'Cet étudiant existe déjà dans ce groupe.';
                } else {
                    $succes = 'Étudiant modifié.';
                }
            }
        }

        $promo    = PromotionModel::findById($pdo, $idPromo);
        $groupes  = GroupeModel::findByPromo($pdo, $idPromo);
        // Si un groupe est sélectionné on affiche ses étudiants, sinon tous ceux de la promo
        $etudiants = $idGroupe > 0
            ? EtudiantModel::findByGroupe($pdo, $idGroupe)
            : EtudiantModel::findByPromo($pdo, $idPromo);

        $this->render('etudiant/etudiant.php', 'Gestion des étudiants', [
            'promo'      => $promo,
            'groupes'    => $groupes,
            'etudiants'  => $etudiants,
            'idGroupe'   => $idGroupe,
            'erreur'     => $erreur,
            'succes'     => $succes,
        ]);
    }
}
