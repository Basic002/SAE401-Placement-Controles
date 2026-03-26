<?php
require_once __DIR__ . '/../models/EtudiantModel.php';
require_once __DIR__ . '/../models/SalleModel.php';

class PlacementController {
    
    //Parse la chaîne de caractères de la BDD pour recréer la grille 2D de la salle
    private static function parsePlanSalle($donneePlan) {
        $lignes = explode('-', $donneePlan);
        $structure = [];
        foreach ($lignes as $ligne) {
            if ($ligne !== '') {
                $structure[] = str_split($ligne);
            }
        }
        return $structure;
    }

    //Trouve les places libres en fonction de l'espacement
    private static function getPlacesDisponibles($structure, $step) {
        $places_standard = [];
        $places_pmr = [];
        
        $nbRangees = count($structure);
        if ($nbRangees == 0) return ['standard' => [], 'pmr' => []];
        
        $nbColonnes = count($structure[0]);
        
        //Calcul des colonnes utilisables sur la première rangée
        $colonnes_utilisables = [];
        for ($c = 0; $c < $nbColonnes; $c += $step) {
            if (isset($structure[$nbRangees - 1][$c]) && $structure[$nbRangees - 1][$c] != 0) {
                $colonnes_utilisables[] = $c;
            } else {
                //Si la case prévue est vide, on cherche la prochaine valide
                $c_temp = $c + 1;
                while ($c_temp < $nbColonnes && $structure[$nbRangees - 1][$c_temp] == 0) {
                    $c_temp++;
                }
                if ($c_temp < $nbColonnes) {
                    $colonnes_utilisables[] = $c_temp;
                    $c = $c_temp;
                }
            }
        }

        //Parcours de la salle
        for ($r = $nbRangees - 1; $r >= 0; $r--) {
            foreach ($colonnes_utilisables as $c) {
                if ($structure[$r][$c] == 1) {
                    $places_standard[] = [$r, $c];
                } elseif ($structure[$r][$c] == 2) {
                    $places_pmr[] = [$r, $c];
                }
            }
        }
        
        return ['standard' => $places_standard, 'pmr' => $places_pmr];
    }

    //Calcule le placement final sans utiliser $_SESSION
    public static function calculerPlacement($pdo, $idSalle, $ids_etudiants, $step = 2) {
        if (empty($ids_etudiants)) return [];

        //Décoder la salle
        $salleData = SalleModel::getPlanBySalleId($pdo, $idSalle);
        $structure = self::parsePlanSalle($salleData['donnee']);
        
        //Récupérer les places
        $places = self::getPlacesDisponibles($structure, $step);
        $places_standard = $places['standard'];
        $places_pmr = $places['pmr'];

        //Récupérer les contraintes des étudiants
        $etudiants_infos = EtudiantModel::getEtudiantsCaracteristiques($pdo, $ids_etudiants);
        
        $etu_pmr = [];
        $etu_premier_rang = [];
        $etu_standard = [];

        foreach ($etudiants_infos as $etu) {
            if ($etu['mob_reduite'] == 1) {
                $etu_pmr[] = $etu['id_etudiant'];
            } elseif ($etu['premier_rang'] == 1) {
                $etu_premier_rang[] = $etu['id_etudiant'];
            } else {
                $etu_standard[] = $etu['id_etudiant'];
            }
        }

        shuffle($etu_pmr);
        shuffle($etu_premier_rang);
        shuffle($etu_standard);

        $placement = [];

        //Affectation PMR
        foreach ($etu_pmr as $id_etu) {
            if (!empty($places_pmr)) {
                $place = array_shift($places_pmr);
                $placement[] = [$place[0], $place[1], $id_etu];
            } else {
                $etu_premier_rang[] = $id_etu;
            }
        }

        //Affectation Premier Rang
        usort($places_standard, function($a, $b) { return $a[0] <=> $b[0]; });
        foreach ($etu_premier_rang as $id_etu) {
            if (!empty($places_standard)) {
                $place = array_shift($places_standard);
                $placement[] = [$place[0], $place[1], $id_etu];
            }
        }

        //Affectation Standard
        shuffle($places_standard);
        foreach ($etu_standard as $id_etu) {
            if (!empty($places_standard)) {
                $place = array_shift($places_standard);
                $placement[] = [$place[0], $place[1], $id_etu];
            }
        }

        return $placement;
    }
}
?>
