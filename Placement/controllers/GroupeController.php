<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/GroupeModel.php';
require_once __DIR__ . '/../models/PromotionModel.php';

class GroupeController extends Controller
{
    /**
     * Point d'entrée unique : groupes d'une promotion + CRUD.
     * Requiert le paramètre GET ?promo=<id_promo>.
     */
    public function index(): void
    {
        global $pdo;
        $this->exigerAdmin();

        $idPromo = (int) ($_GET['promo'] ?? 0);
        if ($idPromo === 0) {
            header('Location: index.php?action=gest_promo');
            exit();
        }

        $erreur = null;
        $succes = null;

        // --- SUPPRESSION DE GROUPE ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'delete_groupe') {
            $idGroupe = (int) ($_POST['id_groupe'] ?? 0);
            if ($idGroupe > 0) {
                GroupeModel::delete($pdo, $idGroupe);
                $succes = 'Groupe supprimé (étudiants associés supprimés).';
            }
        }

        // --- CRÉATION DE GROUPE ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'create_groupe') {
            $nomGroupe = trim($_POST['nom_groupe'] ?? '');
            if ($nomGroupe === '') {
                $erreur = 'Le nom du groupe est obligatoire.';
            } else {
                $result = GroupeModel::create($pdo, $nomGroupe, $idPromo);
                if ($result === false) {
                    $erreur = 'Ce groupe existe déjà dans cette promotion.';
                } else {
                    $succes = 'Groupe créé.';
                }
            }
        }

        // --- MODIFICATION DE GROUPE ---
        if (isset($_POST['_action']) && $_POST['_action'] === 'update_groupe') {
            $idGroupe  = (int) ($_POST['id_groupe']  ?? 0);
            $nomGroupe = trim($_POST['nom_groupe'] ?? '');
            if ($idGroupe > 0 && $nomGroupe !== '') {
                $result = GroupeModel::update($pdo, $idGroupe, $nomGroupe);
                if ($result === false) {
                    $erreur = 'Ce nom de groupe existe déjà dans cette promotion.';
                } else {
                    $succes = 'Groupe renommé.';
                }
            }
        }

        $promo  = PromotionModel::findById($pdo, $idPromo);
        $groupes = GroupeModel::findByPromoWithCount($pdo, $idPromo);

        $this->render('groupe/gest_grp.php', 'Gestion des groupes', [
            'promo'   => $promo,
            'groupes' => $groupes,
            'erreur'  => $erreur,
            'succes'  => $succes,
        ]);
    }

    /**
     * Route AJAX — retourne les groupes d'une promotion en JSON.
     * Utilisé dans le wizard de placement pour remplir le select groupe
     * dynamiquement quand l'utilisateur change la promotion.
     *
     * POST: { idPromo: int }
     * Réponse: [{ id_groupe, nom_groupe }, …]
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

        $groupes = GroupeModel::findByPromo($pdo, $idPromo);
        $this->jsonResponse($groupes);
    }
}
