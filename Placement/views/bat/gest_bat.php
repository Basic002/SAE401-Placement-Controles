<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" href="public/css/s_gest_bat.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">B&acirc;timent</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <div class="message erreur"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($succes)): ?>
        <div class="message succes"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- BOUTON CREATION BATIMENT -->
    <div id="btncrea">Cr&eacute;er b&acirc;timent</div>

    <!-- BLOC CREATION BATIMENT -->
    <div id="bloccreabat" style="display:none">
        <form action="index.php?action=gest_bat" method="post">
            <input type="hidden" name="_action" value="create">
            <label for="nom_bat">Nom &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; </label>
            <input type="text" id="nom_bat" name="nom_bat"><br>
            <label for="ad_bat">Adresse </label>
            <input type="text" id="ad_bat" name="ad_bat"><br>
            <input type="submit" value="Ajouter">
            <input type="button" value="Annuler" onclick="document.getElementById('bloccreabat').style.display='none';">
        </form>
    </div>

    <!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
    <?php if (!empty($batiments)): ?>
        <div id="bloctitre">
            <div class="nomtitre">B&acirc;timent</div>
            <div class="nomtitre">Adresse</div>
        </div>
    <?php endif; ?>

    <!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
    <form action="index.php?action=gest_bat" method="post">
        <input type="hidden" name="_action" value="update">

        <?php foreach ($batiments as $bat): ?>
            <div class="contenutab">
                <div id="<?= 'A' . (int)$bat['id_bat'] ?>" class="nomtitre">
                    <?= htmlspecialchars($bat['nom_bat'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div id="<?= 'B' . (int)$bat['id_bat'] ?>" class="nomtitre">
                    <?= htmlspecialchars($bat['ad_bat'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div onclick="modif_bat(<?= (int)$bat['id_bat'] ?>, <?= htmlspecialchars(json_encode($bat['nom_bat']), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($bat['ad_bat']), ENT_QUOTES, 'UTF-8') ?>);" class="blocmodif"><img class="imgmodif" src="public/images/set.png"></div>
                <a href="#"><div onclick="suppr_bat(<?= (int)$bat['id_bat'] ?>);" class="blocmodif"><img class="imgmodif" src="public/images/delete.png"></div></a>
            </div>
        <?php endforeach; ?>

        <div id="barreValide" style="display:none">
            <input id="bouttonmodif" type="submit" value="Valider">
            <input type="button" value="Annuler" onclick="window.location='index.php?action=gest_bat';">
        </div>

    </form>

    <!-- FORMULAIRES DE SUPPRESSION CACHÉS (un par bâtiment) -->
    <?php foreach ($batiments as $bat): ?>
        <form id="formDelete<?= (int)$bat['id_bat'] ?>" action="index.php?action=gest_bat" method="post" style="display:none">
            <input type="hidden" name="_action" value="delete">
            <input type="hidden" name="id_bat" value="<?= (int)$bat['id_bat'] ?>">
        </form>
    <?php endforeach; ?>

</div>

<div id="fondOpaque" style="display:none"></div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/gest_bat.js"></script>
<script src="public/javascript/onglet_gest.js"></script>
<script>
// Override suppr_bat pour utiliser POST
function suppr_bat(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce bâtiment ?')) {
        document.getElementById('formDelete' + id).submit();
    }
}
// Override modif_bat pour les bons noms de champs
function modif_bat(id, nom, adresse) {
    document.getElementById('A' + id).innerHTML =
        '<input id="blocA" class="newsaisie" type="text" name="nom_bat" value="' + nom + '">'
        + '<input type="hidden" name="id_bat" value="' + id + '">';
    document.getElementById('B' + id).innerHTML =
        '<input id="blocB" class="newsaisie" type="text" name="ad_bat" value="' + adresse + '">';
    document.getElementById('fondOpaque').style.display = '';
    document.getElementById('barreValide').style.display = '';
    document.getElementById('blocA').focus();
}
</script>
