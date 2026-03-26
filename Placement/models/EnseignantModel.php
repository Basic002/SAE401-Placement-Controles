<?php

/**
 * Modèle pour la table `enseignant`.
 *
 * Schéma : id_ens (PK), nom_ens, prenom_ens, sexe, login (UNIQUE), pass, admin
 */
class EnseignantModel
{
    // ------------------------------------------------------------------
    // LECTURE
    // ------------------------------------------------------------------

    /**
     * Retourne tous les enseignants, triés par nom puis prénom.
     *
     * @param PDO $pdo
     * @return array<int, array<string, mixed>>
     */
    public static function findAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT id_ens, nom_ens, prenom_ens, sexe, login, admin
               FROM enseignant
              ORDER BY nom_ens ASC, prenom_ens ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne un enseignant par son identifiant primaire.
     *
     * @param PDO $pdo
     * @param int $idEns
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idEns): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT id_ens, nom_ens, prenom_ens, sexe, login, admin
               FROM enseignant
              WHERE id_ens = :id_ens'
        );
        $stmt->execute(['id_ens' => $idEns]);
        return $stmt->fetch();
    }

    /**
     * Retourne un enseignant par son login (utilisé pour l'authentification).
     * Inclut le hash du mot de passe — ne jamais l'exposer dans une vue.
     *
     * @param PDO    $pdo
     * @param string $login
     * @return array<string, mixed>|false
     */
    public static function findByLogin(PDO $pdo, string $login): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT id_ens, nom_ens, prenom_ens, sexe, login, pass, admin
               FROM enseignant
              WHERE login = :login'
        );
        $stmt->execute(['login' => $login]);
        return $stmt->fetch();
    }

    // ------------------------------------------------------------------
    // ÉCRITURE
    // ------------------------------------------------------------------

    /**
     * Crée un nouvel enseignant. Le mot de passe est haché par cette méthode.
     *
     * @param PDO         $pdo
     * @param string      $nomEns
     * @param string      $prenomEns
     * @param string|null $sexe       'M', 'F' ou null
     * @param string      $login
     * @param string      $motDePasse Mot de passe en clair
     * @param int         $admin      0 = non-admin, 1 = admin
     * @return int  L'id_ens du nouvel enregistrement
     */
    public static function create(
        PDO $pdo,
        string $nomEns,
        string $prenomEns,
        ?string $sexe,
        string $login,
        string $motDePasse,
        int $admin = 0
    ): int {
        $stmt = $pdo->prepare(
            'INSERT INTO enseignant (nom_ens, prenom_ens, sexe, login, pass, admin)
             VALUES (:nom_ens, :prenom_ens, :sexe, :login, :pass, :admin)'
        );
        $stmt->execute([
            'nom_ens'    => strtoupper($nomEns),
            'prenom_ens' => ucfirst($prenomEns),
            'sexe'       => $sexe,
            'login'      => $login,
            'pass'       => password_hash($motDePasse, PASSWORD_BCRYPT),
            'admin'      => $admin,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour les informations d'un enseignant (sans toucher au mot de passe).
     *
     * @param PDO         $pdo
     * @param int         $idEns
     * @param string      $nomEns
     * @param string      $prenomEns
     * @param string|null $sexe
     * @param string      $login
     * @param int         $admin
     * @return bool
     */
    public static function update(
        PDO $pdo,
        int $idEns,
        string $nomEns,
        string $prenomEns,
        ?string $sexe,
        string $login,
        int $admin
    ): bool {
        $stmt = $pdo->prepare(
            'UPDATE enseignant
                SET nom_ens    = :nom_ens,
                    prenom_ens = :prenom_ens,
                    sexe       = :sexe,
                    login      = :login,
                    admin      = :admin
              WHERE id_ens = :id_ens'
        );
        return $stmt->execute([
            'nom_ens'    => strtoupper($nomEns),
            'prenom_ens' => ucfirst($prenomEns),
            'sexe'       => $sexe,
            'login'      => $login,
            'admin'      => $admin,
            'id_ens'     => $idEns,
        ]);
    }

    /**
     * Met à jour le mot de passe d'un enseignant (hachage effectué ici).
     *
     * @param PDO    $pdo
     * @param int    $idEns
     * @param string $nouveauMotDePasse Mot de passe en clair
     * @return bool
     */
    public static function updatePassword(PDO $pdo, int $idEns, string $nouveauMotDePasse): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE enseignant SET pass = :pass WHERE id_ens = :id_ens'
        );
        return $stmt->execute([
            'pass'   => password_hash($nouveauMotDePasse, PASSWORD_BCRYPT),
            'id_ens' => $idEns,
        ]);
    }

    /**
     * Supprime un enseignant par son id.
     *
     * @param PDO $pdo
     * @param int $idEns
     * @return bool
     */
    public static function delete(PDO $pdo, int $idEns): bool
    {
        $stmt = $pdo->prepare('DELETE FROM enseignant WHERE id_ens = :id_ens');
        return $stmt->execute(['id_ens' => $idEns]);
    }

    // ------------------------------------------------------------------
    // ASSOCIATION enseigne (id_mat ↔ id_ens)
    // ------------------------------------------------------------------

    /**
     * Retourne les matières enseignées par un enseignant (avec nom de la matière).
     *
     * @param PDO $pdo
     * @param int $idEns
     * @return array<int, array<string, mixed>>
     */
    public static function getMatieresByEnseignant(PDO $pdo, int $idEns): array
    {
        $stmt = $pdo->prepare(
            'SELECT m.id_mat, m.nom_mat, m.id_promo
               FROM matiere m
               JOIN enseigne e ON m.id_mat = e.id_mat
              WHERE e.id_ens = :id_ens
              ORDER BY m.nom_mat ASC'
        );
        $stmt->execute(['id_ens' => $idEns]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne les enseignants qui enseignent une matière donnée.
     *
     * @param PDO $pdo
     * @param int $idMat
     * @return array<int, array<string, mixed>>
     */
    public static function getEnseignantsByMatiere(PDO $pdo, int $idMat): array
    {
        $stmt = $pdo->prepare(
            'SELECT ens.id_ens, ens.nom_ens, ens.prenom_ens, ens.sexe, ens.login, ens.admin
               FROM enseignant ens
               JOIN enseigne e ON ens.id_ens = e.id_ens
              WHERE e.id_mat = :id_mat
              ORDER BY ens.nom_ens ASC'
        );
        $stmt->execute(['id_mat' => $idMat]);
        return $stmt->fetchAll();
    }

    /**
     * Associe un enseignant à une matière.
     *
     * @param PDO $pdo
     * @param int $idEns
     * @param int $idMat
     * @return bool
     */
    public static function addMatiere(PDO $pdo, int $idEns, int $idMat): bool
    {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO enseigne (id_mat, id_ens) VALUES (:id_mat, :id_ens)'
        );
        return $stmt->execute(['id_mat' => $idMat, 'id_ens' => $idEns]);
    }

    /**
     * Retire l'association entre un enseignant et une matière.
     *
     * @param PDO $pdo
     * @param int $idEns
     * @param int $idMat
     * @return bool
     */
    public static function removeMatiere(PDO $pdo, int $idEns, int $idMat): bool
    {
        $stmt = $pdo->prepare(
            'DELETE FROM enseigne WHERE id_mat = :id_mat AND id_ens = :id_ens'
        );
        return $stmt->execute(['id_mat' => $idMat, 'id_ens' => $idEns]);
    }
}
