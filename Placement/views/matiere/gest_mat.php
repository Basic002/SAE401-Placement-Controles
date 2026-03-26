<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" href="public/css/s_gest_mat.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Mati&egrave;res</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <?php if (!empty($erreur)): ?>
        <div class="message erreur"><?= htmlspecialchars($erreur, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($succes)): ?>
        <div class="message succes"><?= htmlspecialchars($succes, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <!-- BOUTON CREATION MATIERE -->
    <div id="btncrea">Cr&eacute;er mati&egrave;re</div>

    <!-- BLOC CREATION MATIERE -->
    <div id="bloccreamat" style="display:none">
        <form action="index.php?action=gest_mat" method="post">
            <input type="hidden" name="_action" value="create">
            <label for="nom_mat">Nom </label>
            <input type="text" id="nom_mat" name="nom_mat"><br>
            <label for="id_promo">Promotion </label>
            <select id="id_promo" name="id_promo">
                <?php foreach ($promotions as $promo): ?>
                    <option value="<?= (int)$promo['id_promo'] ?>">
                        <?= htmlspecialchars($promo['nom_dpt'] . ' ' . $promo['nom_promo'] . ' ' . $promo['annee'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
            <input type="submit" value="Ajouter">
            <input type="button" value="Annuler" onclick="document.getElementById('bloccreamat').style.display='none';">
        </form>
    </div>

    <!-- ######### AFFICHAGE TITRE TABLEAU ######### -->
    <?php if (!empty($matieres)): ?>
        <div id="bloctitre">
            <div class="nomtitre">Mati&egrave;re</div>
            <div class="nomtitre">Promotion</div>
        </div>
    <?php endif; ?>

    <!-- ######### AFFICHAGE CONTENU TABLEAU ######## -->
    <form action="index.php?action=gest_mat" method="post">
        <input type="hidden" name="_action" value="update">

        <?php foreach ($matieres as $mat): ?>
            <div class="contenutab">
                <div id="<?= 'A' . (int)$mat['id_mat'] ?>" class="nomtitre">
                    <?= htmlspecialchars($mat['nom_mat'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div id="<?= 'B' . (int)$mat['id_mat'] ?>" class="nomtitre">
                    <?= htmlspecialchars($mat['nom_promo'] . ' ' . $mat['nom_dpt'] . ' ' . $mat['annee'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div onclick="modif_mat(<?= (int)$mat['id_mat'] ?>, <?= htmlspecialchars(json_encode($mat['nom_mat']), ENT_QUOTES, 'UTF-8') ?>);" class="blocmodif"><img class="imgmodif" src="public/images/set.png"></div>
                <a href="#"><div onclick="suppr_mat(<?= (int)$mat['id_mat'] ?>);" class="blocmodif"><img class="imgmodif" src="public/images/delete.png"></div></a>
            </div>
        <?php endforeach; ?>

        <div id="barreValide" style="display:none">
            <input id="bouttonmodif" type="submit" value="Valider">
            <input type="button" value="Annuler" onclick="window.location='index.php?action=gest_mat';">
        </div>

    </form>

    <!-- FORMULAIRES DE SUPPRESSION CACHÉS (un par matière) -->
    <?php foreach ($matieres as $mat): ?>
        <form id="formDelete<?= (int)$mat['id_mat'] ?>" action="index.php?action=gest_mat" method="post" style="display:none">
            <input type="hidden" name="_action" value="delete">
            <input type="hidden" name="id_mat" value="<?= (int)$mat['id_mat'] ?>">
        </form>
    <?php endforeach; ?>

</div>

<div id="fondOpaque" style="display:none"></div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/gest_mat.js"></script>
<script src="public/javascript/onglet_gest.js"></script>
<script>
// Données promotions disponibles pour le select de modification
var promOptions = <?= json_encode(array_map(fn($p) => ['v' => $p['id_promo'], 'l' => $p['nom_dpt'] . ' ' . $p['nom_promo'] . ' ' . $p['annee']], $promotions)) ?>;

// Override suppr_mat pour utiliser POST
function suppr_mat(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette matière ?')) {
        document.getElementById('formDelete' + id).submit();
    }
}
// Override modif_mat pour les bons noms de champs
function modif_mat(id, nom) {
    // Générer le HTML du select à partir des options
    var selectHtml = '<div id="listeDer"><select id="listeDer2" name="id_promo">';
    for (var i = 0; i < promOptions.length; i++) {
        selectHtml += '<option value="' + promOptions[i].v + '">' + promOptions[i].l + '</option>';
    }
    selectHtml += '</select></div>';

    document.getElementById('A' + id).innerHTML =
        '<input id="blocA" class="newsaisie" type="text" name="nom_mat" value="' + nom + '">'
        + '<input type="hidden" name="id_mat" value="' + id + '">';
    document.getElementById('B' + id).innerHTML = selectHtml;

    document.getElementById('fondOpaque').style.display = '';
    document.getElementById('barreValide').style.display = '';
    document.getElementById('blocA').focus();
}
</script>

