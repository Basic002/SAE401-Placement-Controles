<?php
class SalleModel {
    //Récupère les données (donnee) et le plan d'une salle spécifique
    public static function getPlanBySalleId($pdo, $idSalle) {
        $stmt = $pdo->prepare('SELECT * FROM plan p JOIN salle s ON p.id_plan = s.id_plan WHERE s.id_salle = :idSalle');
        $stmt->execute(['idSalle' => $idSalle]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
