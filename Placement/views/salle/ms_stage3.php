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
$nbRang = count($rows);
$nbCol  = $nbRang > 0 ? strlen($rows[0]) : 0;
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_stage2.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Modifier une salle — Étape 3</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <h3><?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? ''); ?></h3>

    <!-- AFFICHAGE PLAN ACTUEL EN LECTURE SEULE -->
    <table id="TAB1">
        <?php foreach ($rows as $i => $row): ?>
        <tr id="<?php echo $i; ?>">
            <?php
            $cells = str_split($row);
            foreach ($cells as $j => $cell):
                $cls = cellClass((int)$cell);
            ?>
            <td class="<?php echo htmlspecialchars($cls); ?>"
                id="<?php echo $i . '-' . $j; ?>"></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="bureau">BUREAU</div>

    <!-- FORMULAIRE — passe nb_col/nb_rang au controleur -->
    <form method="POST" action="index.php?action=modif_salle&etape=3">
        <input type="hidden" name="nb_col"  value="<?php echo $nbCol; ?>">
        <input type="hidden" name="nb_rang" value="<?php echo $nbRang; ?>">
    </form>

</div>
