<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="public/css/s_index.css">
		<link rel="stylesheet" href="public/css/s_menu.css">
		<link rel="stylesheet" href="public/css/s_generique.css">
		<title><?= isset($titre_page) ? $titre_page : "Accueil" ?></title>
	</head>
	<body>
		<div class="topbar">
			<a href="index.php?action=home"><div id="btnhome" class="btnbar_l" style="width:45px"><img src="public/images/home.png" style="height:65%; margin-top:5px;"></div></a>
			
			<?php if(isset($_SESSION['droit']) && $_SESSION['droit']) : ?>
                <div id="btngest" class="btnbar_l">Gestion</div>
                <div id="blocgest" class="deroule_gest">
                    <a id="linkdec" href="index.php?action=gest_mat"><div id="btndgest">Matière</div></a>
                    <a id="linkdec" href="index.php?action=gest_ens"><div id="btndgest">Enseignant</div></a>
                    <a id="linkdec" href="index.php?action=gest_ensmat"><div id="btndgest">Enseignement</div></a>
                    <a id="linkdec" href="index.php?action=gest_salle"><div id="btndgest">Salle</div></a>
                    <a id="linkdec" href="index.php?action=gest_dpt"><div id="btndgest">Département</div></a>
                    <a id="linkdec" href="index.php?action=gest_bat"><div id="btndgest">Bâtiment</div></a>
                    <a id="linkdec" href="index.php?action=gest_promo"><div style="border-bottom-left-radius: 6px; border-bottom-right-radius: 6px;" id="btndgest">Promotion</div></a>
                </div>
			<?php endif; ?>
					
			<a id="linkdec" href="index.php?action=util_placement"><div id="btngest" class="btnbar_l">Placement</div></a>
			<a id="linkdec" href="index.php?action=logout"><div id="btncpt" class="btnbar_r">Déconnexion</div></a>
		</div>
		
		<div id="content">
            <?= isset($contenu_page) ? $contenu_page : "" ?>
		</div>

		<script src="public/javascript/jquery-1.7.1.js"></script>
		<script src="public/javascript/index.js"></script>
	</body>
</html>