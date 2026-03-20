<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session php
session_start();

// Inclusion de la connexion à la base de données
require_once 'config/connexion.php';

// INTERCEPTION DE L'URL
// On regarde l'action demandée (si on utilise encore ?p=, on le convertit en action)
$action = 'home'; // Page par défaut
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} elseif (isset($_GET['p'])) {
    $action = $_GET['p']; 
}

// VÉRIFICATION DE LA SÉCURITÉ
// 1. Si non connecté et qu'on n'essaie pas de se connecter, redirection vers login
if (!isset($_SESSION['login']) && $action !== 'login') {
    header('Location: index.php?action=login');
    exit();
}

// 2. Gestion de la première connexion (mot de passe 'declic')
if (isset($_SESSION['login'])) {
    $sql = 'SELECT * FROM enseignant WHERE login = :login';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['login' => $_SESSION['login']]);
    $user = $stmt->fetch();

    // Si le mot de passe est toujours celui par défaut ('declic')
    if ($user && $user['pass'] == md5('declic') && $action !== 'logout' && $action !== 'changer_mdp') {
        // On force l'action sur le changement de mot de passe
        $action = 'changer_mdp';
    }
}

// LE ROUTEUR
// On appelle le bon contrôleur en fonction de l'action
switch ($action) {
    
    // ================== AUTHENTIFICATION ==================
    case 'login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'logout':
    case 'deconnexion':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->deconnexion();
        break;

    case 'changer_mdp':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->forcerChangementMdp();
        break;

    // ================== ACCUEIL ==================
    case 'home':
        require_once 'controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    // ================== GESTION ==================
    case 'gest_mat':
        require_once 'controllers/MatiereController.php';
        $controller = new MatiereController();
        $controller->index();
        break;

    case 'gest_ens':
        require_once 'controllers/EnseignantController.php';
        $controller = new EnseignantController();
        $controller->index();
        break;

    case 'gest_ensmat':
        require_once 'controllers/EnseignementController.php';
        $controller = new EnseignementController();
        $controller->index();
        break;

    case 'gest_salle':
        require_once 'controllers/SalleController.php';
        $controller = new SalleController();
        $controller->index();
        break;

    case 'crea_salle':
        require_once 'controllers/SalleController.php';
        $controller = new SalleController();
        $controller->creer();
        break;

    case 'modif_salle':
        require_once 'controllers/SalleController.php';
        $controller = new SalleController();
        $controller->modifier();
        break;

    case 'gest_dpt':
        require_once 'controllers/DepartementController.php';
        $controller = new DepartementController();
        $controller->index();
        break;

    case 'gest_bat':
        require_once 'controllers/BatimentController.php';
        $controller = new BatimentController();
        $controller->index();
        break;

    case 'gest_promo':
        require_once 'controllers/PromotionController.php';
        $controller = new PromotionController();
        $controller->index();
        break;

    // ================== PLACEMENT ==================
    case 'util_placement':
        require_once 'controllers/PlacementController.php';
        $controller = new PlacementController();
        $controller->index();
        break;

    // ================== PAGE INTROUVABLE ==================
    default:
        http_response_code(404);
        echo "<h1>Erreur 404</h1><p>La page demandée n'existe pas ou le contrôleur n'est pas encore créé.</p>";
        echo "<a href='index.php?action=home'>Retour à l'accueil</a>";
        break;
}