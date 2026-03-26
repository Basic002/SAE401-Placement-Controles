<link rel="stylesheet" href="public/css/s_stage3.css">

<div class="titrecontenu">Créer une salle — Étape 3</div>

<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <div class="erreur"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <h3><?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>

    <div class="radio-groupe">
        <label>
            <input type="radio" name="choixEtat" id="radio_couloir" value="0">
            Couloir
        </label>
        <label>
            <input type="radio" name="choixEtat" id="radio_placeOk" value="1" checked>
            Place normale
        </label>
        <label>
            <input type="radio" name="choixEtat" id="radio_handi" value="2">
            Place PMR
        </label>
        <label>
            <input type="radio" name="choixEtat" id="radio_inex" value="3">
            Place inexistante
        </label>
    </div>

    <table id="TAB1">
        <?php
        $nbRang = (int)($sessionSalle['nb_rang'] ?? 0);
        $nbCol  = (int)($sessionSalle['nb_col']  ?? 0);
        for ($i = 0; $i < $nbRang; $i++):
        ?>
            <tr id="<?php echo $i; ?>">
                <?php for ($j = 0; $j < $nbCol; $j++): ?>
                    <td id="<?php echo $i . '-' . $j; ?>" class="placeOk" onclick="modifEtat(this)"></td>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
    </table>

    <div class="bureau">BUREAU</div>

    <form method="POST" action="index.php?action=crea_salle&etape=3" id="formStage">
        <input type="hidden" id="donnee" name="donnee" value="">
    </form>

</div>

<script src="public/javascript/crea_salle_s3.js"></script>
