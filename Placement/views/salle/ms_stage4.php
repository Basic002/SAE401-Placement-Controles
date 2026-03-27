<?php
function cellClass(int $val): string {
    return match($val) {
        0 => 'couloir',
        2 => 'placeHandi',
        3 => 'placeInex',
        default => 'placeOk',
    };
}

$rows   = array_filter(explode('-', $sessionSalle['donnee'] ?? ''), fn($r) => $r !== '');
$rows   = array_values($rows);
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_stage3.css">

<h3><?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? ''); ?></h3>

<!-- CHOIX DU TYPE DE CELLULE -->
<div class="radio-groupe">
    <label>
        <input type="radio" name="choixEtat" id="radio_couloir" value="0">
        Couloir
    </label>
    <label>
        <input type="radio" name="choixEtat" id="radio_placeOk" value="1" checked>
        Place normale
    </label>
    <label>
        <input type="radio" name="choixEtat" id="radio_handi" value="2">
        Place PMR
    </label>
    <label>
        <input type="radio" name="choixEtat" id="radio_inex" value="3">
        Place inexistante
    </label>
</div>

<!-- GRILLE EDITABLE -->
<table id="TAB1">
    <?php foreach ($rows as $i => $row): ?>
    <tr id="<?php echo $i; ?>">
        <?php
        $cells = str_split($row);
        foreach ($cells as $j => $cell):
            $cls = cellClass((int)$cell);
        ?>
        <td class="<?php echo htmlspecialchars($cls); ?>"
            id="<?php echo $i . '-' . $j; ?>"
            onclick="modifEtat(this)"></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</table>

<div class="bureau">BUREAU</div>

<!-- FORMULAIRE DE SOUMISSION -->
<form method="POST" action="index.php?action=modif_salle&etape=4">
    <input type="hidden" id="donnee" name="donnee" value="">
</form>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/crea_salle_s3.js"></script>
