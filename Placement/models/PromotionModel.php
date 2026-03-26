<?php

/**
 * Modèle pour la table `promotion`.
 *
 * Schéma : id_promo (PK), nom_promo, annee, id_dpt (FK)
 */
class PromotionModel
{
    // ------------------------------------------------------------------
    // LECTURE
    // ------------------------------------------------------------------

    /**
     * Retourne toutes les promotions avec le nom de leur département.
     *
     * @param PDO $pdo
     * @return array<int, array<string, mixed>>
     */
    public static function findAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT p.id_promo, p.nom_promo, p.annee, p.id_dpt, d.nom_dpt
               FROM promotion p
               JOIN departement d ON p.id_dpt = d.id_dpt
              ORDER BY d.nom_dpt ASC, p.nom_promo ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne une promotion par son identifiant (avec nom du département).
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idPromo): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT p.id_promo, p.nom_promo, p.annee, p.id_dpt, d.nom_dpt
               FROM promotion p
               JOIN departement d ON p.id_dpt = d.id_dpt
              WHERE p.id_promo = :id_promo'
        );
        $stmt->execute(['id_promo' => $idPromo]);
        return $stmt->fetch();
    }

    /**
     * Retourne les promotions d'un département.
     *
     * @param PDO $pdo
     * @param int $idDpt
     * @return array<int, array<string, mixed>>
     */
    public static function findByDepartement(PDO $pdo, int $idDpt): array
    {
        $stmt = $pdo->prepare(
            'SELECT id_promo, nom_promo, annee, id_dpt
               FROM promotion
              WHERE id_dpt = :id_dpt
              ORDER BY nom_promo ASC'
        );
        $stmt->execute(['id_dpt' => $idDpt]);
        return $stmt->fetchAll();
    }

    /**
     * Vérifie si une promotion identique (même nom + même année + même dpt) existe déjà.
     *
     * @param PDO      $pdo
     * @param string   $nomPromo
     * @param int|null $annee
     * @param int      $idDpt
     * @param int|null $excludeId  Exclure cet id lors d'une modification
     * @return bool
     */
    public static function exists(
        PDO $pdo,
        string $nomPromo,
        ?int $annee,
        int $idDpt,
        ?int $excludeId = null
    ): bool {
        $sql = 'SELECT COUNT(*) FROM promotion
                 WHERE nom_promo = :nom_promo
                   AND annee     = :annee
                   AND id_dpt    = :id_dpt';
        $params = ['nom_promo' => $nomPromo, 'annee' => $annee, 'id_dpt' => $idDpt];

        if ($excludeId !== null) {
            $sql .= ' AND id_promo != :id_promo';
            $params['id_promo'] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ------------------------------------------------------------------
    // ÉCRITURE
    // ------------------------------------------------------------------

    /**
     * Crée une nouvelle promotion.
     * Retourne l'id_promo créé, ou false si un doublon est détecté.
     *
     * @param PDO      $pdo
     * @param string   $nomPromo
     * @param int|null $annee
     * @param int      $idDpt
     * @return int|false
     */
    public static function create(PDO $pdo, string $nomPromo, ?int $annee, int $idDpt): int|false
    {
        if (self::exists($pdo, $nomPromo, $annee, $idDpt)) {
            return false;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO promotion (nom_promo, annee, id_dpt)
             VALUES (:nom_promo, :annee, :id_dpt)'
        );
        $stmt->execute(['nom_promo' => $nomPromo, 'annee' => $annee, 'id_dpt' => $idDpt]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour une promotion.
     * Retourne false si la nouvelle valeur crée un doublon.
     *
     * @param PDO      $pdo
     * @param int      $idPromo
     * @param string   $nomPromo
     * @param int|null $annee
     * @param int      $idDpt
     * @return bool
     */
    public static function update(
        PDO $pdo,
        int $idPromo,
        string $nomPromo,
        ?int $annee,
        int $idDpt
    ): bool {
        if (self::exists($pdo, $nomPromo, $annee, $idDpt, $idPromo)) {
            return false;
        }

        $stmt = $pdo->prepare(
            'UPDATE promotion
                SET nom_promo = :nom_promo,
                    annee     = :annee,
                    id_dpt    = :id_dpt
              WHERE id_promo = :id_promo'
        );
        return $stmt->execute([
            'nom_promo' => $nomPromo,
            'annee'     => $annee,
            'id_dpt'    => $idDpt,
            'id_promo'  => $idPromo,
        ]);
    }

    /**
     * Supprime une promotion (CASCADE supprime les groupes, étudiants, matières, etc.).
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @return bool
     */
    public static function delete(PDO $pdo, int $idPromo): bool
    {
        $stmt = $pdo->prepare('DELETE FROM promotion WHERE id_promo = :id_promo');
        return $stmt->execute(['id_promo' => $idPromo]);
    }
}
