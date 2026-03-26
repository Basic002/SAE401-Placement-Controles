<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/MatiereModel.php';
require_once __DIR__ . '/../models/PromotionModel.php';

class MatiereController extends Controller
{
    /**
     * Point d'entrée unique : liste + création + modification + suppression.
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $erreur             = null;
        $succes             = null;
        $reopenCreateForm  = false;

        // --- SUPPRESSION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'delete') {
            $idMat = (int) ($_POST['id_mat'] ?? 0);
            if ($idMat > 0) {
                MatiereModel::delete($pdo, $idMat);
                $succes = 'Matière supprimée.';
            }
        }

        // --- CRÉATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'create') {
            $nomMat  = trim($_POST['nom_mat']  ?? '');
            $idPromo = (int) ($_POST['id_promo'] ?? 0);
            if ($nomMat === '') {
                $erreur            = 'Nom obligatoire : saisissez le nom de la matière avant de cliquer sur « Ajouter ».';
                $reopenCreateForm = true;
            } elseif ($idPromo === 0) {
                $erreur            = 'Promotion obligatoire : choisissez une promotion.';
                $reopenCreateForm = true;
            } else {
                $result = MatiereModel::create($pdo, $nomMat, $idPromo);
                if ($result === false) {
                    $erreur            = 'Cette matière existe déjà pour cette promotion.';
                    $reopenCreateForm = true;
                } else {
                    $succes = 'Matière créée.';
                }
            }
        }

        // --- MODIFICATION ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'update') {
            $idMat   = (int) ($_POST['id_mat']   ?? 0);
            $nomMat  = trim($_POST['nom_mat']  ?? '');
            $idPromo = (int) ($_POST['id_promo'] ?? 0);
            if ($idMat > 0 && $nomMat !== '' && $idPromo > 0) {
                $result = MatiereModel::update($pdo, $idMat, $nomMat, $idPromo);
                if ($result === false) {
                    $erreur = 'Cette matière existe déjà pour cette promotion.';
                } else {
                    $succes = 'Matière modifiée.';
                }
            }
        }

        $matieres   = MatiereModel::findAll($pdo);
        $promotions = PromotionModel::findAll($pdo);

        $this->render('matiere/gest_mat.php', 'Gestion des matières', [
            'matieres'           => $matieres,
            'promotions'         => $promotions,
            'erreur'             => $erreur,
            'succes'             => $succes,
            'reopenCreateForm'   => $reopenCreateForm,
        ]);
    }

    /**
     * Route AJAX — retourne les matières d'une promotion en JSON.
     * Appelé par le JS du wizard de placement quand l'utilisateur
     * change la promotion sélectionnée.
     *
     * POST: { idPromo: int }
     * Réponse: [{ id_mat, nom_mat }, …]
     */
    public function ajaxGetByPromo(): void
    {
        global $pdo;
        if (!isset($_SESSION['login'])) {
            $this->jsonResponse(['error' => 'Non authentifié'], 403);
        }

        $idPromo = (int) ($_POST['idPromo'] ?? 0);
        if ($idPromo === 0) {
            $this->jsonResponse([]);
        }

        $matieres = MatiereModel::findByPromo($pdo, $idPromo);
        $this->jsonResponse($matieres);
    }
}
