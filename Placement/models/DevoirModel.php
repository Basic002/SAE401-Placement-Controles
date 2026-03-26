<?php

/**
 * Modèle pour la table `devoir` et ses tables d'association :
 * `devoir_salle`, `devoir_groupe`, `devoir_promo`.
 *
 * Schéma devoir : id_devoir (PK), nom_devoir, date_devoir, heure_devoir, duree_devoir, id_mat (FK nullable)
 */
class DevoirModel
{
    // ------------------------------------------------------------------
    // LECTURE
    // ------------------------------------------------------------------

    /**
     * Retourne tous les devoirs avec le nom de la matière associée.
     *
     * @param PDO $pdo
     * @return array<int, array<string, mixed>>
     */
    public static function findAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT d.id_devoir, d.nom_devoir, d.date_devoir,
                    d.heure_devoir, d.duree_devoir, d.id_mat,
                    m.nom_mat
               FROM devoir d
               LEFT JOIN matiere m ON d.id_mat = m.id_mat
              ORDER BY d.date_devoir DESC, d.heure_devoir DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne un devoir par son identifiant.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idDevoir): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT d.id_devoir, d.nom_devoir, d.date_devoir,
                    d.heure_devoir, d.duree_devoir, d.id_mat,
                    m.nom_mat
               FROM devoir d
               LEFT JOIN matiere m ON d.id_mat = m.id_mat
              WHERE d.id_devoir = :id_devoir'
        );
        $stmt->execute(['id_devoir' => $idDevoir]);
        return $stmt->fetch();
    }

    /**
     * Retourne les salles associées à un devoir.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @return array<int, array<string, mixed>>
     */
    public static function getSalles(PDO $pdo, int $idDevoir): array
    {
        $stmt = $pdo->prepare(
            'SELECT s.id_salle, s.nom_salle, s.capacite, s.intercal
               FROM salle s
               JOIN devoir_salle ds ON s.id_salle = ds.id_salle
              WHERE ds.id_devoir = :id_devoir
              ORDER BY s.nom_salle ASC'
        );
        $stmt->execute(['id_devoir' => $idDevoir]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne les groupes associés à un devoir.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @return array<int, array<string, mixed>>
     */
    public static function getGroupes(PDO $pdo, int $idDevoir): array
    {
        $stmt = $pdo->prepare(
            'SELECT g.id_groupe, g.nom_groupe, g.id_promo,
                    COUNT(e.id_etudiant) AS nb_etud,
                    p.nom_promo, d.nom_dpt
               FROM groupe g
               JOIN devoir_groupe dg ON g.id_groupe = dg.id_groupe
               JOIN promotion p ON g.id_promo = p.id_promo
               JOIN departement d ON p.id_dpt = d.id_dpt
               LEFT JOIN etudiant e ON e.id_groupe = g.id_groupe
              WHERE dg.id_devoir = :id_devoir
              GROUP BY g.id_groupe, g.nom_groupe, g.id_promo, p.nom_promo, d.nom_dpt
              ORDER BY g.nom_groupe ASC'
        );
        $stmt->execute(['id_devoir' => $idDevoir]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne les promotions entières associées à un devoir.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @return array<int, array<string, mixed>>
     */
    public static function getPromos(PDO $pdo, int $idDevoir): array
    {
        $stmt = $pdo->prepare(
            'SELECT p.id_promo, p.nom_promo, p.annee, d.nom_dpt
               FROM promotion p
               JOIN devoir_promo dp ON p.id_promo = dp.id_promo
               JOIN departement d ON p.id_dpt = d.id_dpt
              WHERE dp.id_devoir = :id_devoir
              ORDER BY p.nom_promo ASC'
        );
        $stmt->execute(['id_devoir' => $idDevoir]);
        return $stmt->fetchAll();
    }

    // ------------------------------------------------------------------
    // ÉCRITURE — DEVOIR
    // ------------------------------------------------------------------

    /**
     * Crée un nouveau devoir.
     * Retourne l'id_devoir créé.
     *
     * @param PDO         $pdo
     * @param string      $nomDevoir
     * @param string      $dateDevoir   Format 'YYYY-MM-DD'
     * @param string      $heureDevoir  Format 'HH:MM:SS'
     * @param string      $dureeDevoir  Format 'HH:MM:SS'
     * @param int|null    $idMat
     * @return int
     */
    public static function create(
        PDO $pdo,
        string $nomDevoir,
        string $dateDevoir,
        string $heureDevoir,
        string $dureeDevoir,
        ?int $idMat = null
    ): int {
        $stmt = $pdo->prepare(
            'INSERT INTO devoir (nom_devoir, date_devoir, heure_devoir, duree_devoir, id_mat)
             VALUES (:nom_devoir, :date_devoir, :heure_devoir, :duree_devoir, :id_mat)'
        );
        $stmt->execute([
            'nom_devoir'   => $nomDevoir,
            'date_devoir'  => $dateDevoir,
            'heure_devoir' => $heureDevoir,
            'duree_devoir' => $dureeDevoir,
            'id_mat'       => $idMat,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Supprime un devoir (CASCADE supprime devoir_salle, devoir_groupe,
     * devoir_promo, placement, surveille).
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @return bool
     */
    public static function delete(PDO $pdo, int $idDevoir): bool
    {
        $stmt = $pdo->prepare('DELETE FROM devoir WHERE id_devoir = :id_devoir');
        return $stmt->execute(['id_devoir' => $idDevoir]);
    }

    // ------------------------------------------------------------------
    // ÉCRITURE — ASSOCIATIONS
    // ------------------------------------------------------------------

    /**
     * Associe une salle à un devoir.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @param int $idSalle
     * @return bool
     */
    public static function addSalle(PDO $pdo, int $idDevoir, int $idSalle): bool
    {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO devoir_salle (id_devoir, id_salle)
             VALUES (:id_devoir, :id_salle)'
        );
        return $stmt->execute(['id_devoir' => $idDevoir, 'id_salle' => $idSalle]);
    }

    /**
     * Associe un groupe à un devoir.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @param int $idGroupe
     * @return bool
     */
    public static function addGroupe(PDO $pdo, int $idDevoir, int $idGroupe): bool
    {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO devoir_groupe (id_devoir, id_groupe)
             VALUES (:id_devoir, :id_groupe)'
        );
        return $stmt->execute(['id_devoir' => $idDevoir, 'id_groupe' => $idGroupe]);
    }

    /**
     * Associe une promotion entière à un devoir.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @param int $idPromo
     * @return bool
     */
    public static function addPromo(PDO $pdo, int $idDevoir, int $idPromo): bool
    {
        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO devoir_promo (id_devoir, id_promo)
             VALUES (:id_devoir, :id_promo)'
        );
        return $stmt->execute(['id_devoir' => $idDevoir, 'id_promo' => $idPromo]);
    }
}
