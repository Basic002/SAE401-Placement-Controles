<?php
// ============================================================
// POINT D'ENTRÉE UNIQUE (Front Controller)
// Toutes les requêtes HTTP passent ici.
// ============================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Session & BDD ----------------------------------------
session_start();
require_once __DIR__ . '/config/connexion.php';

// --- Lecture de l'action demandée -------------------------
// On accepte ?action= (nouveau) et ?p= (compatibilité ancien code).
// La valeur est filtrée : seuls les caractères alphanumériques
// et les underscores sont autorisés (whitelist implicite via le switch).
$action = 'home';
if (isset($_GET['action']) && $_GET['action'] !== '') {
    $action = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['action']);
} elseif (isset($_GET['p']) && $_GET['p'] !== '') {
    $action = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['p']);
}

// --- Garde globale : accès refusé si non connecté ----------
// Les seules actions accessibles sans session sont login et les
// routes AJAX publiques (aucune dans ce projet).
$actionsPubliques = ['login'];
if (!isset($_SESSION['login']) && !in_array($action, $actionsPubliques, true)) {
    header('Location: index.php?action=login');
    exit();
}

// --- Détection première connexion (mot de passe par défaut) -
// La vérification se fait via le contrôleur Auth pour ne pas
// mettre de logique métier dans le routeur.
if (isset($_SESSION['login']) && !in_array($action, ['logout', 'deconnexion', 'changer_mdp'], true)) {
    require_once __DIR__ . '/controllers/AuthController.php';
    require_once __DIR__ . '/models/EnseignantModel.php';
    if (AuthController::doitChangerMotDePasse($pdo, $_SESSION['login'])) {
        $action = 'changer_mdp';
    }
}

// ============================================================
// ROUTEUR PRINCIPAL
// Chaque cas instancie le contrôleur approprié et appelle
// la bonne méthode. Aucun HTML ne sort du routeur.
// ============================================================
switch ($action) {

    // =================== AUTHENTIFICATION ===================
    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->login();
        break;

    case 'logout':
    case 'deconnexion':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->deconnexion();
        break;

    case 'changer_mdp':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->forcerChangementMdp();
        break;

    // =================== ACCUEIL ============================
    case 'home':
        require_once __DIR__ . '/controllers/HomeController.php';
        (new HomeController())->index();
        break;

    // =================== SALLES =============================
    case 'gest_salle':
        require_once __DIR__ . '/controllers/SalleController.php';
        (new SalleController())->index();
        break;

    case 'crea_salle':
        require_once __DIR__ . '/controllers/SalleController.php';
        (new SalleController())->creer();
        break;

    case 'modif_salle':
        require_once __DIR__ . '/controllers/SalleController.php';
        (new SalleController())->modifier();
        break;

    case 'visu_salle':
    case 'x_salle':
        require_once __DIR__ . '/controllers/SalleController.php';
        (new SalleController())->visualiser();
        break;

    // =================== PROMOTIONS & GROUPES ===============
    case 'gest_promo':
        require_once __DIR__ . '/controllers/PromotionController.php';
        (new PromotionController())->index();
        break;

    case 'gest_grp':
        require_once __DIR__ . '/controllers/GroupeController.php';
        (new GroupeController())->index();
        break;

    case 'etudiant':
    case 'gest_etud':
        require_once __DIR__ . '/controllers/EtudiantController.php';
        (new EtudiantController())->index();
        break;

    case 'import_promo':
        require_once __DIR__ . '/controllers/EtudiantController.php';
        (new EtudiantController())->importer();
        break;

    // =================== MATIÈRES ===========================
    case 'gest_mat':
        require_once __DIR__ . '/controllers/MatiereController.php';
        (new MatiereController())->index();
        break;

    // =================== ENSEIGNANTS ========================
    case 'gest_ens':
        require_once __DIR__ . '/controllers/EnseignantController.php';
        (new EnseignantController())->index();
        break;

    case 'gest_ensmat':
        require_once __DIR__ . '/controllers/EnseignementController.php';
        (new EnseignementController())->index();
        break;

    // =================== DÉPARTEMENT & BÂTIMENT =============
    case 'gest_dpt':
        require_once __DIR__ . '/controllers/DepartementController.php';
        (new DepartementController())->index();
        break;

    case 'gest_bat':
        require_once __DIR__ . '/controllers/BatimentController.php';
        (new BatimentController())->index();
        break;

    // =================== PLACEMENT ==========================
    case 'util_placement':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->index();
        break;

    case 'placement_stage1':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->stage1();
        break;

    case 'placement_stage2':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->stage2();
        break;

    case 'placement_stage3':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->stage3();
        break;

    case 'placement_stage4':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->stage4();
        break;

    // =================== AJAX ===============================
    // Ces routes retournent du JSON (pas de layout).
    case 'ajax_groupe':
        require_once __DIR__ . '/controllers/GroupeController.php';
        (new GroupeController())->ajaxGetByPromo();
        break;

    case 'ajax_matiere':
        require_once __DIR__ . '/controllers/MatiereController.php';
        (new MatiereController())->ajaxGetByPromo();
        break;

    case 'ajax_info_devoir':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->ajaxInfoDevoir();
        break;

    case 'ajax_add_combi':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->ajaxAddCombi();
        break;

    case 'ajax_suppr_combi':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->ajaxSupprCombi();
        break;

    case 'ajax_affiche_combi':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->ajaxAfficheCombi();
        break;

    case 'ajax_intervertir':
        require_once __DIR__ . '/controllers/PlacementController.php';
        (new PlacementController())->ajaxIntervertir();
        break;

    case 'export_pdf':
        require_once __DIR__ . '/public/export_pdf.php';
        break;

    // =================== 404 ================================
    default:
        http_response_code(404);
        $titre_page = "Page introuvable";
        ob_start();
        ?>
        <div style="padding: 2rem; text-align: center;">
            <h1>Erreur 404</h1>
            <p>La page <strong><?= htmlspecialchars($action, ENT_QUOTES, 'UTF-8') ?></strong> n'existe pas.</p>
            <a href="index.php?action=home">← Retour à l'accueil</a>
        </div>
        <?php
        $contenu_page = ob_get_clean();
        require_once __DIR__ . '/views/layout.php';
        break;
}
