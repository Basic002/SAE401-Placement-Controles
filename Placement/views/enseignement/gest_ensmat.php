<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_gest_ensmat.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Enseignement</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

<?php if (!empty($erreur)): ?>
    <div class="bandeau-erreur"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($succes)): ?>
    <div class="bandeau-succes"><?= $succes ?></div>
<?php endif; ?>

    <!-- BOUTON CREATION ENSEIGNEMENT -->
    <div id="btncrea">Créer Enseignement</div>

    <!-- BLOC CREATION ENSEIGNEMENT -->
    <div id="bloccreaens" style="display:none">
        <form action="index.php?action=gest_ensmat" method="post">
            <input type="hidden" name="_action" value="create">

            <label for="id_ens">Nom </label>
            <select id="id_ens" name="id_ens">
                <?php foreach ($enseignants as $ens): ?>
                    <option value="<?= (int) $ens['id_ens'] ?>">
                        <?= htmlspecialchars($ens['nom_ens'] . ' ' . $ens['prenom_ens'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <label for="id_mat">Matière </label>
            <select id="id_mat" name="id_mat">
                <?php foreach ($matieres as $mat): ?>
                    <option value="<?= (int) $mat['id_mat'] ?>">
                        <?= htmlspecialchars($mat['nom_dpt'] . ' ' . $mat['nom_promo'] . ' - ' . $mat['nom_mat'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <input type="submit" value="Ajouter">
            <input type="button" value="Annuler" onclick="document.getElementById('bloccreaens').style.display='none';">
        </form>
    </div>

    <!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
    <?php if (!empty($associations)): ?>
        <div id="bloctitre" style="width: 740px">
            <div style="width: 349px" class="nomtitreens">Nom Prénom</div>
            <div style="width: 300px" class="nomtitreens">Matière</div>
            <div style="width: 85px"  class="nomtitreens">Promotion</div>
        </div>
    <?php endif; ?>

    <!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
    <?php foreach ($associations as $i => $assoc):
        $idEns  = (int) $assoc['id_ens'];
        $idMat  = (int) $assoc['id_mat'];
        $nomPrenom = htmlspecialchars($assoc['nom_ens'] . ' ' . $assoc['prenom_ens'], ENT_QUOTES, 'UTF-8');
        $nomMat    = htmlspecialchars($assoc['nom_mat'],   ENT_QUOTES, 'UTF-8');
        $nomPromo  = htmlspecialchars($assoc['nom_promo'], ENT_QUOTES, 'UTF-8');
    ?>
        <div class="contenutab">
            <div style="width: 349px" id="A<?= $i ?>" class="nomtitreens"><?= $nomPrenom ?></div>
            <div style="width: 300px" id="B<?= $i ?>" class="nomtitreens"><?= $nomMat ?></div>
            <div style="width: 85px"  id="C<?= $i ?>" class="nomtitreens"><?= $nomPromo ?></div>

            <a href="#"><div onclick="suppr_ensmat(<?= $idMat ?>, <?= $idEns ?>);" class="blocmodif">
                <img class="imgmodif" src="public/images/delete.png">
            </div></a>
        </div>
    <?php endforeach; ?>

    <!-- FORMS SUPPRESSION CACHEES -->
    <?php foreach ($associations as $assoc):
        $idEns = (int) $assoc['id_ens'];
        $idMat = (int) $assoc['id_mat'];
    ?>
        <form id="formDelete_<?= $idEns ?>_<?= $idMat ?>" action="index.php?action=gest_ensmat" method="post" style="display:none">
            <input type="hidden" name="_action" value="delete">
            <input type="hidden" name="id_mat" value="<?= $idMat ?>">
            <input type="hidden" name="id_ens" value="<?= $idEns ?>">
        </form>
    <?php endforeach; ?>

</div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/onglet_gest.js"></script>
<script>
function suppr_ensmat(idMat, idEns) {
    if (confirm('Supprimer cette association ?')) {
        document.getElementById('formDelete_'+idEns+'_'+idMat).submit();
    }
}
</script>
