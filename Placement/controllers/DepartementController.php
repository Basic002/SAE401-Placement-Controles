<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/DepartementModel.php';

class DepartementController extends Controller
{
    /**
     * Point d'entrée unique : liste + création + modification + suppression.
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $erreur  = null;
        $succes  = null;

        // --- SUPPRESSION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'delete') {
            $idDpt = (int) ($_POST['id_dpt'] ?? 0);
            if ($idDpt > 0) {
                DepartementModel::delete($pdo, $idDpt);
                $succes = 'Département supprimé.';
            }
        }

        // --- CRÉATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'create') {
            $nomDpt = trim($_POST['nom_dpt'] ?? '');
            if ($nomDpt === '') {
                $erreur = 'Le nom du département est obligatoire.';
            } else {
                $result = DepartementModel::create($pdo, $nomDpt);
                if ($result === false) {
                    $erreur = 'Ce département existe déjà.';
                } else {
                    $succes = 'Département créé.';
                }
            }
        }

        // --- MODIFICATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'update') {
            $idDpt  = (int) ($_POST['id_dpt']   ?? 0);
            $nomDpt = trim($_POST['nom_dpt'] ?? '');
            if ($idDpt > 0 && $nomDpt !== '') {
                DepartementModel::update($pdo, $idDpt, $nomDpt);
                $succes = 'Département modifié.';
            }
        }

        $departements = DepartementModel::findAll($pdo);

        $this->render('departement/gest_dpt.php', 'Gestion des départements', [
            'departements' => $departements,
            'erreur'       => $erreur,
            'succes'       => $succes,
        ]);
    }
}
