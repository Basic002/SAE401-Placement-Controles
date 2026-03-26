<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_gest_salle.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Gestion des salles</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <!-- BOUTONS D'ACTION -->
    <div class="actions-header">
        <a href="index.php?action=crea_salle" class="btn-action btn-primary">+ Créer une salle</a>
        <a href="index.php?action=modif_salle" class="btn-action btn-secondary">✎ Modifier une salle</a>
    </div>

    <!-- TABLEAU DES SALLES -->
    <?php if (!empty($salles)): ?>
    <table class="tableau-salles">
        <thead>
            <tr>
                <th class="col-nom">Nom</th>
                <th class="col-bat">Bâtiment</th>
                <th class="col-etage">Étage</th>
                <th class="col-dpt">Département</th>
                <th class="col-cap">Capacité</th>
                <th class="col-actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($salles as $salle): ?>
            <tr>
                <td class="col-nom">
                    <strong><?php echo htmlspecialchars($salle['nom_salle']); ?></strong>
                </td>
                <td class="col-bat"><?php echo htmlspecialchars($salle['nom_bat']); ?></td>
                <td class="col-etage"><?php echo htmlspecialchars($salle['etage']); ?></td>
                <td class="col-dpt"><?php echo htmlspecialchars($salle['nom_dpt'] ?? 'N/A'); ?></td>
                <td class="col-cap"><?php echo htmlspecialchars($salle['capacite']); ?> places</td>
                <td class="col-actions">
                    <a href="index.php?action=visu_salle&amp;id_salle=<?php echo (int)$salle['id_salle']; ?>" 
                       class="btn-view" title="Visualiser">
                        👁️
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="no-data">
        <p>Aucune salle créée pour le moment.</p>
        <a href="index.php?action=crea_salle" class="btn-action btn-primary">Créer la première salle</a>
    </div>
    <?php endif; ?>

</div>
