<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_crea_salle.css">

<!-- #################### TITRE PRINCIPAL (MASQUÉ) ################### -->
<div class="titrecontenu" style="display:none;">Modification salle</div>

<!-- ##################### CONTENU PAGE ##################### -->
<div class="contenu">

	<!-- iFrame pour faire defiler les etapes (REMPLACÉ PAR DIV) -->
	<div id="stage-content">
		<?php 
		if (!empty($stageFile)) {
			include __DIR__ . '/' . basename($stageFile);
		}
		?>
	</div>
	<input type="hidden" id="currentStageName" value="stage<?php echo htmlspecialchars((string)($etape ?? 1), ENT_QUOTES, 'UTF-8'); ?>">

	<!-- Bouton precedent/suivant -->
	<div class="navigation-buttons">
		<button type="button" id="btnbef" style="display:none;">← Précédent</button>
		<button type="button" id="btnnext">Suivant →</button>
		<button type="button" id="btnsave" style="display:none;">✓ Enregistrer</button>
	</div>
	
	<div class="footer-actions">
		<a href="index.php?action=gest_salle" class="btn-quitter">Quitter</a>
	</div>
	
</div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/modif_salle.js"></script>
