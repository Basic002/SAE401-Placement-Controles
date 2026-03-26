<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/EnseignantModel.php';
require_once __DIR__ . '/../models/MatiereModel.php';

class EnseignementController extends Controller
{
    /**
     * Point d'entrée unique : liste des associations enseigne + création + suppression.
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $erreur = null;
        $succes = null;

        // --- SUPPRESSION de l'association (id_mat + id_ens passés en POST) ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'delete') {
            $idMat = (int) ($_POST['id_mat'] ?? 0);
            $idEns = (int) ($_POST['id_ens'] ?? 0);
            if ($idMat > 0 && $idEns > 0) {
                EnseignantModel::removeMatiere($pdo, $idEns, $idMat);
                $succes = 'Association supprimée.';
            }
        }

        // --- CRÉATION de l'association ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'create') {
            $idMat = (int) ($_POST['id_mat'] ?? 0);
            $idEns = (int) ($_POST['id_ens'] ?? 0);
            if ($idMat > 0 && $idEns > 0) {
                // INSERT IGNORE utilisé dans le modèle : pas de doublon possible
                EnseignantModel::addMatiere($pdo, $idEns, $idMat);
                $succes = 'Association créée.';
            } else {
                $erreur = 'Veuillez sélectionner un enseignant et une matière.';
            }
        }

        // Récupère toutes les associations pour l'affichage
        $associations = $pdo->query(
            'SELECT en.id_ens, en.nom_ens, en.prenom_ens,
                    m.id_mat, m.nom_mat,
                    p.nom_promo, d.nom_dpt
               FROM enseigne e
               JOIN enseignant en ON e.id_ens = en.id_ens
               JOIN matiere m ON e.id_mat = m.id_mat
               JOIN promotion p ON m.id_promo = p.id_promo
               JOIN departement d ON p.id_dpt = d.id_dpt
              ORDER BY en.nom_ens ASC, p.nom_promo ASC, m.nom_mat ASC'
        )->fetchAll();

        $enseignants = EnseignantModel::findAll($pdo);
        $matieres    = MatiereModel::findAll($pdo);

        $this->render('enseignement/gest_ensmat.php', 'Gestion des enseignements', [
            'associations' => $associations,
            'enseignants'  => $enseignants,
            'matieres'     => $matieres,
            'erreur'       => $erreur,
            'succes'       => $succes,
        ]);
    }
}
