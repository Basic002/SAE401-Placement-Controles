<?php

/**
 * Modèle pour la table `departement`.
 *
 * Schéma : id_dpt (PK), nom_dpt
 */
class DepartementModel
{
    /**
     * Retourne tous les départements triés par nom.
     *
     * @param PDO $pdo
     * @return array<int, array<string, mixed>>
     */
    public static function findAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT id_dpt, nom_dpt FROM departement ORDER BY nom_dpt ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne un département par son identifiant.
     *
     * @param PDO $pdo
     * @param int $idDpt
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idDpt): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT id_dpt, nom_dpt FROM departement WHERE id_dpt = :id_dpt'
        );
        $stmt->execute(['id_dpt' => $idDpt]);
        return $stmt->fetch();
    }

    /**
     * Crée un département.
     * Retourne l'id_dpt créé, ou false si un doublon est détecté.
     *
     * @param PDO    $pdo
     * @param string $nomDpt
     * @return int|false
     */
    public static function create(PDO $pdo, string $nomDpt): int|false
    {
        $check = $pdo->prepare('SELECT COUNT(*) FROM departement WHERE nom_dpt = :nom_dpt');
        $check->execute(['nom_dpt' => $nomDpt]);
        if ((int) $check->fetchColumn() > 0) {
            return false;
        }

        $stmt = $pdo->prepare('INSERT INTO departement (nom_dpt) VALUES (:nom_dpt)');
        $stmt->execute(['nom_dpt' => $nomDpt]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour un département.
     *
     * @param PDO    $pdo
     * @param int    $idDpt
     * @param string $nomDpt
     * @return bool
     */
    public static function update(PDO $pdo, int $idDpt, string $nomDpt): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE departement SET nom_dpt = :nom_dpt WHERE id_dpt = :id_dpt'
        );
        return $stmt->execute(['nom_dpt' => $nomDpt, 'id_dpt' => $idDpt]);
    }

    /**
     * Supprime un département (CASCADE sur promotion → groupe → etudiant).
     *
     * @param PDO $pdo
     * @param int $idDpt
     * @return bool
     */
    public static function delete(PDO $pdo, int $idDpt): bool
    {
        $stmt = $pdo->prepare('DELETE FROM departement WHERE id_dpt = :id_dpt');
        return $stmt->execute(['id_dpt' => $idDpt]);
    }
}
