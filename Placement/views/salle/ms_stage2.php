<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_ms_stage2.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Modifier une salle — Étape 2</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <!-- AFFICHAGE ERREUR -->
    <?php if (!empty($erreur)): ?>
    <div class="erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>

    <!-- FORMULAIRE METADONNEES SALLE -->
    <form method="POST" action="index.php?action=modif_salle&amp;etape=2">

        <div class="champ">
            <label for="nom_salle">Nom de la salle :</label>
            <input type="text" name="nom_salle" id="nom_salle"
                   value="<?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? ''); ?>"
                   required>
        </div>

        <div class="champ">
            <label for="etage">Étage :</label>
            <select name="etage" id="etage">
                <?php
                $etages = [0 => 'RDC', 1 => '1er', 2 => '2ème', 3 => '3ème', 4 => '4ème'];
                $etageActuel = $sessionSalle['etage'] ?? 0;
                foreach ($etages as $val => $label):
                ?>
                <option value="<?php echo $val; ?>" <?php echo ((int)$etageActuel === $val) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="id_bat">Bâtiment :</label>
            <select name="id_bat" id="id_bat">
                <?php foreach ($batiments as $bat): ?>
                <option value="<?php echo (int)$bat['id_bat']; ?>"
                    <?php echo (isset($sessionSalle['id_bat']) && (int)$sessionSalle['id_bat'] === (int)$bat['id_bat']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($bat['nom_bat']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="id_dpt">Département :</label>
            <select name="id_dpt" id="id_dpt">
                <?php foreach ($departements as $dpt): ?>
                <option value="<?php echo (int)$dpt['id_dpt']; ?>"
                    <?php echo (isset($sessionSalle['id_dpt']) && (int)$sessionSalle['id_dpt'] === (int)$dpt['id_dpt']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($dpt['nom_dpt']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="intercal">Intercalation :</label>
            <select name="intercal" id="intercal">
                <option value="1" <?php echo (isset($sessionSalle['intercal']) && (int)$sessionSalle['intercal'] === 1) ? 'selected' : ''; ?>>Oui</option>
                <option value="0" <?php echo (isset($sessionSalle['intercal']) && (int)$sessionSalle['intercal'] === 0) ? 'selected' : ''; ?>>Non</option>
            </select>
        </div>

    </form>

</div>
