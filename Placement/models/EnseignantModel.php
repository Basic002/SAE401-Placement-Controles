<?php
class EnseignantModel {
    
    //Récupère la liste de tous les enseignants.
    public static function getAllEnseignants($pdo) {
        $stmt = $pdo->query('SELECT login, admin FROM enseignant ORDER BY login ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Récupère un enseignant spécifique via son login.
    public static function getEnseignantByLogin($pdo, $login) {
        $stmt = $pdo->prepare('SELECT login, admin FROM enseignant WHERE login = :login');
        $stmt->execute(['login' => $login]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function createEnseignant($pdo, $login, $motDePasse, $isAdmin = 0) {
        //Hachage sécurisé du mot de passe
        $hashMdp = password_hash($motDePasse, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare('INSERT INTO enseignant (login, pass, admin) VALUES (:login, :pass, :admin)');
        return $stmt->execute([
            'login' => $login,
            'pass'  => $hashMdp,
            'admin' => $isAdmin
        ]);
    }

    //Met à jour les droits d'un enseignant
    public static function updateDroitsEnseignant($pdo, $login, $isAdmin) {
        $stmt = $pdo->prepare('UPDATE enseignant SET admin = :admin WHERE login = :login');
        return $stmt->execute([
            'admin' => $isAdmin,
            'login' => $login
        ]);
    }

    //Met à jour le mot de passe d'un enseignant.
    public static function updatePassword($pdo, $login, $nouveauMotDePasse) {
        $hashMdp = password_hash($nouveauMotDePasse, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare('UPDATE enseignant SET pass = :pass WHERE login = :login');
        return $stmt->execute([
            'pass'  => $hashMdp,
            'login' => $login
        ]);
    }

    //Supprime un enseignant de la base de données.
    public static function deleteEnseignant($pdo, $login) {
        $stmt = $pdo->prepare('DELETE FROM enseignant WHERE login = :login');
        return $stmt->execute(['login' => $login]);
    }
}
?>
