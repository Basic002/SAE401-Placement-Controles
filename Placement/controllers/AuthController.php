<?php
class AuthController {

    /**
     * Affiche le formulaire de connexion et traite la soumission.
     */
    public function login(): void {
        global $pdo;
        $erreur = null;

        if (isset($_POST['connexion']) && $_POST['connexion'] === 'Connexion') {
            $login = trim($_POST['login'] ?? '');
            $pass  = $_POST['pass']  ?? '';

            if (!empty($login) && !empty($pass)) {
                try {
                    // ============================================================
                    // CORRECTION BDD5 - INJECTION SQL :
                    // On utilise une requête préparée avec un paramètre nommé
                    // :login. La valeur est envoyée SÉPARÉMENT de la requête SQL,
                    // ce qui rend toute injection impossible.
                    // Ancienne version vulnérable (exemple) :
                    //   "WHERE login = '$login'" → injection directe possible.
                    //
                    // CORRECTION BDD6 - VÉRIFICATION MDP :
                    // On ne met PLUS le mot de passe dans la requête SQL.
                    // On récupère uniquement le hash BCrypt stocké en base,
                    // puis on laisse password_verify() faire la comparaison
                    // côté PHP. C'est la seule façon compatible avec BCrypt
                    // car BCrypt génère un salt unique à chaque hash :
                    // deux hashes du même mot de passe sont DIFFÉRENTS,
                    // donc une comparaison SQL directe est impossible.
                    // ============================================================
                    $sql  = 'SELECT pass, admin FROM enseignant WHERE login = :login';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(['login' => $login]);
                    $user = $stmt->fetch();

                    // ============================================================
                    // CORRECTION BDD4-HACHAGE + BDD6 :
                    // password_verify($mot_de_passe_saisi, $hash_en_base)
                    // - Recalcule le hash BCrypt du mot de passe saisi
                    //   en utilisant le salt intégré dans $user['pass'].
                    // - Compare en TEMPS CONSTANT (résistance aux timing attacks).
                    // Ancienne version : md5($pass) → MD5 est cassé depuis 2008,
                    //   pas de salt, crackable en quelques minutes avec une
                    //   rainbow table.
                    // ============================================================
                    if ($user && password_verify($pass, $user['pass'])) {

                        // Régénérer l'ID de session pour prévenir
                        // les attaques de fixation de session.
                        session_regenerate_id(true);

                        $_SESSION['login'] = $login;
                        $_SESSION['droit'] = (int) $user['admin'];

                        header('Location: index.php?action=home');
                        exit();

                    } else {
                        // Message VOLONTAIREMENT générique :
                        // ne pas distinguer "login inconnu" de "mauvais mdp"
                        // pour éviter l'énumération des comptes utilisateurs.
                        $erreur = 'Identifiants incorrects.';
                    }

                } catch (PDOException $e) {
                    error_log('[AUTH LOGIN] Erreur BDD : ' . $e->getMessage());
                    $erreur = "Erreur technique. Veuillez réessayer.";
                }

            } else {
                $erreur = 'Tous les champs sont obligatoires.';
            }
        }

        require_once 'views/auth/login.php';
    }

    /**
     * Déconnecte l'utilisateur et détruit la session.
     */
    public function deconnexion(): void {
        // Vider le tableau de session avant de détruire
        // pour s'assurer qu'aucune donnée ne subsiste.
        $_SESSION = [];

        // Supprimer le cookie de session côté navigateur.
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        header('Location: index.php?action=login');
        exit();
    }

    /**
     * Affiche la vue de changement de mot de passe.
     */
    public function forcerChangementMdp(): void {
        require_once 'views/auth/changer_mdp.php';
    }
}
