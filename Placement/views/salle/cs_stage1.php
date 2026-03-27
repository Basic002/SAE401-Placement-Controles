<link rel="stylesheet" href="public/css/s_stage1.css">

<?php if (!empty($erreur)): ?>
    <div class="erreur"><?php echo htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8'); ?></div>
<?php endif; ?>

<form method="POST" action="index.php?action=crea_salle&etape=1" id="formStage">

        <div class="champ">
            <label for="nomSalle">Nom de la salle</label>
            <input
                type="text"
                id="nomSalle"
                name="nom_salle"
                required
                value="<?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            >
            <span class="tooltip">Le nom doit avoir au moins 2 caractères</span>
        </div>

        <div class="champ">
            <label for="etageSalle">Étage</label>
            <select id="etageSalle" name="etage">
                <option value="A">— Choisir —</option>
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
            <span class="tooltip">Sélectionnez un étage</span>
        </div>

        <div class="champ">
            <label for="batSalle">Bâtiment</label>
            <select id="batSalle" name="id_bat">
                <option value="A">— Choisir —</option>
                <?php foreach ($batiments as $bat):
                    $selected = ((string)($sessionSalle['id_bat'] ?? '') === (string)$bat['id_bat']) ? ' selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars((string)$bat['id_bat'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo $selected; ?>>
                        <?php echo htmlspecialchars($bat['nom_bat'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="tooltip">Sélectionnez un bâtiment</span>
        </div>

        <div class="champ">
            <label for="dptSalle">Département</label>
            <select id="dptSalle" name="id_dpt">
                <option value="A">— Choisir —</option>
                <?php foreach ($departements as $dpt):
                    $selected = ((string)($sessionSalle['id_dpt'] ?? '') === (string)$dpt['id_dpt']) ? ' selected' : '';
                ?>
                    <option value="<?php echo htmlspecialchars((string)$dpt['id_dpt'], ENT_QUOTES, 'UTF-8'); ?>"<?php echo $selected; ?>>
                        <?php echo htmlspecialchars($dpt['nom_dpt'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="tooltip">Sélectionnez un département</span>
        </div>

    </form>
