<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Placement IUT</title>
    <link rel="stylesheet" href="public/css/s_login.css">
</head>
<body>
    <h1>Service d'authentification</h1>

    <?php if (isset($erreur) && !empty($erreur)): ?>
        <!--
            CORRECTION XSS :
            htmlspecialchars() encode les caractères spéciaux HTML
            (<, >, ", ', &) avant affichage.
            Sans ça, si $erreur contenait du HTML/JS injecté,
            le navigateur l'exécuterait (Cross-Site Scripting).
        -->
        <p class="erreur"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <div id="bloc1">
        <form action="index.php?action=login" method="post">

            <input
                type="text"
                name="login"
                placeholder="Login"
                value="<?php echo htmlspecialchars($loginValue ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                autocomplete="username"
                required
            >
            <input
                type="password"
                name="pass"
                placeholder="Mot de passe"
                autocomplete="current-password"
                required
            >
            <input type="submit" name="connexion" value="Connexion">
        </form>
    </div>
</body>
</html>
