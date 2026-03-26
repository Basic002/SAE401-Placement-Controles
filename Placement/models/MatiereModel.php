<?php

/**
 * Modèle pour la table `matiere`.
 *
 * Schéma : id_mat (PK), nom_mat, id_promo (FK)
 */
class MatiereModel
{
    // ------------------------------------------------------------------
    // LECTURE
    // ------------------------------------------------------------------

    /**
     * Retourne toutes les matières avec les informations de leur promotion et département.
     *
     * @param PDO $pdo
     * @return array<int, array<string, mixed>>
     */
    public static function findAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT m.id_mat, m.nom_mat, m.id_promo,
                    p.nom_promo, p.annee,
                    d.nom_dpt
               FROM matiere m
               JOIN promotion p ON m.id_promo = p.id_promo
               JOIN departement d ON p.id_dpt = d.id_dpt
              ORDER BY p.nom_promo ASC, m.nom_mat ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne une matière par son identifiant (avec promotion et département).
     *
     * @param PDO $pdo
     * @param int $idMat
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idMat): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT m.id_mat, m.nom_mat, m.id_promo,
                    p.nom_promo, p.annee,
                    d.nom_dpt
               FROM matiere m
               JOIN promotion p ON m.id_promo = p.id_promo
               JOIN departement d ON p.id_dpt = d.id_dpt
              WHERE m.id_mat = :id_mat'
        );
        $stmt->execute(['id_mat' => $idMat]);
        return $stmt->fetch();
    }

    /**
     * Retourne toutes les matières d'une promotion.
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @return array<int, array<string, mixed>>
     */
    public static function findByPromo(PDO $pdo, int $idPromo): array
    {
        $stmt = $pdo->prepare(
            'SELECT id_mat, nom_mat, id_promo
               FROM matiere
              WHERE id_promo = :id_promo
              ORDER BY nom_mat ASC'
        );
        $stmt->execute(['id_promo' => $idPromo]);
        return $stmt->fetchAll();
    }

    /**
     * Vérifie si une matière de même nom existe déjà dans la promotion.
     *
     * @param PDO      $pdo
     * @param string   $nomMat
     * @param int      $idPromo
     * @param int|null $excludeId
     * @return bool
     */
    public static function exists(
        PDO $pdo,
        string $nomMat,
        int $idPromo,
        ?int $excludeId = null
    ): bool {
        $sql = 'SELECT COUNT(*) FROM matiere
                 WHERE nom_mat = :nom_mat AND id_promo = :id_promo';
        $params = ['nom_mat' => $nomMat, 'id_promo' => $idPromo];

        if ($excludeId !== null) {
            $sql .= ' AND id_mat != :id_mat';
            $params['id_mat'] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ------------------------------------------------------------------
    // ÉCRITURE
    // ------------------------------------------------------------------

    /**
     * Crée une nouvelle matière.
     * Retourne l'id_mat créé, ou false si un doublon est détecté.
     *
     * @param PDO    $pdo
     * @param string $nomMat
     * @param int    $idPromo
     * @return int|false
     */
    public static function create(PDO $pdo, string $nomMat, int $idPromo): int|false
    {
        if (self::exists($pdo, $nomMat, $idPromo)) {
            return false;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO matiere (nom_mat, id_promo) VALUES (:nom_mat, :id_promo)'
        );
        $stmt->execute(['nom_mat' => $nomMat, 'id_promo' => $idPromo]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour une matière.
     * Retourne false si la nouvelle valeur crée un doublon.
     *
     * @param PDO    $pdo
     * @param int    $idMat
     * @param string $nomMat
     * @param int    $idPromo
     * @return bool
     */
    public static function update(PDO $pdo, int $idMat, string $nomMat, int $idPromo): bool
    {
        if (self::exists($pdo, $nomMat, $idPromo, $idMat)) {
            return false;
        }

        $stmt = $pdo->prepare(
            'UPDATE matiere SET nom_mat = :nom_mat, id_promo = :id_promo
              WHERE id_mat = :id_mat'
        );
        return $stmt->execute(['nom_mat' => $nomMat, 'id_promo' => $idPromo, 'id_mat' => $idMat]);
    }

    /**
     * Supprime une matière (CASCADE supprime les associations enseigne et les devoirs liés).
     *
     * @param PDO $pdo
     * @param int $idMat
     * @return bool
     */
    public static function delete(PDO $pdo, int $idMat): bool
    {
        $stmt = $pdo->prepare('DELETE FROM matiere WHERE id_mat = :id_mat');
        return $stmt->execute(['id_mat' => $idMat]);
    }
}
