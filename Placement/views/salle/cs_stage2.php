<link rel="stylesheet" href="public/css/s_stage2.css">

<div class="titrecontenu">Créer une salle — Étape 2</div>

<h3><?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>

<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <div class="erreur"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=crea_salle&etape=2">

        <div class="champ">
            <label for="nb_col">Nombre de colonnes</label>
            <input
                type="number"
                id="nb_col"
                name="nb_col"
                min="1"
                max="20"
                required
                value="<?php echo htmlspecialchars((string)($sessionSalle['nb_col'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="champ">
            <label for="nb_rang">Nombre de rangs</label>
            <input
                type="number"
                id="nb_rang"
                name="nb_rang"
                min="1"
                max="20"
                required
                value="<?php echo htmlspecialchars((string)($sessionSalle['nb_rang'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="actions">
            <a href="index.php?action=crea_salle&etape=1">← Retour</a>
            <button type="submit">Suivant →</button>
        </div>

    </form>

</div>
