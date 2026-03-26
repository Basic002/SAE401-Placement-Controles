<link rel="stylesheet" href="public/css/s_stage3.css">
<link rel="stylesheet" href="public/css/s_stage4.css">

<div class="titrecontenu">
    <a href="index.php?action=gest_salle">
        <div class="blocretour" title="Retour"><img class="imgretour" src="public/images/fleche.png"></div>
    </a>
    Salle : <?= htmlspecialchars($salle['nom_salle'], ENT_QUOTES, 'UTF-8') ?>
    <?php if ($idDevoir > 0): ?>
        — Placement #<?= (int)$idDevoir ?>
    <?php endif; ?>
</div>

<div class="contenu">

<?php
// Build a lookup: "place_x,place_y" => [nom_etudiant, prenom_etudiant, tiers_temps, mob_reduite]
$lookup = [];
foreach ($placements as $p) {
    $lookup[(int)$p['place_x'] . ',' . (int)$p['place_y']] = $p;
}

// Cell class helper
function visuCellClass(int $val): string {
    return match($val) {
        0       => 'couloir',
        2       => 'placeHandi',
        3       => 'placeInex',
        default => 'placeOk',
    };
}
?>

    <p>
        <strong>Bâtiment :</strong> <?= htmlspecialchars($salle['nom_bat'] ?? '', ENT_QUOTES, 'UTF-8') ?> &nbsp;
        <strong>Étage :</strong> <?= (int)$salle['etage'] ?> &nbsp;
        <strong>Capacité :</strong> <?= (int)$salle['capacite'] ?> places
        <?php if ($salle['intercal']): ?>&nbsp; <em>(avec intercalation)</em><?php endif; ?>
    </p>

    <center>
        <table id="TAB1">
            <?php foreach ($grille as $i => $row): ?>
                <tr id="<?= $i ?>">
                    <?php foreach ($row as $j => $val): ?>
                        <?php
                            $key    = $i . ',' . $j;
                            $placed = $lookup[$key] ?? null;
                            $class  = visuCellClass($val);
                            if ($placed && $class !== 'couloir' && $class !== 'placeInex') {
                                $class = 'placeOk'; // occupied cell
                            }
                        ?>
                        <td class="<?= $class ?>" id="<?= $i ?>-<?= $j ?>"
                            style="font-size:9px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?php if ($placed): ?>
                                <?= htmlspecialchars($placed['nom_etudiant'], ENT_QUOTES, 'UTF-8') ?><br>
                                <?= htmlspecialchars($placed['prenom_etudiant'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($placed['tiers_temps']): ?><br><em>(TT)</em><?php endif; ?>
                                <?php if ($placed['mob_reduite']): ?><br><em>(MR)</em><?php endif; ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="bureau">BUREAU</div>
    </center>

    <?php if ($idDevoir > 0): ?>
    <p style="margin-top:1rem;">
        <a href="index.php?action=export_pdf&varD=1&idDevoir=<?= (int)$idDevoir ?>&idSalle=<?= (int)$salle['id_salle'] ?>" target="_blank">
            📄 Liste PDF
        </a>
        &nbsp;
        <a href="index.php?action=export_pdf&varD=2&idDevoir=<?= (int)$idDevoir ?>&idSalle=<?= (int)$salle['id_salle'] ?>" target="_blank">
            📋 Feuille d'émargement
        </a>
    </p>
    <?php endif; ?>

</div>
