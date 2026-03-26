<?php
class EtudiantModel {
    
    //Récupère les caractéristiques d'une liste d'étudiants (pour le placement)
    public static function getEtudiantsCaracteristiques($pdo, $ids_etudiants) {
        if (empty($ids_etudiants)) return [];
        $placeholders = implode(',', array_fill(0, count($ids_etudiants), '?'));
        $stmt = $pdo->prepare("SELECT id_etudiant, mob_reduite, premier_rang, tiers_temps FROM etudiant WHERE id_etudiant IN ($placeholders)");
        $stmt->execute($ids_etudiants);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Compte le nombre d'étudiants dans une promo ou un groupe
    public static function countByPromoOrGroup($pdo, $idPromo, $idGroup) {
        if ($idGroup == 0) {
            $stmt = $pdo->prepare('SELECT COUNT(*) as a FROM etudiant e JOIN groupe g ON e.id_groupe = g.id_groupe WHERE g.id_promo = :idPromo');
            $stmt->execute(['idPromo' => $idPromo]);
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) as a FROM etudiant e JOIN groupe g ON e.id_groupe = g.id_groupe WHERE g.id_groupe = :idGroup');
            $stmt->execute(['idGroup' => $idGroup]);
        }
        return $stmt->fetch(PDO::FETCH_ASSOC)['a'];
    }

    //Récupère le nom et prénom d'un étudiant via son ID
    public static function getNomPrenomById($pdo, $idEtud) {
        $stmt = $pdo->prepare('SELECT nom_etudiant, prenom_etudiant FROM etudiant WHERE id_etudiant = :idEtud');
        $stmt->execute(['idEtud' => $idEtud]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Récupère la liste des IDs des étudiants d'une promo ou d'un groupe
    public static function getIdsByPromoOrGroup($pdo, $idPromo, $idGroupe) {
        if ($idGroupe == '0') {
            $stmt = $pdo->prepare('SELECT id_etudiant FROM etudiant e JOIN groupe g ON e.id_groupe = g.id_groupe WHERE g.id_promo = :idPromo');
            $stmt->execute(['idPromo' => $idPromo]);
        } else {
            $stmt = $pdo->prepare('SELECT id_etudiant FROM etudiant e JOIN groupe g ON e.id_groupe = g.id_groupe WHERE g.id_groupe = :idGroupe');
            $stmt->execute(['idGroupe' => $idGroupe]);
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>