<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" href="public/css/s_gest_dpt.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">D&eacute;partement</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <div class="message erreur"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($succes)): ?>
        <div class="message succes"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- BOUTON CREATION DEPARTEMENT -->
    <div id="btncrea">Cr&eacute;er d&eacute;partement</div>

    <!-- BLOC CREATION DEPARTEMENT -->
    <div id="bloccreadpt" style="display:<?= !empty($reopenCreateForm) ? 'block' : 'none' ?>">
        <form action="index.php?action=gest_dpt" method="post">
            <input type="hidden" name="_action" value="create">
            <label for="nom_dpt">Nom </label>
            <input type="text" id="nom_dpt" name="nom_dpt" required autocomplete="off"><br>
            <input type="submit" value="Ajouter">
            <input type="button" value="Annuler" onclick="document.getElementById('bloccreadpt').style.display='none';">
        </form>
    </div>

    <!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
    <?php if (!empty($departements)): ?>
        <div id="bloctitre">
            <div class="nomtitredpt">D&eacute;partement</div>
        </div>
    <?php endif; ?>

    <!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
    <form action="index.php?action=gest_dpt" method="post">
        <input type="hidden" name="_action" value="update">

        <?php foreach ($departements as $dpt): ?>
            <div class="contenutab">
                <div id="<?= 'A' . (int)$dpt['id_dpt'] ?>" class="nomtitredpt">
                    <?= htmlspecialchars($dpt['nom_dpt'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <a href="#"><div onclick="modif_dpt(<?= (int)$dpt['id_dpt'] ?>, <?= htmlspecialchars(json_encode($dpt['nom_dpt']), ENT_QUOTES, 'UTF-8') ?>);" class="blocmodif"><img class="imgmodif" src="public/images/set.png"></div></a>
                <a href="#"><div onclick="suppr_dpt(<?= (int)$dpt['id_dpt'] ?>);" class="blocmodif"><img class="imgmodif" src="public/images/delete.png"></div></a>
            </div>
        <?php endforeach; ?>

        <div id="barreValide" style="display:none">
            <input id="bouttonmodif" type="submit" value="Valider">
            <input type="button" value="Annuler" onclick="window.location='index.php?action=gest_dpt';">
        </div>

    </form>

    <!-- FORMULAIRES DE SUPPRESSION CACHÉS (un par département) -->
    <?php foreach ($departements as $dpt): ?>
        <form id="formDelete<?= (int)$dpt['id_dpt'] ?>" action="index.php?action=gest_dpt" method="post" style="display:none">
            <input type="hidden" name="_action" value="delete">
            <input type="hidden" name="id_dpt" value="<?= (int)$dpt['id_dpt'] ?>">
        </form>
    <?php endforeach; ?>

</div>

<div id="fondOpaque" style="display:none"></div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/gest_dpt.js"></script>
<script src="public/javascript/onglet_gest.js"></script>
<script>
// Override suppr_dpt pour utiliser POST
function suppr_dpt(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce département ?')) {
        document.getElementById('formDelete' + id).submit();
    }
}
// Override modif_dpt pour les bons noms de champs
function modif_dpt(id, nom) {
    document.getElementById('A' + id).innerHTML =
        '<input id="blocA" class="newsaisie" type="text" name="nom_dpt" value="' + nom + '">'
        + '<input type="hidden" name="id_dpt" value="' + id + '">';
    document.getElementById('fondOpaque').style.display = '';
    document.getElementById('barreValide').style.display = '';
    document.getElementById('blocA').focus();
}
</script>
