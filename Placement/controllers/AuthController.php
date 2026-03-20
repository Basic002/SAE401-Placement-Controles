<?php
class AuthController {
    
    // Affiche le formulaire ou traite la connexion
    public function login() {
        global $pdo;
        $erreur = null;

        // Si le formulaire est soumis
        if (isset($_POST['connexion']) && $_POST['connexion'] == 'Connexion') {
            $login = $_POST['login'] ?? '';
            $pass = $_POST['pass'] ?? '';

            if (!empty($login) && !empty($pass)) {
                try {
                    // Vérification des identifiants
                    $sql = 'SELECT count(*) as count, admin FROM enseignant WHERE login = :login AND pass = :pass';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['login' => $login, 'pass' => md5($pass)]);
                    $user = $stmt->fetch();

                    if ($user && $user['count'] == 1) {
                        // Hydratation de la session
                        $_SESSION['login'] = $login;
                        $_SESSION['droit'] = $user['admin'];
                        
                        header('Location: index.php?action=home');
                        exit();
                    } else {
                        $erreur = 'Compte non reconnu.';
                    }
                } catch (PDOException $e) {
                    $erreur = "Erreur de base de données.";
                }
            } else {
                $erreur = 'Au moins un des champs est vide.';
            }
        }

        // On inclut la vue en lui passant la variable $erreur si elle existe
        require_once 'views/auth/login.php';
    }

    public function deconnexion() {
        $_SESSION = array();
        session_destroy();
        header('Location: index.php?action=login');
        exit();
    }

    public function forcerChangementMdp() {
        // Logique pour afficher la vue de changement de mot de passe
        require_once 'views/auth/changer_mdp.php';
    }
}
?>