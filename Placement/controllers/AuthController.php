<?php
class AuthController {
    
    // Méthode appelée par index.php quand l'action est 'login'
    public function afficherLogin() {
        global $pdo; // Permet à ton fichier login.php d'utiliser la connexion BDD de l'index
        require_once 'views/auth/login.php';
    }

    // Méthode pour se déconnecter
    public function deconnexion() {
        // On vide la session
        $_SESSION = array();
        session_destroy();
        // On redirige vers l'accueil/login
        header('Location: index.php?action=login');
        exit();
    }

    // Méthode pour le changement de mot de passe obligatoire
    public function forcerChangementMdp() {
        echo "<h1>Changement de mot de passe requis</h1>";
        echo "<p>Veuillez changer votre mot de passe par défaut 'declic'. (Page à développer)</p>";
        // Plus tard, tu pourras inclure une vue ici : require_once 'views/auth/changer_mdp.php';
    }
}
?>