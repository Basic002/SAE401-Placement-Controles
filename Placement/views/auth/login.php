<html>
<head>
    <title>Connexion</title>
    <link rel="stylesheet" href="public/css/s_login.css">
</head>
<body>
    <h1>Service d'authentification</h1>
    
    <?php if (isset($erreur)): ?>
        <p style="color:red;"><?php echo $erreur; ?></p>
    <?php endif; ?>

    <div id="bloc1">
        <form action="index.php?action=login" method="post">
            <input type="text" name="login" placeholder="Login" required>
            <input type="password" name="pass" placeholder="Mot de passe" required>
            <input type="submit" name="connexion" value="Connexion">
        </form>
    </div>
</body>
</html>