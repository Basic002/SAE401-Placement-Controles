<link rel="stylesheet" href="public/css/s_up_stage1.css">

<div class="titrecontenu">Étape 1 — Informations du devoir</div>

<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <p class="erreur"><?php echo htmlspecialchars($erreur); ?></p>
    <?php endif; ?>

    <form id="formStage1" method="POST" action="index.php?action=placement_stage2">

        <div class="form-group">
            <label for="date_devoir">Date du devoir</label>
            <input type="date" name="date_devoir" id="date_devoir"
                   value="<?php echo htmlspecialchars($sessionUp['date_devoir'] ?? ''); ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="heure_debut">Heure de début</label>
            <input type="time" name="heure_debut" id="heure_debut"
                   value="<?php echo htmlspecialchars($sessionUp['heure_debut'] ?? ''); ?>"
                   required>
        </div>

        <div class="form-group">
            <label for="duree">Durée</label>
            <input type="time" name="duree" id="duree"
                   value="<?php echo htmlspecialchars($sessionUp['duree'] ?? ''); ?>"
                   required>
        </div>

        <hr>
        <h3>Combinaisons promo / groupe / salle / matière</h3>

        <div class="form-group">
            <label for="sel_promo">Promotion</label>
            <select id="sel_promo" name="id_promo" onchange="grDynamique()">
                <option value="">-- Promotion --</option>
                <?php foreach ($promotions as $promo): ?>
                    <option value="<?php echo (int)$promo['id_promo']; ?>">
                        <?php echo htmlspecialchars($promo['nom_dpt'] . ' ' . $promo['nom_promo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="sel_groupe">Groupe</label>
            <select id="sel_groupe" name="id_groupe" style="display:none">
                <option value="0">Toute la promo</option>
            </select>
        </div>

        <div class="form-group">
            <label for="sel_matiere">Matière</label>
            <select id="sel_matiere" name="id_mat" style="display:none">
                <option value="">-- Matière --</option>
            </select>
        </div>

        <div class="form-group">
            <label for="sel_salle">Salle</label>
            <select id="sel_salle" name="id_salle" onchange="affBtn()">
                <option value="">-- Salle --</option>
                <?php foreach ($salles as $salle): ?>
                    <option value="<?php echo (int)$salle['id_salle']; ?>">
                        <?php echo htmlspecialchars($salle['nom_salle']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="button" id="btnAddCombi" style="display:none" onclick="recupCombi()">Ajouter la combinaison</button>

        <div id="tabRecap"></div>

        <div class="form-nav">
            <a href="index.php?action=util_placement">&larr; Retour</a>
            <button type="submit" id="btnSuivant" disabled>Suivant &rarr;</button>
        </div>

    </form>

</div>

<script src="public/javascript/up_stage1.js"></script>
