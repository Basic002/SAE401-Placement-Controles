<link rel="stylesheet" href="public/css/s_up_stage3.css">

<div class="titrecontenu">Étape 3 — Export</div>

<div class="contenu">

    <h3>Devoir enregistré : <?php echo htmlspecialchars($nomDevoir); ?></h3>

    <table id="tabPDF">
        <tr>
            <th>Salle</th>
            <th>Liste</th>
            <th>Feuille d'émargement</th>
            <th>Plan</th>
        </tr>
        <?php foreach ($sallesInfo as $salle): ?>
            <tr>
                <td><?php echo htmlspecialchars($salle['nom_salle']); ?></td>
                <td class="btnPDF">
                    <a href="index.php?action=export_pdf&varD=1&idDevoir=<?php echo (int)$idDevoir; ?>&idSalle=<?php echo (int)$salle['id_salle']; ?>">
                        <img class="imgPDF" src="public/images/loupe.png" alt="Voir liste">
                    </a>
                </td>
                <td class="btnPDF">
                    <a href="index.php?action=export_pdf&varD=2&idDevoir=<?php echo (int)$idDevoir; ?>&idSalle=<?php echo (int)$salle['id_salle']; ?>">
                        <img class="imgPDF" src="public/images/loupe.png" alt="Voir émargement">
                    </a>
                </td>
                <td class="btnPDF">
                    <a href="index.php?action=export_pdf&varD=4&idDevoir=<?php echo (int)$idDevoir; ?>&idSalle=<?php echo (int)$salle['id_salle']; ?>">
                        <img class="imgPDF" src="public/images/loupe.png" alt="Voir plan">
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <div class="form-nav">
        <a href="index.php?action=util_placement">Nouveau placement &rarr;</a>
        <a href="index.php?action=home">Retour à l'accueil &rarr;</a>
    </div>

</div>
