<?php
function cellClass(int $val): string {
    return match($val) {
        0 => 'couloir',
        2 => 'placeHandi',
        3 => 'placeInex',
        default => 'placeOk',
    };
}

$donnee   = $sessionSalle['donnee'] ?? '';
$rows     = array_filter(explode('-', $donnee), fn($r) => $r !== '');
$rows     = array_values($rows);
$placeOk  = substr_count($donnee, '1');
$placeHandi = substr_count($donnee, '2');
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_stage4.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Modifier une salle — Étape 5</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

    <h3><?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? ''); ?></h3>

    <!-- CAPACITE -->
    <p>
        <strong><?php echo $placeOk; ?></strong> places
        <?php if ($placeHandi > 0): ?>
        (+ <strong><?php echo $placeHandi; ?></strong> place<?php echo $placeHandi > 1 ? 's' : ''; ?> PMR)
        <?php endif; ?>
    </p>

    <!-- APERCU GRILLE EN LECTURE SEULE -->
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

    <!-- FORMULAIRE DE VALIDATION -->
    <form method="POST" action="index.php?action=modif_salle&etape=5">
        <input type="hidden" name="donnee" value="<?php echo htmlspecialchars($donnee); ?>">
    </form>

</div>
