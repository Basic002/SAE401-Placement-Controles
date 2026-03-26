<div style="max-width: 420px; margin: 4rem auto; padding: 2rem;">
    <h2>Changement de mot de passe obligatoire</h2>
    <p>Votre mot de passe est celui attribué par défaut. Veuillez le modifier avant de continuer.</p>

    <?php if (!empty($erreur)): ?>
        <p class="erreur"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form action="index.php?action=changer_mdp" method="post">
        <label for="nouveau_mdp">Nouveau mot de passe</label><br>
        <input type="password" id="nouveau_mdp" name="nouveau_mdp"
               minlength="8" required autocomplete="new-password"><br><br>

        <label for="confirme_mdp">Confirmer le mot de passe</label><br>
        <input type="password" id="confirme_mdp" name="confirme_mdp"
               minlength="8" required autocomplete="new-password"><br><br>

        <input type="submit" value="Enregistrer le nouveau mot de passe">
    </form>
</div>
