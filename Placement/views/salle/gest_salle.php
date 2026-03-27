<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_gest_salle.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Gestion des salles</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <!-- BOUTON CREATION SALLE -->
    <div id="btncrea">Créer une salle</div>

    <!-- BLOC CREATION SALLE -->
    <div id="bloccreasal" style="display:none">
        <a href="index.php?action=crea_salle">
            <button type="button">Créer une salle</button>
        </a>
        <button type="button" onclick="document.getElementById('bloccreasal').style.display='none';">Annuler</button>
    </div>

    <!-- BOUTON MODIFICATION SALLE -->
    <div id="btnmodif">Modifier une salle</div>

    <!-- BLOC AFFICHAGE TITRE TABLEAU -->
    <?php if (!empty($salles)): ?>
    <div id="bloctitre">
        <div class="nomtitre">Nom</div>
        <div class="nomtitre">Bâtiment</div>
        <div class="nomtitre">Étage</div>
        <div class="nomtitre">Département</div>
        <div class="nomtitre">Capacité</div>
        <div class="nomtitre">Actions</div>
    </div>
    <?php endif; ?>

    <!-- BLOC AFFICHAGE TABLEAU -->
    <?php foreach ($salles as $salle): ?>
    <div class="contenutab">
        <div class="nomtitre"><?php echo htmlspecialchars($salle['nom_salle']); ?></div>
        <div class="nomtitre"><?php echo htmlspecialchars($salle['nom_bat']); ?></div>
        <div class="nomtitre"><?php echo htmlspecialchars($salle['etage']); ?></div>
        <div class="nomtitre"><?php echo htmlspecialchars($salle['nom_dpt'] ?? 'N/A'); ?></div>
        <div class="nomtitre"><?php echo htmlspecialchars($salle['capacite']); ?></div>
        <div class="nomtitre">
            <a href="index.php?action=visu_salle&amp;id_salle=<?php echo (int)$salle['id_salle']; ?>">
                <img src="public/images/loupe.png" alt="Visualiser" class="img-action">
            </a>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- MESSAGE AUCUNE SALLE -->
    <?php if (empty($salles)): ?>
    <div id="nodata">
        <p>Aucune salle créée pour le moment.</p>
        <a href="index.php?action=crea_salle"><button>Créer une salle</button></a>
    </div>
    <?php endif; ?>

</div>
