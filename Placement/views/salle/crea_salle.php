<?php
	// Reinitialisation informations
	$_SESSION['nomSalle']="";
	$_SESSION['rangSalle']="";
	$_SESSION['colSalle']="";
?>

<!-- ##################### IMPORT STYLE ##################### -->
<link rel="stylesheet" type="text/css" href="public/css/s_crea_salle.css">

<!-- #################### TITRE PRINCIPAL ################### -->
<div class="titrecontenu">Création salle</div>

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
	<button type="button" id="btnbef" style="display:none; float:left; margin-left:20px;">Précédent</button>
	<button type="button" id="btnnext" style="float: right; margin-right: 20px;">Suivant</button>
	<button type="button" id="btnsave" style="display:none; float: right; margin-right: 20px;">Enregistrer</button>
	
	<br>
	<br>
	<br>
	<center>
		<a href="index.php?p=gest_salle"><button type="button">Quitter</button></a>
	</center>
	
</div>

<!-- ################## IMPORT JAVASCRIPT ################### -->
<script src="public/javascript/crea_salle.js"></script>
