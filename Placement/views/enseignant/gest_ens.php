<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_gest_ens.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Enseignant</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

<?php if (!empty($erreur)): ?>
    <div class="bandeau-erreur"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($succes)): ?>
    <div class="bandeau-succes"><?= $succes ?></div>
<?php endif; ?>

    <!-- BOUTON CREATION ENSEIGNANT -->
    <div id="btncrea">Créer Enseignant</div>

    <!-- BLOC CREATION ENSEIGNANT -->
    <div id="bloccreaens" style="display:none">
        <form action="index.php?action=gest_ens" method="post">
            <input type="hidden" name="_action" value="create">
            <label for="nom_ens">Nom &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input type="text" id="nom_ens" name="nom_ens"><br>
            <label for="prenom_ens">Prénom </label>
            <input type="text" id="prenom_ens" name="prenom_ens"><br>
            <label for="sexe">Sexe </label>
            <select id="sexe" name="sexe">
                <option value="F">Femme</option>
                <option value="M">Homme</option>
            </select><br>
            <input type="submit" value="Ajouter">
            <input type="button" value="Annuler" onclick="document.getElementById('bloccreaens').style.display='none';">
        </form>
    </div>

    <!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
    <?php if (!empty($enseignants)): ?>
        <div id="bloctitre">
            <div style="width: 210px" class="nomtitreens">Nom</div>
            <div style="width: 210px" class="nomtitreens">Prénom</div>
            <div style="width: 100px" class="nomtitreens">Sexe</div>
            <div style="width: 174px" class="nomtitreens">Login</div>
        </div>
    <?php endif; ?>

    <!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
    <form action="index.php?action=gest_ens" method="post">
        <input type="hidden" name="_action" value="update">

        <?php foreach ($enseignants as $ens): ?>
            <?php
                $id  = (int) $ens['id_ens'];
                $nom = htmlspecialchars($ens['nom_ens'],    ENT_QUOTES, 'UTF-8');
                $pre = htmlspecialchars($ens['prenom_ens'], ENT_QUOTES, 'UTF-8');
                $sex = htmlspecialchars($ens['sexe'],       ENT_QUOTES, 'UTF-8');
                $log = htmlspecialchars($ens['login'],      ENT_QUOTES, 'UTF-8');
            ?>
            <div class="contenutab">
                <div style="width: 210px" id="A<?= $id ?>" class="nomtitreens"><?= $nom ?></div>
                <div style="width: 210px" id="B<?= $id ?>" class="nomtitreens"><?= $pre ?></div>
                <div style="width: 100px" id="C<?= $id ?>" class="nomtitreens"><?= $sex ?></div>
                <div style="width: 174px" id="D<?= $id ?>" class="nomtitreens"><?= $log ?></div>

                <a href="#"><div onclick="modif_ens(<?= $id ?>, '<?= $nom ?>');" class="blocmodif">
                    <img class="imgmodif" src="public/images/set.png">
                </div></a>
                <a href="#"><div onclick="suppr_ens(<?= $id ?>);" class="blocmodif">
                    <img class="imgmodif" src="public/images/delete.png">
                </div></a>
            </div>
        <?php endforeach; ?>

        <div id="barreValide" style="display:none">
            <input id="bouttonmodif" type="submit" value="Valider">
            <input type="button" value="Annuler" onclick="location.reload();">
        </div>

    </form>

    <!-- FORMS SUPPRESSION CACHEES -->
    <?php foreach ($enseignants as $ens): ?>
        <?php $id = (int) $ens['id_ens']; ?>
        <form id="formDelete<?= $id ?>" action="index.php?action=gest_ens" method="post" style="display:none">
            <input type="hidden" name="_action" value="delete">
            <input type="hidden" name="id_ens" value="<?= $id ?>">
        </form>
    <?php endforeach; ?>

    <a href="public/export_pdf_ens.php" target="_blank" style="text-decoration:none; color:black;">
        <img width="30px" height="30px" src="public/images/iconpdf.jpg">
    </a>

</div>

<div id="fondOpaque" style="display:none"></div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/onglet_gest.js"></script>
<script>
function suppr_ens(id) {
    if (confirm('Êtes-vous sûr ?')) document.getElementById('formDelete'+id).submit();
}
function modif_ens(id, nom) {
    var e  = document.getElementById('A'+id);
    var pe = document.getElementById('B'+id);
    var se = document.getElementById('C'+id);
    var lg = document.getElementById('D'+id);
    document.getElementById('fondOpaque').style.display='';
    document.getElementById('barreValide').style.display='';
    e.innerHTML='<input id="blocA" class="newsaisie" style="width:200px" type="text" name="nom_ens" value="'+nom+'">'
        +'<input type="hidden" name="id_ens" value="'+id+'">';
    pe.innerHTML='<input class="newsaisie" style="width:200px" type="text" name="prenom_ens" value="'+pe.innerHTML+'">';
    se.innerHTML='<select style="width:90px" name="sexe"><option value="F">Femme</option><option value="M">Homme</option></select>';
    lg.innerHTML='<input class="newsaisie" style="width:164px" type="text" name="login" value="'+lg.innerHTML+'">';
    document.getElementById('blocA').focus();
}
</script>
