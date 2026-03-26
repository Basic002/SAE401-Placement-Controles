<link rel="stylesheet" href="public/css/s_stage1.css">

<div class="titrecontenu">Créer une salle — Étape 1</div>

<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <div class="erreur"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=crea_salle&etape=1">

        <div class="champ">
            <label for="nom_salle">Nom de la salle</label>
            <input
                type="text"
                id="nom_salle"
                name="nom_salle"
                required
                value="<?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
        </div>

        <div class="champ">
            <label for="etage">Étage</label>
            <select id="etage" name="etage">
                <?php
                $etageActuel = $sessionSalle['etage'] ?? '';
                $etages = [
                    0 => 'RDC',
                    1 => '1er',
                    2 => '2ème',
                    3 => '3ème',
                    4 => '4ème',
                ];
                foreach ($etages as $val => $libelle):
                    $selected = ((string)$etageActuel === (string)$val) ? ' selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $selected; ?>>
                        <?php echo htmlspecialchars($libelle, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="id_bat">Bâtiment</label>
            <select id="id_bat" name="id_bat">
                <option value="">— Choisir —</option>
                <?php foreach ($batiments as $bat):
                    $selected = ((string)($sessionSalle['id_bat'] ?? '') === (string)$bat['id_bat']) ? ' selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars((string)$bat['id_bat'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo $selected; ?>>
                        <?php echo htmlspecialchars($bat['nom_bat'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="id_dpt">Département</label>
            <select id="id_dpt" name="id_dpt">
                <option value="">— Choisir —</option>
                <?php foreach ($departements as $dpt):
                    $selected = ((string)($sessionSalle['id_dpt'] ?? '') === (string)$dpt['id_dpt']) ? ' selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars((string)$dpt['id_dpt'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo $selected; ?>>
                        <?php echo htmlspecialchars($dpt['nom_dpt'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="champ">
            <label for="intercal">Intercalaire</label>
            <select id="intercal" name="intercal">
                <?php
                $intercalActuel = $sessionSalle['intercal'] ?? 1;
                $intercalOptions = [1 => 'Oui', 0 => 'Non'];
                foreach ($intercalOptions as $val => $libelle):
                    $selected = ((string)$intercalActuel === (string)$val) ? ' selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $selected; ?>>
                        <?php echo htmlspecialchars($libelle, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="actions">
            <button type="submit">Suivant →</button>
        </div>

    </form>

</div>
