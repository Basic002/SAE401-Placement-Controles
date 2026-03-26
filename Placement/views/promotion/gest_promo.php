<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_gest_promo.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Promotion</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

<?php if (!empty($erreur)): ?>
    <div class="bandeau-erreur"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (!empty($succes)): ?>
    <div class="bandeau-succes"><?= $succes ?></div>
<?php endif; ?>

    <!-- ONGLETS -->
    <div id="blocOnglet">
        <div id="onglet1" class="onglet"  style="background: gray">Créer promotion</div>
        <div id="onglet2" class="onglet2" style="background: gray">Importer promotion</div>
        <div id="onglet3" class="onglet2" style="display:none"></div>
    </div>

    <!-- ONGLET 1 : CREATION PROMOTION -->
    <div id="blocOnglet1" class="contenuonglet" style="display:none">
        <form action="index.php?action=gest_promo" method="post">
            <input type="hidden" name="_action" value="create">
            <label for="nom_promo">Nom &nbsp;&nbsp; </label>
            <input type="text" id="nom_promo" name="nom_promo"><br>
            <label for="annee">Année </label>
            <input type="text" id="annee" name="annee"><br>
            <label for="id_dpt">Département </label>
            <select id="id_dpt" name="id_dpt">
                <?php foreach ($departements as $dpt): ?>
                    <option value="<?= (int) $dpt['id_dpt'] ?>">
                        <?= htmlspecialchars($dpt['nom_dpt'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Ajouter promotion">
            <input type="button" value="Annuler" onclick="document.getElementById('blocOnglet1').style.display='none';">
        </form>
    </div>

    <!-- ONGLET 2 : caché (slot réservé par le CSS) -->
    <div id="blocOnglet2" class="contenuonglet" style="display:none"></div>

    <!-- ONGLET 3 : IMPORT CSV -->
    <div id="blocOnglet3" class="contenuonglet" style="display:none">
        <p style="text-align:left; margin-left:15px">
            Le fichier doit correspondre au schéma suivant, à partir de la ligne 1 :<br>
            Num;Groupe;Nom;Prenom<br>
            1;1;ALLARD;Martin<br>
            2;1;BASSAN;Bastien<br>
            <br>
            avec répétition éventuelle (groupe en colonne 2(B), 7(G), 12(L), etc.)
        </p>
        <form action="index.php?action=gest_promo" method="post" enctype="multipart/form-data">
            <input type="hidden" name="_action" value="import">
            <label for="id_promo_import">Promotion </label>
            <select id="id_promo_import" name="id_promo">
                <?php foreach ($promotions as $promo): ?>
                    <option value="<?= (int) $promo['id_promo'] ?>">
                        <?= htmlspecialchars($promo['nom_promo'] . ' ' . $promo['annee'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
            <input type="file" name="myFile"><br><br>
            <input type="submit" value="Importer promotion">
            <input type="button" value="Annuler" onclick="document.getElementById('blocOnglet3').style.display='none';">
        </form>
    </div>

    <!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
    <?php if (!empty($promotions)): ?>
        <div id="bloctitrepromo">
            <div class="nomtitrepromo">Promotion</div>
            <div class="nomtitrepromo">Département</div>
            <div class="nomtitrepromo">Année</div>
        </div>
    <?php endif; ?>

    <!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
    <form action="index.php?action=gest_promo" method="post">
        <input type="hidden" name="_action" value="update">

        <?php foreach ($promotions as $promo):
            $id      = (int) $promo['id_promo'];
            $nom     = htmlspecialchars($promo['nom_promo'], ENT_QUOTES, 'UTF-8');
            $nomDpt  = htmlspecialchars($promo['nom_dpt'],   ENT_QUOTES, 'UTF-8');
            $annee   = htmlspecialchars($promo['annee'],     ENT_QUOTES, 'UTF-8');
        ?>
            <div class="contenutabpromo">
                <div id="A<?= $id ?>" class="nomtitrepromo"><?= $nom ?></div>
                <div id="B<?= $id ?>" class="nomtitrepromo"><?= $nomDpt ?></div>
                <div id="C<?= $id ?>" class="nomtitrepromo"><?= $annee ?></div>

                <a href="#"><div onclick="modif_promo(<?= $id ?>, '<?= $nom ?>');" class="blocmodif" title="Modifier la promotion">
                    <img class="imgmodif" src="public/images/set.png">
                </div></a>
                <a href="#"><div onclick="suppr_promo(<?= $id ?>);" class="blocmodif" title="Supprimer la promotion">
                    <img class="imgmodif" src="public/images/delete.png">
                </div></a>
                <a href="index.php?action=gest_grp&amp;promo=<?= $id ?>"><div class="blocmodif" title="Gérer les groupes">
                    <img class="imgmodif" src="public/images/loupe.png">
                </div></a>
            </div>
        <?php endforeach; ?>

        <div id="barreValide" style="display:none">
            <input id="bouttonmodif" type="submit" value="Valider">
            <input type="button" value="Annuler" onclick="location.reload();">
        </div>

    </form>

    <!-- FORMS SUPPRESSION CACHEES -->
    <?php foreach ($promotions as $promo): ?>
        <?php $id = (int) $promo['id_promo']; ?>
        <form id="formDeletePromo<?= $id ?>" action="index.php?action=gest_promo" method="post" style="display:none">
            <input type="hidden" name="_action" value="delete">
            <input type="hidden" name="id_promo" value="<?= $id ?>">
        </form>
    <?php endforeach; ?>

</div>

<div id="fondOpaque" style="display:none"></div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/onglet_gest.js"></script>
<script>
var promos_dpts = <?= json_encode(array_map(fn($p) => ['id' => $p['id_promo'], 'dpt' => $p['nom_dpt'], 'nom' => $p['nom_promo'], 'annee' => $p['annee']], $promotions)) ?>;
var depts = <?= json_encode(array_map(fn($d) => ['v' => $d['id_dpt'], 'l' => $d['nom_dpt']], $departements)) ?>;

function suppr_promo(id) {
    if (confirm('Supprimer cette promotion et tous ses groupes/étudiants ?'))
        document.getElementById('formDeletePromo'+id).submit();
}
function modif_promo(id, nom) {
    var blocA = document.getElementById('A'+id);
    var blocB = document.getElementById('B'+id);
    var blocC = document.getElementById('C'+id);
    document.getElementById('fondOpaque').style.display='';
    document.getElementById('barreValide').style.display='';
    var promo = promos_dpts.find(p => p.id == id);
    blocA.innerHTML = '<input id="blocA" class="newsaisie" type="text" name="nom_promo" value="'+nom+'">'
        + '<input type="hidden" name="id_promo" value="'+id+'">';
    var selOpts = depts.map(d => '<option value="'+d.v+'"'+(promo && promo.dpt==d.l?' selected':'')+'>'+d.l+'</option>').join('');
    blocB.innerHTML = '<select name="id_dpt">'+selOpts+'</select>';
    blocC.innerHTML = '<input class="newsaisie" type="text" name="annee" value="'+(promo?promo.annee:'')+'">';
    document.getElementById('blocA').focus();
}
</script>
