<?php
require_once __DIR__ . '/../models/EnseignantModel.php';

class AuthController
{
    /**
     * Vérifie si l'utilisateur connecté doit changer son mot de passe.
     * Appelé par le routeur à chaque requête pour forcer le changement
     * si le mot de passe est encore le mot de passe par défaut 'declic'.
     *
     * @param PDO    $pdo
     * @param string $login
     * @return bool
     */
    public static function doitChangerMotDePasse(PDO $pdo, string $login): bool
    {
        $user = EnseignantModel::findByLogin($pdo, $login);
        if (!$user) {
            return false;
        }
        // Le mot de passe par défaut attribué aux nouveaux comptes est 'declic'.
        // password_verify permet de tester contre un hash bcrypt.
        return password_verify('declic', $user['pass']);
    }

    // ------------------------------------------------------------------

    /**
     * Affiche le formulaire de connexion et traite la soumission POST.
     */
    public function login(): void
    {
        global $pdo;
        $erreur = null;
        $loginValue = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim($_POST['login'] ?? '');
            $pass  = $_POST['pass']  ?? '';
            $loginValue = $login;

            if ($login !== '' && $pass !== '') {
                try {
                    // On récupère uniquement le hash — le mot de passe ne transite
                    // jamais dans la requête SQL (protection injection + timing attack).
                    $user = EnseignantModel::findByLogin($pdo, $login);

                    if ($user && password_verify($pass, $user['pass'])) {
                        // Régénération de l'ID de session : prévient la fixation de session.
                        session_regenerate_id(true);

                        $_SESSION['login'] = $login;
                        $_SESSION['id_ens'] = (int) $user['id_ens'];
                        $_SESSION['droit']  = (int) $user['admin'];

                        header('Location: index.php?action=home');
                        exit();
                    } else {
                        // Message volontairement générique : ne pas distinguer
                        // "login inconnu" de "mauvais mdp" (évite l'énumération).
                        $erreur = 'Identifiants incorrects.';
                    }
                } catch (PDOException $e) {
                    error_log('[AUTH LOGIN] Erreur BDD : ' . $e->getMessage());
                    $erreur = 'Erreur technique. Veuillez réessayer.';
                }
            } else {
                $erreur = 'Tous les champs sont obligatoires.';
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    // ------------------------------------------------------------------

    /**
     * Traite le changement de mot de passe forcé (premier login avec 'declic').
     */
    public function forcerChangementMdp(): void
    {
        global $pdo;
        $erreur  = null;
        $succes  = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nouveau    = $_POST['nouveau_mdp']    ?? '';
            $confirme   = $_POST['confirme_mdp']   ?? '';

            if ($nouveau === '' || $confirme === '') {
                $erreur = 'Tous les champs sont obligatoires.';
            } elseif ($nouveau !== $confirme) {
                $erreur = 'Les deux mots de passe ne correspondent pas.';
            } elseif (strlen($nouveau) < 8) {
                $erreur = 'Le mot de passe doit contenir au moins 8 caractères.';
            } elseif (password_verify('declic', password_hash($nouveau, PASSWORD_BCRYPT))) {
                // Empêche de "changer" pour le même mot de passe par défaut.
                $erreur = 'Vous devez choisir un mot de passe différent de celui par défaut.';
            } else {
                $user = EnseignantModel::findByLogin($pdo, $_SESSION['login']);
                if ($user) {
                    EnseignantModel::updatePassword($pdo, (int) $user['id_ens'], $nouveau);
                    $succes = true;
                    // Redirection vers l'accueil après changement réussi
                    header('Location: index.php?action=home');
                    exit();
                }
            }
        }

        $titre_page = 'Changement de mot de passe obligatoire';
        ob_start();
        require_once __DIR__ . '/../views/auth/changer_mdp.php';
        $contenu_page = ob_get_clean();
        require_once __DIR__ . '/../views/layout.php';
    }

    // ------------------------------------------------------------------

    /**
     * Déconnecte l'utilisateur, détruit la session et le cookie associé.
     */
    public function deconnexion(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        header('Location: index.php?action=login');
        exit();
    }
}
