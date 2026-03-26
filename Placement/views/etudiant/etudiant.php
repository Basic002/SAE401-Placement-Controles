<link rel="stylesheet" href="public/css/s_etudiant.css">

<div class="titrecontenu">
    <a href="index.php?action=gest_grp&promo=<?= (int)($promo['id_promo'] ?? 0) ?>">
        <div class="blocretour" title="Retour"><img class="imgretour" src="public/images/fleche.png"></div>
    </a>
    <?php if ($promo): ?>
        <?= htmlspecialchars($promo['nom_promo'] . ' ' . ($promo['nom_dpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
    <?php endif; ?>
</div>

<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <p class="erreur"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if (!empty($succes)): ?>
        <p class="succes"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php $idPromo = (int)($promo['id_promo'] ?? 0); ?>

    <div id="blocOnglet">
        <div id="onglet1" class="onglet" style="background: #BEBEBE">Étudiants</div>
        <div id="onglet2" class="onglet2" style="background: gray">Ajouter étudiant</div>
    </div>

    <!-- Onglet 1 : liste des étudiants -->
    <div id="blocOnglet1" class="contenuonglet" style="background: #BEBEBE">

        <!-- Filtre par groupe -->
        <?php if (!empty($groupes)): ?>
        <div id="blocFiltreGroupe" style="margin-bottom:8px;">
            <strong>Groupe :</strong>
            <a href="index.php?action=gest_etud&promo=<?= $idPromo ?>">Tous</a>
            <?php foreach ($groupes as $grp): ?>
                <a href="index.php?action=gest_etud&promo=<?= $idPromo ?>&groupe=<?= (int)$grp['id_groupe'] ?>"
                   <?= ((int)($grp['id_groupe'] ?? 0) === $idGroupe) ? 'style="font-weight:bold"' : '' ?>>
                    <?= htmlspecialchars($grp['nom_groupe'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div id="bloctitreetud">
            <div class="nomtitreetud">Nom</div>
            <div class="nomtitreetud">Prénom</div>
            <div class="nomtitreetud">TT</div>
            <div class="nomtitreetud">MR</div>
            <div class="nomtitreetud">½ Gr.</div>
            <div class="nomtitreetud">Groupe</div>
        </div>

        <?php foreach ($etudiants as $etu): ?>
            <div class="contenutabetud">
                <div class="nomtitreetud"><?= htmlspecialchars($etu['nom_etudiant'],    ENT_QUOTES, 'UTF-8') ?></div>
                <div class="nomtitreetud"><?= htmlspecialchars($etu['prenom_etudiant'], ENT_QUOTES, 'UTF-8') ?></div>
                <div class="nomtitreetud"><?= $etu['tiers_temps']  ? 'Oui' : 'Non' ?></div>
                <div class="nomtitreetud"><?= $etu['mob_reduite']  ? 'Oui' : 'Non' ?></div>
                <div class="nomtitreetud"><?= $etu['demigr']       ? 'Oui' : 'Non' ?></div>
                <div class="nomtitreetud"><?= htmlspecialchars($etu['nom_groupe'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                <a href="#">
                    <div onclick="modif_etud(
                            <?= (int)$etu['id_etudiant'] ?>,
                            '<?= addslashes(htmlspecialchars($etu['nom_etudiant'],    ENT_QUOTES, 'UTF-8')) ?>',
                            '<?= addslashes(htmlspecialchars($etu['prenom_etudiant'], ENT_QUOTES, 'UTF-8')) ?>',
                            <?= (int)$etu['id_groupe']    ?>,
                            <?= (int)$etu['tiers_temps']  ?>,
                            <?= (int)$etu['mob_reduite']  ?>,
                            <?= (int)$etu['demigr']       ?>
                        );" class="blocmodif" title="Modifier">
                        <img class="imgmodif" src="public/images/set.png">
                    </div>
                </a>
                <a href="#">
                    <div onclick="suppr_etud(<?= (int)$etu['id_etudiant'] ?>);" class="blocmodif" title="Supprimer">
                        <img class="imgmodif" src="public/images/delete.png">
                    </div>
                </a>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- Onglet 2 : ajouter étudiant -->
    <div id="blocOnglet2" class="contenuonglet" style="display:none">
        <form action="index.php?action=gest_etud&promo=<?= $idPromo ?><?= $idGroupe ? '&groupe=' . $idGroupe : '' ?>" method="post">
            <input type="hidden" name="_action" value="create">
            <label for="nom_etudiant">Nom </label>
            <input type="text" id="nom_etudiant" name="nom_etudiant" required><br>
            <label for="prenom_etudiant">Prénom </label>
            <input type="text" id="prenom_etudiant" name="prenom_etudiant" required><br>
            <label for="crea_id_groupe">Groupe </label>
            <select id="crea_id_groupe" name="id_groupe" required>
                <option value="">-- Choisir --</option>
                <?php foreach ($groupes as $grp): ?>
                    <option value="<?= (int)$grp['id_groupe'] ?>"
                        <?= ((int)$grp['id_groupe'] === $idGroupe) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($grp['nom_groupe'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
            <label><input type="checkbox" name="tiers_temps" value="1"> Tiers-temps</label><br>
            <label><input type="checkbox" name="mob_reduite" value="1"> Mobilité réduite</label><br>
            <label><input type="checkbox" name="demigr" value="1"> Demi-groupe</label><br>
            <button type="submit">Ajouter</button>
        </form>
    </div>

    <!-- Formulaires de suppression cachés -->
    <?php foreach ($etudiants as $etu): ?>
        <form id="formDeleteEtud<?= (int)$etu['id_etudiant'] ?>"
              action="index.php?action=gest_etud&promo=<?= $idPromo ?><?= $idGroupe ? '&groupe=' . $idGroupe : '' ?>"
              method="post" style="display:none">
            <input type="hidden" name="_action" value="delete">
            <input type="hidden" name="id_etudiant" value="<?= (int)$etu['id_etudiant'] ?>">
        </form>
    <?php endforeach; ?>

    <!-- Formulaire de modification (modal) -->
    <div id="modalEditEtud" style="display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%);
         background:#fff; border:2px solid #666; padding:20px; z-index:1000; min-width:300px;">
        <h3>Modifier étudiant</h3>
        <form id="formUpdateEtud" action="index.php?action=gest_etud&promo=<?= $idPromo ?><?= $idGroupe ? '&groupe=' . $idGroupe : '' ?>" method="post">
            <input type="hidden" name="_action" value="update">
            <input type="hidden" id="upd_id" name="id_etudiant" value="">
            <label>Nom<br><input type="text" id="upd_nom" name="nom_etudiant" required></label><br><br>
            <label>Prénom<br><input type="text" id="upd_prenom" name="prenom_etudiant" required></label><br><br>
            <label>Groupe<br>
                <select id="upd_groupe" name="id_groupe" required>
                    <?php foreach ($groupes as $grp): ?>
                        <option value="<?= (int)$grp['id_groupe'] ?>">
                            <?= htmlspecialchars($grp['nom_groupe'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label><br><br>
            <label><input type="checkbox" id="upd_tt" name="tiers_temps" value="1"> Tiers-temps</label><br>
            <label><input type="checkbox" id="upd_mr" name="mob_reduite" value="1"> Mobilité réduite</label><br>
            <label><input type="checkbox" id="upd_dg" name="demigr" value="1"> Demi-groupe</label><br><br>
            <button type="submit">Valider</button>
            <button type="button" onclick="closeModal()">Annuler</button>
        </form>
    </div>

</div>

<div id="fondOpaque" style="display:none"></div>

<script src="public/javascript/onglet_gest.js"></script>
<script>
function suppr_etud(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet étudiant ?')) {
        document.getElementById('formDeleteEtud' + id).submit();
    }
}
function modif_etud(id, nom, prenom, idGrp, tt, mr, demigr) {
    document.getElementById('upd_id').value     = id;
    document.getElementById('upd_nom').value    = nom;
    document.getElementById('upd_prenom').value = prenom;
    document.getElementById('upd_groupe').value = idGrp;
    document.getElementById('upd_tt').checked   = tt == 1;
    document.getElementById('upd_mr').checked   = mr == 1;
    document.getElementById('upd_dg').checked   = demigr == 1;
    document.getElementById('modalEditEtud').style.display = '';
    document.getElementById('fondOpaque').style.display    = '';
}
function closeModal() {
    document.getElementById('modalEditEtud').style.display = 'none';
    document.getElementById('fondOpaque').style.display    = 'none';
}
document.getElementById('fondOpaque').addEventListener('click', closeModal);
</script>
