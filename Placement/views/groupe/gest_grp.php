<link rel="stylesheet" href="public/css/s_gest_grp.css">

<div class="titrecontenu">
    <a href="index.php?action=gest_promo">
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

    <div id="blocOnglet">
        <div id="onglet1" class="onglet" style="background: #BEBEBE">Groupes</div>
        <div id="onglet2" class="onglet2" style="background: gray">Ajouter groupe</div>
    </div>

    <?php $idPromo = $promo['id_promo'] ?? 0; ?>

    <!-- Onglet 1 : liste des groupes -->
    <div id="blocOnglet1" class="contenuonglet" style="background: #BEBEBE">
        <div id="bloctitregrp2">
            <div class="nomtitregrp2">Nom du groupe</div>
            <div class="nomtitregrp2">Nombre d'étudiants</div>
        </div>

        <form action="index.php?action=gest_grp&promo=<?= $idPromo ?>" method="post">
            <input type="hidden" name="_action" value="update_groupe">

            <?php foreach ($groupes as $grp): ?>
                <div class="contenutabgrp2">
                    <div id="A<?= $grp['id_groupe'] ?>" class="nomtitregrp2">
                        <?= htmlspecialchars($grp['nom_groupe'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="nomtitregrp2">
                        <?= (int) ($grp['nb_etud'] ?? 0) ?>
                    </div>
                    <a href="index.php?action=gest_etud&promo=<?= $idPromo ?>&groupe=<?= $grp['id_groupe'] ?>">
                        <div class="blocmodif" title="Voir les étudiants">
                            <img class="imgmodif" src="public/images/loupe.png">
                        </div>
                    </a>
                    <a href="#">
                        <div onclick="modif_groupe(<?= $grp['id_groupe'] ?>, '<?= addslashes(htmlspecialchars($grp['nom_groupe'], ENT_QUOTES, 'UTF-8')) ?>');" class="blocmodif" title="Modifier">
                            <img class="imgmodif" src="public/images/set.png">
                        </div>
                    </a>
                    <a href="#">
                        <div onclick="suppr_groupe(<?= $grp['id_groupe'] ?>);" class="blocmodif" title="Supprimer">
                            <img class="imgmodif" src="public/images/delete.png">
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <div id="barreValide7" style="display:none">
                <input id="bouttonmodif" type="submit" value="Valider">
            </div>
        </form>
    </div>

    <!-- Onglet 2 : créer un groupe -->
    <div id="blocOnglet2" class="contenuonglet" style="display: none">
        <form action="index.php?action=gest_grp&promo=<?= $idPromo ?>" method="post">
            <input type="hidden" name="_action" value="create_groupe">
            <label for="nom_groupe">Nom du groupe </label>
            <input type="text" id="nom_groupe" name="nom_groupe" required><br>
            <button type="submit">Ajouter</button>
        </form>
    </div>

    <!-- Forms de suppression cachées -->
    <?php foreach ($groupes as $grp): ?>
        <form id="formDeleteGroupe<?= $grp['id_groupe'] ?>" action="index.php?action=gest_grp&promo=<?= $idPromo ?>" method="post" style="display:none">
            <input type="hidden" name="_action" value="delete_groupe">
            <input type="hidden" name="id_groupe" value="<?= $grp['id_groupe'] ?>">
        </form>
    <?php endforeach; ?>

</div>

<div id="fondOpaque" style="display:none"></div>

<script src="public/javascript/gest_grp.js"></script>
<script src="public/javascript/onglet_gest.js"></script>
<script>
function suppr_groupe(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce groupe (et ses étudiants) ?')) {
        document.getElementById('formDeleteGroupe' + id).submit();
    }
}
function modif_groupe(id, nom) {
    var blocA = document.getElementById('A' + id);
    document.getElementById('fondOpaque').style.display = '';
    document.getElementById('barreValide7').style.display = '';
    blocA.innerHTML = '<input id="blocA" class="newsaisie2" type="text" name="nom_groupe" value="' + nom + '">'
        + '<input type="hidden" name="id_groupe" value="' + id + '">';
    document.getElementById('blocA').focus();
}
</script>
