<?php

/**
 * Modèle pour la table `groupe`.
 *
 * Schéma : id_groupe (PK), nom_groupe, id_promo (FK), nb_etud
 */
class GroupeModel
{
    // ------------------------------------------------------------------
    // LECTURE
    // ------------------------------------------------------------------

    /**
     * Retourne tous les groupes d'une promotion, triés par nom.
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @return array<int, array<string, mixed>>
     */
    public static function findByPromo(PDO $pdo, int $idPromo): array
    {
        $stmt = $pdo->prepare(
            'SELECT id_groupe, nom_groupe, id_promo
               FROM groupe
              WHERE id_promo = :id_promo
              ORDER BY nom_groupe ASC'
        );
        $stmt->execute(['id_promo' => $idPromo]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne un groupe par son identifiant.
     *
     * @param PDO $pdo
     * @param int $idGroupe
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idGroupe): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT id_groupe, nom_groupe, id_promo, nb_etud
               FROM groupe
              WHERE id_groupe = :id_groupe'
        );
        $stmt->execute(['id_groupe' => $idGroupe]);
        return $stmt->fetch();
    }

    /**
     * Retourne les groupes d'une promotion avec le compte réel d'étudiants
     * (COUNT(*) sur la table etudiant, plus fiable que nb_etud).
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @return array<int, array<string, mixed>>
     */
    public static function findByPromoWithCount(PDO $pdo, int $idPromo): array
    {
        $stmt = $pdo->prepare(
            'SELECT g.id_groupe, g.nom_groupe, g.id_promo, g.nb_etud,
                    COUNT(e.id_etudiant) AS nb_etud_reel
               FROM groupe g
               LEFT JOIN etudiant e ON g.id_groupe = e.id_groupe
              WHERE g.id_promo = :id_promo
              GROUP BY g.id_groupe
              ORDER BY g.nom_groupe ASC'
        );
        $stmt->execute(['id_promo' => $idPromo]);
        return $stmt->fetchAll();
    }

    /**
     * Vérifie si un groupe de même nom existe déjà dans la promotion.
     *
     * @param PDO      $pdo
     * @param string   $nomGroupe
     * @param int      $idPromo
     * @param int|null $excludeId Exclure cet id (pour une modification)
     * @return bool
     */
    public static function exists(
        PDO $pdo,
        string $nomGroupe,
        int $idPromo,
        ?int $excludeId = null
    ): bool {
        $sql = 'SELECT COUNT(*) FROM groupe
                 WHERE nom_groupe = :nom_groupe AND id_promo = :id_promo';
        $params = ['nom_groupe' => $nomGroupe, 'id_promo' => $idPromo];

        if ($excludeId !== null) {
            $sql .= ' AND id_groupe != :id_groupe';
            $params['id_groupe'] = $excludeId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ------------------------------------------------------------------
    // ÉCRITURE
    // ------------------------------------------------------------------

    /**
     * Crée un nouveau groupe dans une promotion.
     * Retourne l'id_groupe créé, ou false si un doublon est détecté.
     *
     * @param PDO    $pdo
     * @param string $nomGroupe
     * @param int    $idPromo
     * @return int|false
     */
    public static function create(PDO $pdo, string $nomGroupe, int $idPromo): int|false
    {
        if (self::exists($pdo, $nomGroupe, $idPromo)) {
            return false;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO groupe (nom_groupe, id_promo, nb_etud)
             VALUES (:nom_groupe, :id_promo, 0)'
        );
        $stmt->execute(['nom_groupe' => $nomGroupe, 'id_promo' => $idPromo]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Renomme un groupe.
     * Retourne false si le nouveau nom crée un doublon.
     *
     * @param PDO    $pdo
     * @param int    $idGroupe
     * @param string $nomGroupe
     * @return bool
     */
    public static function update(PDO $pdo, int $idGroupe, string $nomGroupe): bool
    {
        $groupe = self::findById($pdo, $idGroupe);
        if (!$groupe) {
            return false;
        }
        if (self::exists($pdo, $nomGroupe, (int) $groupe['id_promo'], $idGroupe)) {
            return false;
        }

        $stmt = $pdo->prepare(
            'UPDATE groupe SET nom_groupe = :nom_groupe WHERE id_groupe = :id_groupe'
        );
        return $stmt->execute(['nom_groupe' => $nomGroupe, 'id_groupe' => $idGroupe]);
    }

    /**
     * Supprime un groupe (CASCADE supprime les étudiants associés).
     *
     * @param PDO $pdo
     * @param int $idGroupe
     * @return bool
     */
    public static function delete(PDO $pdo, int $idGroupe): bool
    {
        $stmt = $pdo->prepare('DELETE FROM groupe WHERE id_groupe = :id_groupe');
        return $stmt->execute(['id_groupe' => $idGroupe]);
    }

    /**
     * Met à jour directement le compteur nb_etud (utilisé lors de l'import CSV).
     *
     * @param PDO $pdo
     * @param int $idGroupe
     * @param int $nbEtud
     * @return bool
     */
    public static function updateNbEtud(PDO $pdo, int $idGroupe, int $nbEtud): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE groupe SET nb_etud = :nb_etud WHERE id_groupe = :id_groupe'
        );
        return $stmt->execute(['nb_etud' => $nbEtud, 'id_groupe' => $idGroupe]);
    }
}
