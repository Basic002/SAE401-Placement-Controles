<?php
function cellClass($val) {
    switch ((string)$val) {
        case '0': return 'couloir';
        case '1': return 'placeOk';
        case '2': return 'placeHandi';
        case '3': return 'placeInex';
        default:  return 'placeOk';
    }
}
?>
<link rel="stylesheet" href="public/css/s_stage4.css">

<div class="titrecontenu">Créer une salle — Étape 4</div>

<div class="contenu">

    <h3><?php echo htmlspecialchars($sessionSalle['nom_salle'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>

    <p>
        Capacité : <strong><?php echo htmlspecialchars((string)($sessionSalle['capacite'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></strong> places
    </p>

    <?php
    $donnee = $sessionSalle['donnee'] ?? '';
    $lignes = explode('-', $donnee);
    // Remove trailing empty element produced by trailing '-'
    if (end($lignes) === '') {
        array_pop($lignes);
    }
    ?>

    <table id="TAB1">
        <?php foreach ($lignes as $rowIndex => $ligne):
            $cellules = str_split($ligne);
        ?>
            <tr id="<?php echo (int)$rowIndex; ?>">
                <?php foreach ($cellules as $colIndex => $val): ?>
                    <td id="<?php echo (int)$rowIndex . '-' . (int)$colIndex; ?>"
                        class="<?php echo htmlspecialchars(cellClass($val), ENT_QUOTES, 'UTF-8'); ?>"></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="bureau">BUREAU</div>

    <form method="POST" action="index.php?action=crea_salle&etape=4">
        <div class="actions">
            <a href="index.php?action=crea_salle&etape=3">← Retour</a>
            <button type="submit">Enregistrer</button>
        </div>
    </form>

</div>
