<?php	
	// ########################################## Recuperation no place salle ###########################################
	function numeroPlace()
	{	
		$col=0;
		$rang=0;
		$_SESSION['noPlace']=array(array());
		
		for($i=$_SESSION['rangSalle']-1; $i>=0; $i--)
		{
			if($_SESSION['structSalle'][$i][0]!=0)
			{
				$rang++;
				$col=0;
				for($j=0; $j<$_SESSION['colSalle']; $j++)
				{
					if($_SESSION['structSalle'][$i][$j]!=0)
					{
						$col++;
						if($_SESSION['structSalle'][$i][$j]!=3)
						{
							$_SESSION['noPlace'][$i][$j]=$rang.'-'.$col;
						}
					}
				}
			}
		}
	//	echo ("PLNP ");
	//	print_r($_SESSION['noPlace']);
	//	echo(" /PLNP");
	}
	
	// ########################################## Recuperation structure salle ###########################################
	function recupStructSalle($idSalle)
	{
		global $pdo;
		require_once __DIR__ . '/../config/connexion.php';
		// Requete
		$stmt = $pdo->prepare('SELECT * FROM plan, salle WHERE plan.id_plan = salle.id_plan AND id_salle = :idSalle');
		$stmt->execute(['idSalle' => $idSalle]);
		$salle = $stmt->fetch(PDO::FETCH_ASSOC);
		
		// Recupere donnees plan
		$text = $salle['donnee'];
		
		// Separe les rang dans un tableau
		// $array=split('-',$text);
		$array=explode('-',$text);
		// RAZ VARIABLES
		unset($_SESSION['colSalle']);
		unset($_SESSION['rangSalle']);
		unset($_SESSION['structSalle']);
		
		// Recupere le nombre de colonne et rang
		$_SESSION['colSalle']=strlen($array[0]);
		$_SESSION['rangSalle']=count($array)-1;
		
		// Recupere les valeurs dans le tableau de structure
		for($i=0; $i<$_SESSION['rangSalle']; $i++)
		{
			for($j=0; $j<$_SESSION['colSalle']; $j++)
			{
				$_SESSION['structSalle'][$i][$j]=$array[$i][$j];
			}
		}
	//	echo "PLSS ";
	//	print_r($_SESSION['structSalle']);
	//	echo " /PLSS";
	}
	
	
	// ############################## Recupere tous les etudiants d'une salle pour un devoir ##############################
	function recupListeSalle($idDevoir, $idSalle)
	{
		global $pdo;
		require_once __DIR__ . '/../config/connexion.php';
		$stmt = $pdo->prepare("
			SELECT DISTINCT
				e.nom_etudiant,
				e.prenom_etudiant,
				g.nom_groupe,
				p.nom_promo,
				d.nom_dpt,
				s.nom_salle,
				pl.place_x,
				pl.place_y
			FROM placement pl
			JOIN etudiant e ON e.id_etudiant = pl.id_etudiant
			JOIN groupe g ON g.id_groupe = e.id_groupe
			JOIN promotion p ON p.id_promo = g.id_promo
			JOIN departement d ON d.id_dpt = p.id_dpt
			JOIN salle s ON s.id_salle = pl.id_salle
			WHERE pl.id_devoir = :idDevoir
			  AND pl.id_salle = :idSalle
			  AND (
					EXISTS (
						SELECT 1
						FROM devoir_groupe dg
						WHERE dg.id_devoir = pl.id_devoir
						  AND dg.id_groupe = e.id_groupe
					)
					OR EXISTS (
						SELECT 1
						FROM devoir_promo dp
						WHERE dp.id_devoir = pl.id_devoir
						  AND dp.id_promo = g.id_promo
					)
			  )
			ORDER BY e.nom_etudiant
		");
		$stmt->execute([
			'idDevoir' => $idDevoir,
			'idSalle'  => $idSalle,
		]);
		
		// RAZ Variables
		$_SESSION['data'] = array(array());
		$_SESSION['cpt'] = 0;
		
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$_SESSION['data'][$_SESSION['cpt']][0] = utf8_decode($result['nom_etudiant']);
			$_SESSION['data'][$_SESSION['cpt']][1] = utf8_decode($result['prenom_etudiant']);
			$_SESSION['data'][$_SESSION['cpt']][2] = $result['nom_dpt'] . ' ' . $result['nom_promo'];
			$_SESSION['data'][$_SESSION['cpt']][3] = $result['nom_groupe'];
			$_SESSION['data'][$_SESSION['cpt']][4] = $result['nom_salle'];
			$_SESSION['data'][$_SESSION['cpt']][5] = $_SESSION['noPlace'][$result['place_x']][$result['place_y']];
			$_SESSION['cpt']++;
		}
	/*
		echo "PLD ";
		print_r($_SESSION['data']);
		echo " /PLD";
		echo "PLC ";
		print_r($_SESSION['cpt']);
		echo  " /PLC";		
	*/
	}
	
	// ############################## Recupere tout les etudiants d'une promo pour un devoir ##############################
	function recupListePromo($idDevoir, $idPromo)
	{
		global $pdo;
		require_once __DIR__ . '/../config/connexion.php';
		$stmt = $pdo->prepare("
			SELECT DISTINCT
				s.id_salle AS s,
				e.nom_etudiant,
				e.prenom_etudiant,
				g.nom_groupe,
				p.nom_promo,
				d.nom_dpt,
				s.nom_salle,
				pl.place_x,
				pl.place_y
			FROM placement pl
			JOIN etudiant e ON e.id_etudiant = pl.id_etudiant
			JOIN groupe g ON g.id_groupe = e.id_groupe
			JOIN promotion p ON p.id_promo = g.id_promo
			JOIN departement d ON d.id_dpt = p.id_dpt
			JOIN salle s ON s.id_salle = pl.id_salle
			WHERE pl.id_devoir = :idDevoir
			  AND g.id_promo = :idPromo
			  AND (
					EXISTS (
						SELECT 1
						FROM devoir_promo dp
						WHERE dp.id_devoir = pl.id_devoir
						  AND dp.id_promo = g.id_promo
					)
					OR EXISTS (
						SELECT 1
						FROM devoir_groupe dg
						WHERE dg.id_devoir = pl.id_devoir
						  AND dg.id_groupe = e.id_groupe
					)
			  )
			ORDER BY s.nom_salle, p.nom_promo, e.nom_etudiant
		");
		$stmt->execute([
			'idDevoir' => $idDevoir,
			'idPromo'  => $idPromo,
		]);
		
		// RAZ Variables
		$_SESSION['data'] = array(array());
		$_SESSION['cpt'] = 0;
		
		// Recuperation premier tour
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$result) {
			return;
		}
		$salle = $result['s'];
		// Recup struct Salle + place
		recupStructSalle($result['s']);
		numeroPlace();
		$_SESSION['data'][$_SESSION['cpt']][0] = utf8_decode($result['nom_etudiant']);
		$_SESSION['data'][$_SESSION['cpt']][1] = utf8_decode($result['prenom_etudiant']);
		$_SESSION['data'][$_SESSION['cpt']][2] = $result['nom_dpt'] . ' ' . $result['nom_promo'];
		$_SESSION['data'][$_SESSION['cpt']][3] = $result['nom_groupe'];
		$_SESSION['data'][$_SESSION['cpt']][4] = $result['nom_salle'];
		$_SESSION['data'][$_SESSION['cpt']][5] = $_SESSION['noPlace'][$result['place_x']][$result['place_y']];
		$_SESSION['cpt']++;
		
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($salle != $result['s']) {
				// Recup struct Salle + place
				recupStructSalle($result['s']);
				numeroPlace();
				$salle = $result['s'];
			}
		
			$_SESSION['data'][$_SESSION['cpt']][0] = utf8_decode($result['nom_etudiant']);
			$_SESSION['data'][$_SESSION['cpt']][1] = utf8_decode($result['prenom_etudiant']);
			$_SESSION['data'][$_SESSION['cpt']][2] = $result['nom_dpt'] . ' ' . $result['nom_promo'];
			$_SESSION['data'][$_SESSION['cpt']][3] = $result['nom_groupe'];
			$_SESSION['data'][$_SESSION['cpt']][4] = $result['nom_salle'];
			$_SESSION['data'][$_SESSION['cpt']][5] = $_SESSION['noPlace'][$result['place_x']][$result['place_y']];
			$_SESSION['cpt']++;
		}
		/*
		echo "PLD RLP ";
		print_r($_SESSION['data']);
		echo " /PLD RLP";
		echo "PLC RLP";
		print_r($_SESSION['cpt']);
		echo  " /PLC RLP";		
		*/
	}
	

?>
