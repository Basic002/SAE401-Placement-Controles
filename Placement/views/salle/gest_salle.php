<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_gest_salle.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Gestion des salles</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <!-- BOUTONS D'ACTION -->
    <div class="actions">
        <a href="index.php?action=crea_salle" class="btn-action">Créer une salle</a>
        <a href="index.php?action=modif_salle" class="btn-action">Modifier une salle</a>
    </div>

    <!-- ENTETE TABLEAU -->
    <div class="ligne-entete">
        <div class="col-nom">Nom</div>
        <div class="col-bat">Bâtiment</div>
        <div class="col-etage">Étage</div>
        <div class="col-cap">Capacité</div>
        <div class="col-actions">Actions</div>
    </div>

    <!-- LISTE DES SALLES -->
    <?php foreach ($salles as $salle): ?>
    <div class="ligne-salle">
        <div class="col-nom"><?php echo htmlspecialchars($salle['nom_salle']); ?></div>
        <div class="col-bat"><?php echo htmlspecialchars($salle['nom_bat']); ?></div>
        <div class="col-etage"><?php echo htmlspecialchars($salle['etage']); ?></div>
        <div class="col-cap"><?php echo htmlspecialchars($salle['capacite']); ?></div>
        <div class="col-actions">
            <a href="index.php?action=visu_salle&amp;id_salle=<?php echo (int)$salle['id_salle']; ?>">
                <img src="public/images/loupe.png" alt="Visualiser" class="img-action">
            </a>
        </div>
    </div>
    <?php endforeach; ?>

</div>
