<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_ms_stage1.css">

<!-- AFFICHAGE ERREUR -->
<?php if (!empty($erreur)): ?>
<div class="erreur"><?php echo htmlspecialchars($erreur); ?></div>
<?php endif; ?>

<!-- FORMULAIRE SELECTION SALLE -->
<form method="POST" action="index.php?action=modif_salle&etape=1">

        <div class="champ">
            <label for="id_salle">Salle à modifier :</label>
            <select name="id_salle" id="id_salle" required>
                <option value="">-- Sélectionner une salle --</option>
                <?php foreach ($salles as $salle): ?>
                <option value="<?php echo (int)$salle['id_salle']; ?>">
                    <?php echo htmlspecialchars($salle['nom_salle'] . ' - ' . $salle['nom_bat']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

    </form>
