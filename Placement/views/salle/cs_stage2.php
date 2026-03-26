<link rel="stylesheet" href="public/css/s_stage2.css">

<div class="titrecontenu">Créer une salle — Étape 2</div>

<h3><?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>

<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <div class="erreur"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=crea_salle&etape=2" id="formStage">

        <div class="champ">
            <label for="nbCol">Nombre de colonnes</label>
            <input
                type="number"
                id="nbCol"
                name="nb_col"
                min="1"
                max="20"
                required
                value="<?php echo htmlspecialchars((string)($sessionSalle['nb_col'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="champ">
            <label for="nbRang">Nombre de rangs</label>
            <input
                type="number"
                id="nbRang"
                name="nb_rang"
                min="1"
                max="20"
                required
                value="<?php echo htmlspecialchars((string)($sessionSalle['nb_rang'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

    </form>

</div>
