<?php
$donnee     = $sessionSalle['donnee'] ?? '';
$placeOk    = substr_count($donnee, '1');
$placeHandi = substr_count($donnee, '2');
$capacite   = $placeOk + $placeHandi;

// Retrouve le nom du batiment depuis l'id
$nomBat = '';
foreach ($batiments as $bat) {
    if ((int)$bat['id_bat'] === (int)($sessionSalle['id_bat'] ?? -1)) {
        $nomBat = $bat['nom_bat'];
        break;
    }
}

// Retrouve le nom du departement depuis l'id
$nomDpt = '';
foreach ($departements as $dpt) {
    if ((int)$dpt['id_dpt'] === (int)($sessionSalle['id_dpt'] ?? -1)) {
        $nomDpt = $dpt['nom_dpt'];
        break;
    }
}

$etages = [0 => 'RDC', 1 => '1er', 2 => '2ème', 3 => '3ème', 4 => '4ème'];
$etageLabel = $etages[(int)($sessionSalle['etage'] ?? 0)] ?? (string)($sessionSalle['etage'] ?? '');
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_stage4.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Modifier une salle — Étape 6</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <h3><?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? ''); ?></h3>

    <!-- RECAPITULATIF -->
    <table class="recap">
        <tr>
            <th>Bâtiment</th>
            <td><?php echo htmlspecialchars($nomBat); ?></td>
        </tr>
        <tr>
            <th>Département</th>
            <td><?php echo htmlspecialchars($nomDpt); ?></td>
        </tr>
        <tr>
            <th>Étage</th>
            <td><?php echo htmlspecialchars($etageLabel); ?></td>
        </tr>
        <tr>
            <th>Intercalation</th>
            <td><?php echo (int)($sessionSalle['intercal'] ?? 0) ? 'Oui' : 'Non'; ?></td>
        </tr>
        <tr>
            <th>Capacité</th>
            <td>
                <?php echo $placeOk; ?> place<?php echo $placeOk > 1 ? 's' : ''; ?>
                <?php if ($placeHandi > 0): ?>
                (+ <?php echo $placeHandi; ?> place<?php echo $placeHandi > 1 ? 's' : ''; ?> PMR)
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <!-- FORMULAIRE D'ENREGISTREMENT -->
    <form method="POST" action="index.php?action=modif_salle&etape=6" id="formSave">
    </form>

</div>
