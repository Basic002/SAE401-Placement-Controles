<link rel="stylesheet" href="public/css/s_up_stage2.css">

<div class="titrecontenu">Étape 2 — Grille de placement</div>

<div class="contenu">

    <?php foreach ($grilles as $idSalle => $data): ?>
        <?php
            // Build lookup: "place_x,place_y" => id_etudiant
            $lookup = [];
            foreach ($data['placement'] as $p) {
                $key = $p['place_x'] . ',' . $p['place_y'];
                $lookup[$key] = $p['id_etudiant'];
            }
            $grille = $data['grille'];
            $classeMap = [0 => 'couloir', 1 => 'placeOk', 2 => 'placeHandi', 3 => 'placeInex'];
        ?>

        <h3>Salle : <?php echo htmlspecialchars($data['salle']['nom_salle']); ?></h3>

        <div class="placement-grid-wrap">
        <table class="placement-grid" id="TAB_salle<?php echo (int)$idSalle; ?>">
            <?php foreach ($grille as $i => $row): ?>
                <tr>
                    <?php foreach ($row as $j => $val): ?>
                        <?php
                            $classe = $classeMap[$val] ?? 'couloir';
                            $key = $i . ',' . $j;
                        ?>
                        <?php if (isset($lookup[$key])): ?>
                            <?php
                                $idEtu = $lookup[$key];
                                $nom = htmlspecialchars(
                                    $nomsEtudiants[$idEtu]['nom_etudiant']
                                    . ' '
                                    . $nomsEtudiants[$idEtu]['prenom_etudiant']
                                );
                            ?>
                            <td class="<?php echo $classe; ?>"
                                data-etu-id="<?php echo (int)$idEtu; ?>"
                                onclick="selecTwo(this)"><?php echo $nom; ?></td>
                        <?php elseif ($val !== 0): ?>
                            <td class="<?php echo $classe; ?>" onclick="selecTwo(this)"></td>
                        <?php else: ?>
                            <td class="couloir"></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
        </div>

        <div class="bureau">BUREAU</div>

    <?php endforeach; ?>

    <center>
        <button id="btnInter" style="display:none" onclick="intervertir()" type="button">Intervertir</button>
    </center>

    <p>Cliquez sur deux places pour les intervertir.</p>

    <div class="form-nav">
        <a href="index.php?action=placement_stage1">&larr; Retour</a>
        <form id="formSave" method="POST" action="index.php?action=placement_stage3" style="display:inline">
            <button type="submit">Enregistrer &rarr;</button>
        </form>
    </div>

</div>

<script src="public/javascript/up_stage2.js"></script>
