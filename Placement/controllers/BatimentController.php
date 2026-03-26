<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/BatimentModel.php';

class BatimentController extends Controller
{
    /**
     * Point d'entrée unique : liste + création + modification + suppression.
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $erreur = null;
        $succes = null;

        // --- SUPPRESSION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'delete') {
            $idBat = (int) ($_POST['id_bat'] ?? 0);
            if ($idBat > 0) {
                if (BatimentModel::isUsed($pdo, $idBat)) {
                    $erreur = 'Impossible de supprimer : des salles utilisent ce bâtiment.';
                } else {
                    BatimentModel::delete($pdo, $idBat);
                    $succes = 'Bâtiment supprimé.';
                }
            }
        }

        // --- CRÉATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'create') {
            $nomBat = trim($_POST['nom_bat'] ?? '');
            $adBat  = trim($_POST['ad_bat']  ?? '');
            if ($nomBat === '') {
                $erreur = 'Le nom du bâtiment est obligatoire.';
            } else {
                $result = BatimentModel::create($pdo, $nomBat, $adBat);
                if ($result === false) {
                    $erreur = 'Ce bâtiment existe déjà.';
                } else {
                    $succes = 'Bâtiment créé.';
                }
            }
        }

        // --- MODIFICATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'update') {
            $idBat  = (int) ($_POST['id_bat']  ?? 0);
            $nomBat = trim($_POST['nom_bat'] ?? '');
            $adBat  = trim($_POST['ad_bat']  ?? '');
            if ($idBat > 0 && $nomBat !== '') {
                BatimentModel::update($pdo, $idBat, $nomBat, $adBat);
                $succes = 'Bâtiment modifié.';
            }
        }

        $batiments = BatimentModel::findAll($pdo);

        $this->render('bat/gest_bat.php', 'Gestion des bâtiments', [
            'batiments' => $batiments,
            'erreur'    => $erreur,
            'succes'    => $succes,
        ]);
    }
}
