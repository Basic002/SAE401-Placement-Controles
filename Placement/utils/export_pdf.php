<?php
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	// Les warnings/deprecations cassent le flux binaire PDF (headers déjà envoyés).
	ini_set('display_errors', '0');
	error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
	require_once __DIR__ . '/../config/connexion.php';
	require_once __DIR__ . '/../libs/ezpdf/class.ezpdf.php';
	require_once __DIR__ . '/fct_pdf.php';
	
	$idDevoir = (int) ($_GET['idDevoir'] ?? 0);
	$idSalle  = (int) ($_GET['idSalle'] ?? 0);
	$idPromo  = (int) ($_GET['idPromo'] ?? 0);

	function getInfosDevoirExport(int $idDevoir): array
	{
		global $pdo;
		$stmt = $pdo->prepare("SELECT date_devoir, heure_devoir, duree_devoir FROM devoir WHERE id_devoir = :id_devoir");
		$stmt->execute(['id_devoir' => $idDevoir]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

		$date = (string) ($row['date_devoir'] ?? '');
		$heure = (string) ($row['heure_devoir'] ?? '00:00:00');
		$duree = (string) ($row['duree_devoir'] ?? '00:00:00');

		return [
			'date'   => $date,
			'h'      => substr($heure, 0, 2) ?: '00',
			'm'      => substr($heure, 3, 2) ?: '00',
			'duree_h'=> substr($duree, 0, 2) ?: '00',
			'duree_m'=> substr($duree, 3, 2) ?: '00',
		];
	}

	function parsePlanRows(string $donnee): array
	{
		$rows = [];
		foreach (explode('-', $donnee) as $row) {
			if ($row !== '') {
				$rows[] = str_split($row);
			}
		}
		return $rows;
	}

	function streamPdfClean(Cezpdf $pdf, string $filename): void
	{
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		$pdf->ezStream([
			'Content-Disposition' => $filename,
		]);
	}

	function creaPDFPlanSalle(int $idDevoir, int $idSalle): void
	{
		global $pdo;

		$stmt = $pdo->prepare("
			SELECT s.nom_salle, p.donnee
			FROM salle s
			JOIN plan p ON p.id_plan = s.id_plan
			WHERE s.id_salle = :id_salle
		");
		$stmt->execute(['id_salle' => $idSalle]);
		$salleRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

		$nomSalle = (string) ($salleRow['nom_salle'] ?? "Salle {$idSalle}");
		$structure = parsePlanRows((string) ($salleRow['donnee'] ?? ''));
		if (empty($structure)) {
			$structure = [['0']];
		}

		$stmtPlac = $pdo->prepare("
			SELECT place_x, place_y, nom_etudiant, prenom_etudiant
			FROM placement pl
			JOIN etudiant e ON e.id_etudiant = pl.id_etudiant
			WHERE pl.id_devoir = :id_devoir
			  AND pl.id_salle = :id_salle
		");
		$stmtPlac->execute([
			'id_devoir' => $idDevoir,
			'id_salle'  => $idSalle,
		]);

		$lookup = [];
		while ($r = $stmtPlac->fetch(PDO::FETCH_ASSOC)) {
			$key = ((int) $r['place_x']) . ',' . ((int) $r['place_y']);
			$full = trim(((string) $r['nom_etudiant']) . ' ' . ((string) $r['prenom_etudiant']));
			$lookup[$key] = mb_convert_encoding($full, 'ISO-8859-1', 'UTF-8');
		}

		$nbCols = count($structure[0]);
		$cols = [];
		for ($c = 0; $c < $nbCols; $c++) {
			$cols[$c] = 'C' . ($c + 1);
		}

		$data = [];
		foreach ($structure as $x => $row) {
			$line = [];
			foreach ($row as $y => $cell) {
				$key = $x . ',' . $y;
				if (isset($lookup[$key])) {
					$line[$y] = $lookup[$key];
				} elseif ((int) $cell === 0) {
					$line[$y] = ' ';
				} elseif ((int) $cell === 3) {
					$line[$y] = '-';
				} else {
					$line[$y] = '';
				}
			}
			$data[] = $line;
		}

		$pdf = new Cezpdf('a4', 'landscape');
		$pdf->selectFont(__DIR__ . '/../libs/ezpdf/fonts/Helvetica.afm');

		$infoDevoir = getInfosDevoirExport($idDevoir);
		$conf = ['justification' => 'center'];
		$pdf->ezText(mb_convert_encoding("Plan de salle {$nomSalle}", 'ISO-8859-1', 'UTF-8'), 14, $conf);
		$pdf->ezText(
			$infoDevoir['date'].' - '.$infoDevoir['h'].'h'.$infoDevoir['m']
			. mb_convert_encoding(' - Durée: ', 'ISO-8859-1', 'UTF-8')
			. $infoDevoir['duree_h'].'h'.$infoDevoir['duree_m'],
			10,
			$conf
		);

		$options = [
			'showLines' => 1,
			'showHeadings' => 0,
			'shaded' => 1,
			'shadeCol' => [0.95, 0.95, 0.95],
			'shadeCol2' => [0.88, 0.88, 0.88],
			'textCol' => [0, 0, 0],
			'rowGap' => 2,
			'colGap' => 3,
			'lineCol' => [1, 1, 1],
			'xPos' => 'center',
			'fontSize' => 8,
			'width' => 800
		];
		$pdf->ezTable($data, $cols, ' ', $options);
		streamPdfClean($pdf, "plan_salle_{$idSalle}_devoir_{$idDevoir}.pdf");
	}


// ########################################################################################
// 						EXPORT LISTE PDF								  #
// ########################################################################################

	// ######################### LISTE PAR SALLE #########################
	
	function creaPDFSalle($idDevoir, $idSalle)
	{
		global $pdo;
		require_once __DIR__ . '/../config/connexion.php';

		$stmtSalle = $pdo->prepare("SELECT nom_salle FROM salle WHERE id_salle = :id_salle");
		$stmtSalle->execute(['id_salle' => $idSalle]);
		$nomSalle = (string) ($stmtSalle->fetchColumn() ?: "Salle {$idSalle}");
		recupStructSalle($idSalle);
		numeroPlace();
		
		$pdf= new Cezpdf('a4','portrait');
		$pdf->selectFont(__DIR__ . '/../libs/ezpdf/fonts/Helvetica.afm');
		
		$cols[0]=mb_convert_encoding('Nom', 'ISO-8859-1', 'UTF-8');
		$cols[1]=mb_convert_encoding('Prénom', 'ISO-8859-1', 'UTF-8');
		$cols[2]='Place';
		$cols[3]='Promotion';
		$cols[4]='Groupe';
		//$cols[5]='Salle';
		//$cols[6]='Signature';
		
	 	recupListeSalle($idDevoir, $idSalle);
	 	
	 	for($i=0; $i<$_SESSION['cpt']; $i++)
	 	{
		    $data[$i][0]=$_SESSION['data'][$i][0];
		    $data[$i][1]=$_SESSION['data'][$i][1];
		    $data[$i][2]=$_SESSION['data'][$i][5];
		    $data[$i][3]=$_SESSION['data'][$i][2];
		    $data[$i][4]=$_SESSION['data'][$i][3];
		}
		
		// ######################### OPTIONS ####################
		$options=array(
			// Ligne
			'showLines' => 1,
			
			// Afficher titre (Non)
			'show Headings' => 1,
			
			// Couleurs ligne
			'shaded' => 1, // gris une ligne sur 2 seulement
			'shadeCol' => array(0.95,0.95,0.95),
			'shadeCol2' => array(0.8,0.8,0.8),
			
			// Couleurs texte
			'textCol' => array(0,0,0),
	
			// Equivalent padding
			'rowGap' => 1, // hauteur des lignes
			'colGap' => 10,
			
			// Couleur ligne (transparente//blanche)
			'lineCol' => array(1,1,1),
			
			// Positions
			'xPos' => 'center',
			//'xOrientation' => 'center',
			
			// Taille
			'width' => 90,
			//'maxWidth' => 400
			);
		
		
		
		// ##################### TITRE - LISTE PAR SALLE ########################################
		
		// ###################### RECUP MATIERE #################
		
		$nbMat=0;
		$mat=array(array());
		
		$nbCombi = (int) ($_SESSION['nbCombi'] ?? 0);
		for($i=0; $i<$nbCombi; $i++)
		{
			if(($_SESSION['infoCombi'][$i][2] ?? null) == $idSalle)
			{
				$query1=$pdo->prepare('SELECT nom_mat, nom_promo 
					FROM matiere, promotion 
					WHERE matiere.id_promo = promotion.id_promo 
					AND id_mat=:id_mat');
				$query1->execute(['id_mat'=>(int) ($_SESSION['infoCombi'][$i][3] ?? 0)]);
				$res=$query1->fetch(PDO::FETCH_ASSOC);
				if ($res) {
					$mat[$nbMat][0]=mb_convert_encoding((string) $res['nom_mat'], 'ISO-8859-1', 'UTF-8');
					$mat[$nbMat][1]=mb_convert_encoding((string) $res['nom_promo'], 'ISO-8859-1', 'UTF-8');
					$nbMat++;
				}
			}
		}
		
		// ########################## TITRE #####################
		$titleSalle='Liste '.$nomSalle;
		$titleMat='';
		
		for($i=0; $i<$nbMat; $i++)
		{
			if($i==0)
				$delim='';
			else
				$delim=' - ';
				
			$titleMat=$titleMat.$delim.$mat[$i][0]." (".$mat[$i][1].')';
		}
		
		$conf=array(
				'justification' => 'center',
				);
		
		// Nom salle
		$pdf->ezText(mb_convert_encoding($titleSalle, 'ISO-8859-1', 'UTF-8'), 14, $conf);
		
		// Matieres
		$pdf->ezText($titleMat, 10, $conf);
		
		// Date format europeen
		$infoDevoir = getInfosDevoirExport($idDevoir);
		$pdf->ezText(
			$infoDevoir['date'].' - '.$infoDevoir['h'].'h'.$infoDevoir['m']
			. mb_convert_encoding(' - Durée: ', 'ISO-8859-1', 'UTF-8')
			. $infoDevoir['duree_h'].'h'.$infoDevoir['duree_m'],
			10,
			$conf
		);
		
	//echo "PLD ";print_r($data);echo " /PLD";
	//echo "PLC ";print_r($cols);echo " /PLC";
	//echo "PLO ";print_r($options);echo " /PLO";	
		// ########################## TABLEAU ####################
		$pdf->ezTable($data,$cols,' ',$options);

		// ########################## EXPORT #####################
		streamPdfClean($pdf, "liste_salle_{$idSalle}_devoir_{$idDevoir}.pdf");
	}
	
	// ######################### LISTE D'EMARGEMENT PAR SALLE #########################
	function creaPDFEmarge($idDevoir, $idSalle)
	{
		global $pdo;
		require_once __DIR__ . '/../config/connexion.php';
		$stmtSalle = $pdo->prepare("SELECT nom_salle FROM salle WHERE id_salle = :id_salle");
		$stmtSalle->execute(['id_salle' => $idSalle]);
		$nomSalle = (string) ($stmtSalle->fetchColumn() ?: "Salle {$idSalle}");
		
		recupStructSalle($idSalle);
		numeroPlace();
		
		$pdf= new Cezpdf('a4','portrait');
		$pdf->selectFont(__DIR__ . '/../libs/ezpdf/fonts/Helvetica.afm');

		$cols[0]='       Signature       ';		
		$cols[1]=mb_convert_encoding('Nom', 'ISO-8859-1', 'UTF-8');
		$cols[2]=mb_convert_encoding('Prénom', 'ISO-8859-1', 'UTF-8');
		$cols[3]='Place';
		$cols[4]='Promotion';
		$cols[5]='Groupe';
		// $cols[4]='Salle';
		
		// ---- original de W -----
		// $cols[0]=mb_convert_encoding('Nom');
		// $cols[1]=mb_convert_encoding('Prénom');
		// $cols[2]='Promotion';
		// $cols[3]='Groupe';
		// $cols[4]='Salle';
		// $cols[5]='Place';
		// $cols[6]='Signature';
		
	 	recupListeSalle($idDevoir, $idSalle);
	 	
	 	for($i=0; $i<$_SESSION['cpt']; $i++)
	 	{
		    $data[$i][0]="";
		    $data[$i][1]=$_SESSION['data'][$i][0];
		    $data[$i][2]=$_SESSION['data'][$i][1];
		    $data[$i][3]=$_SESSION['data'][$i][5];
		    $data[$i][4]=$_SESSION['data'][$i][2];
		    $data[$i][5]=$_SESSION['data'][$i][3];
	 	}
		
		// ############## TITRE - FEUILLE D'EMARGEMENT PAR SALLE ###########################
		
		// ###################### RECUP MATIERE #################
		
		$nbMat=0;
		$mat=array(array());
		
		$nbCombi = (int) ($_SESSION['nbCombi'] ?? 0);
		for($i=0; $i<$nbCombi; $i++)
		{
			if(($_SESSION['infoCombi'][$i][2] ?? null) == $idSalle)
			{
				$query1=$pdo->prepare('SELECT nom_mat, nom_promo 
					FROM matiere, promotion 
					WHERE matiere.id_promo = promotion.id_promo 
					AND id_mat=:id_mat');
				$query1->execute(['id_mat'=>(int) ($_SESSION['infoCombi'][$i][3] ?? 0)]);
				$res=$query1->fetch(PDO::FETCH_ASSOC);
				if ($res) {
					$mat[$nbMat][0]=mb_convert_encoding((string) $res['nom_mat'], 'ISO-8859-1', 'UTF-8');
					$mat[$nbMat][1]=mb_convert_encoding((string) $res['nom_promo'], 'ISO-8859-1', 'UTF-8');
					$nbMat++;
				}
			}
		}
		
		// ########################## TITRE #####################
		$title='FEUILLE D\'EMARGEMENT '.$nomSalle;

		$titleMat='';
		
		for($i=0; $i<$nbMat; $i++)
		{
			if($i==0)
				$delim='';
			else
				$delim=' - ';
				
			$titleMat=$titleMat.$delim.$mat[$i][0]." (".$mat[$i][1].')';
		}
		
		$conf=array(
				'justification' => 'center',
				);
				
		$confLeft=array(
				'justification' => 'left',
				);

		$pdf->ezText('Surveillant :',10,  $confLeft);
		$pdf->ezText('Nombre d\'absents :',10,  $confLeft);
		$pdf->ezText('Absents :',10,  $confLeft);
		$pdf->ezText(' ',20,  $confLeft);

		// Nom salle
		$pdf->ezText(mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 14, $conf);
		
		// Matieres
		$pdf->ezText($titleMat, 10, $conf);
		
		// Date format europeen
		$infoDevoir = getInfosDevoirExport($idDevoir);
		$pdf->ezText(
			$infoDevoir['date'].' - '.$infoDevoir['h'].'h'.$infoDevoir['m']
			. mb_convert_encoding(' - Durée: ', 'ISO-8859-1', 'UTF-8')
			. $infoDevoir['duree_h'].'h'.$infoDevoir['duree_m'],
			10,
			$conf
		);

		
		$options=array(
			// Ligne
			'showLines' => 1,
			
			// Afficher titre (Non)
			'show Headings' => 1,
			
			// Couleurs ligne
			'shaded' => 1,
			'shadeCol' => array(0.95,0.95,0.95),
			'shadeCol2' => array(0.9,0.9,0.9),
			
			// Couleurs texte
			'textCol' => array(0,0,0),
	
			// Equivalent padding
			'rowGap' => 3,
			'colGap' => 10,
			
			// Couleur ligne (transparente//blanche)
			'lineCol' => array(1,1,1),
			
			// Positions
			'xPos' => 'center',
			'xOrientation' => 'center',
			
			// Taille
			'width' => 50,
			'maxWidth' => 300
			);
		$pdf->ezTable($data,$cols,' ',$options);
		streamPdfClean($pdf, "emargement_salle_{$idSalle}_devoir_{$idDevoir}.pdf");
	
	}
	
	// ######################### LISTE PAR PROMOTION #########################
	function creaPDFPromo($idDevoir, $idPromo)
	{
		global $pdo;
		require_once __DIR__ . '/../config/connexion.php';
		$stmtPromo = $pdo->prepare("SELECT p.nom_promo, d.nom_dpt
					FROM promotion p
					JOIN departement d ON p.id_dpt = d.id_dpt
					WHERE p.id_promo = :id_promo");
		$stmtPromo->execute(['id_promo' => $idPromo]);
		$promoRow = $stmtPromo->fetch(PDO::FETCH_ASSOC) ?: [];
		$nomPromo = (string) ($promoRow['nom_promo'] ?? "Promo {$idPromo}");
		$nomDpt = (string) ($promoRow['nom_dpt'] ?? '');
		
		$pdf= new Cezpdf('a4','portrait');
		$pdf->selectFont(__DIR__ . '/../libs/ezpdf/fonts/Helvetica.afm');
		
		$cols[0]=mb_convert_encoding('Nom', 'ISO-8859-1', 'UTF-8');
		$cols[1]=mb_convert_encoding('Prénom', 'ISO-8859-1', 'UTF-8');
		$cols[2]='Place';
		$cols[3]='Salle';
		$cols[4]='Groupe';

		// -- l'affichage original de W
		// $cols[2]='Promotion';
		// $cols[3]='Groupe';
		// $cols[4]='Salle';
		// $cols[5]='Place';
		//$cols[6]='Signature';
		
	 	recupListePromo($idDevoir, $idPromo);
	 	
	 	for($i=0; $i<$_SESSION['cpt']; $i++)
	 	{
		    $data[$i][0]=$_SESSION['data'][$i][0];
		    $data[$i][1]=$_SESSION['data'][$i][1];
		    $data[$i][2]=$_SESSION['data'][$i][5];
		    $data[$i][3]=$_SESSION['data'][$i][4];
		    $data[$i][4]=$_SESSION['data'][$i][3];
		    // $data[$i][5]=$_SESSION[data][$i][5];
	 	}	
				
		// ###################### RECUP MATIERE #################
		
		/**
		* Ca plantait la page, je commente et ça passe, je ne vais pas essayer de comprendre
		*/
		// $query2=mysql_query("SELECT COUNT(DISTINCT promotion.id_promo) nbPromo
		// 		FROM matiere, promotion
		// 		WHERE matiere.id_promo = promotion.id_promo
		// 		AND promotion.id_promo = $idPromo
		// 		AND id_mat='".$_SESSION['infoCombi'][$i][3]."'");

		$nbMat=0;
		$mat=array();
		
		$nbCombi = (int) ($_SESSION['nbCombi'] ?? 0);
		for($i=0; $i<$nbCombi; $i++)
		{
			//if($_SESSION['infoCombi'][$i][2]==$idSalle) -- pas besoin ici apparemment
			//{
				$query1=$pdo->prepare("SELECT nom_mat 
				FROM matiere, promotion
				WHERE matiere.id_promo = promotion.id_promo
				AND promotion.id_promo = $idPromo
				AND id_mat=:id_mat");
				$query1->execute(['id_mat'=>(int) ($_SESSION['infoCombi'][$i][3] ?? 0)]);
				$res=$query1->fetch(PDO::FETCH_ASSOC);	
				if ($res && !empty($res['nom_mat'])) {
					$mat[$nbMat]=mb_convert_encoding((string) $res['nom_mat'], 'ISO-8859-1', 'UTF-8');
					$nbMat++;
				}
			//}
		}
		
		// ########################## TITRE #####################
		$titlePromo='Liste '.$nomDpt.' '.$nomPromo;

		// On affiche juste la matière de la promo; si par ex. 2 groupes différentes avec 2 matières différentes, on affiche les 2

		$titleMat='';
	
		for($i=0; $i<$nbMat; $i++)
		{
			if($i==0)
				$delim='';
			else
				$delim=' - ';
				
			$titleMat=$titleMat.$delim.$mat[$i];
		}

		$conf=array(
				'justification' => 'center',
				);

		// Nom promo
		$pdf->ezText(mb_convert_encoding($titlePromo, 'ISO-8859-1', 'UTF-8'), 14, $conf);

		// Nom matière
		$pdf->ezText($titleMat, 10, $conf);
		
		// Date format europeen
		$infoDevoir = getInfosDevoirExport($idDevoir);
		$pdf->ezText(
			$infoDevoir['date'].' - '.$infoDevoir['h'].'h'.$infoDevoir['m']
			. mb_convert_encoding(' - Durée: ', 'ISO-8859-1', 'UTF-8')
			. $infoDevoir['duree_h'].'h'.$infoDevoir['duree_m'],
			10,
			$conf
		);

		$options=array(
			// Ligne
			'showLines' => 1,
			
			// Afficher titre (Non)
			'show Headings' => 1,
			
			// Couleurs ligne
			'shaded' => 1,
			'shadeCol' => array(0.95,0.95,0.95),
			'shadeCol2' => array(0.9,0.9,0.9),
			
			// Couleurs texte
			'textCol' => array(0,0,0),
	
			// Equivalent padding
			'rowGap' => 1,
			'colGap' => 10,
			
			// Couleur ligne (transparente//blanche)
			'lineCol' => array(1,1,1),
			
			// Positions
			'xPos' => 'center',
			'xOrientation' => 'center',
			
			// Taille
			'width' => 50,
			'maxWidth' => 300
			);
			
		$pdf->ezTable($data,$cols,' ',$options);
		streamPdfClean($pdf, "liste_promo_{$idPromo}_devoir_{$idDevoir}.pdf");
	}

// ########################################################################################
// 		PGME PRINCIPAL									  #
// ########################################################################################
//header('Content-Disposition: attachment;; filename="file.pdf"');
	
	// CHOIX LISTE PDF
	$varD = (string) ($_GET['varD'] ?? '');
	switch($varD)
	{
		case '1' : creaPDFSalle($idDevoir, $idSalle); break;
		case '2' : creaPDFEmarge($idDevoir, $idSalle); break;
		case '3' : creaPDFPromo($idDevoir, $idPromo); break;
		case '4' : creaPDFPlanSalle($idDevoir, $idSalle); break;
		default: break;
	}


	
