<?php

/**
 * Modèle pour la table `placement`.
 *
 * Schéma : id_etudiant (PK/FK), id_devoir (PK/FK), id_salle (FK), place_x, place_y
 *
 * place_x = rang (ligne), place_y = colonne dans la grille de la salle.
 */
class PlacementModel
{
    // ------------------------------------------------------------------
    // LECTURE
    // ------------------------------------------------------------------

    /**
     * Retourne tous les placements d'un devoir (toutes salles confondues),
     * avec le nom et prénom de chaque étudiant.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @return array<int, array<string, mixed>>
     */
    public static function findByDevoir(PDO $pdo, int $idDevoir): array
    {
        $stmt = $pdo->prepare(
            'SELECT pl.id_etudiant, pl.id_devoir, pl.id_salle,
                    pl.place_x, pl.place_y,
                    e.nom_etudiant, e.prenom_etudiant,
                    e.tiers_temps, e.mob_reduite,
                    s.nom_salle
               FROM placement pl
               JOIN etudiant e ON pl.id_etudiant = e.id_etudiant
               JOIN salle s ON pl.id_salle = s.id_salle
              WHERE pl.id_devoir = :id_devoir
              ORDER BY s.nom_salle ASC, pl.place_x ASC, pl.place_y ASC'
        );
        $stmt->execute(['id_devoir' => $idDevoir]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne les placements pour une salle et un devoir donnés.
     * Utilisé pour afficher le plan de salle après placement.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @param int $idSalle
     * @return array<int, array<string, mixed>>
     */
    public static function findByDevoirAndSalle(PDO $pdo, int $idDevoir, int $idSalle): array
    {
        $stmt = $pdo->prepare(
            'SELECT pl.id_etudiant, pl.place_x, pl.place_y,
                    e.nom_etudiant, e.prenom_etudiant,
                    e.tiers_temps, e.mob_reduite
               FROM placement pl
               JOIN etudiant e ON pl.id_etudiant = e.id_etudiant
              WHERE pl.id_devoir = :id_devoir
                AND pl.id_salle  = :id_salle
              ORDER BY pl.place_x ASC, pl.place_y ASC'
        );
        $stmt->execute(['id_devoir' => $idDevoir, 'id_salle' => $idSalle]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne l'historique des placements d'un étudiant.
     *
     * @param PDO $pdo
     * @param int $idEtudiant
     * @return array<int, array<string, mixed>>
     */
    public static function findByEtudiant(PDO $pdo, int $idEtudiant): array
    {
        $stmt = $pdo->prepare(
            'SELECT pl.id_devoir, pl.id_salle, pl.place_x, pl.place_y,
                    d.nom_devoir, d.date_devoir, d.heure_devoir,
                    s.nom_salle
               FROM placement pl
               JOIN devoir d ON pl.id_devoir = d.id_devoir
               JOIN salle s ON pl.id_salle = s.id_salle
              WHERE pl.id_etudiant = :id_etudiant
              ORDER BY d.date_devoir DESC'
        );
        $stmt->execute(['id_etudiant' => $idEtudiant]);
        return $stmt->fetchAll();
    }

    // ------------------------------------------------------------------
    // ÉCRITURE
    // ------------------------------------------------------------------

    /**
     * Enregistre le placement d'un étudiant pour un devoir.
     *
     * @param PDO $pdo
     * @param int $idEtudiant
     * @param int $idDevoir
     * @param int $idSalle
     * @param int $placeX  Rang (ligne dans la grille)
     * @param int $placeY  Colonne dans la grille
     * @return bool
     */
    public static function save(
        PDO $pdo,
        int $idEtudiant,
        int $idDevoir,
        int $idSalle,
        int $placeX,
        int $placeY
    ): bool {
        $stmt = $pdo->prepare(
            'INSERT INTO placement (id_etudiant, id_devoir, id_salle, place_x, place_y)
             VALUES (:id_etudiant, :id_devoir, :id_salle, :place_x, :place_y)
             ON DUPLICATE KEY UPDATE
                id_salle = VALUES(id_salle),
                place_x  = VALUES(place_x),
                place_y  = VALUES(place_y)'
        );
        return $stmt->execute([
            'id_etudiant' => $idEtudiant,
            'id_devoir'   => $idDevoir,
            'id_salle'    => $idSalle,
            'place_x'     => $placeX,
            'place_y'     => $placeY,
        ]);
    }

    /**
     * Enregistre en lot tous les placements d'une salle pour un devoir.
     * Utilisé après l'algorithme de placement (étape 3).
     * $placements est un tableau de ['id_etudiant', 'place_x', 'place_y'].
     *
     * @param PDO                              $pdo
     * @param int                              $idDevoir
     * @param int                              $idSalle
     * @param array<int, array<string, mixed>> $placements
     * @return void
     */
    public static function saveBatch(PDO $pdo, int $idDevoir, int $idSalle, array $placements): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO placement (id_etudiant, id_devoir, id_salle, place_x, place_y)
             VALUES (:id_etudiant, :id_devoir, :id_salle, :place_x, :place_y)
             ON DUPLICATE KEY UPDATE
                id_salle = VALUES(id_salle),
                place_x  = VALUES(place_x),
                place_y  = VALUES(place_y)'
        );
        foreach ($placements as $p) {
            $stmt->execute([
                'id_etudiant' => $p['id_etudiant'],
                'id_devoir'   => $idDevoir,
                'id_salle'    => $idSalle,
                'place_x'     => $p['place_x'],
                'place_y'     => $p['place_y'],
            ]);
        }
    }

    /**
     * Intervertit les places de deux étudiants dans un même devoir.
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @param int $idEtudiant1
     * @param int $idEtudiant2
     * @return bool
     */
    public static function swapPlacements(
        PDO $pdo,
        int $idDevoir,
        int $idEtudiant1,
        int $idEtudiant2
    ): bool {
        // Récupère les deux placements
        $stmt = $pdo->prepare(
            'SELECT id_salle, place_x, place_y
               FROM placement
              WHERE id_devoir = :id_devoir AND id_etudiant = :id_etudiant'
        );

        $stmt->execute(['id_devoir' => $idDevoir, 'id_etudiant' => $idEtudiant1]);
        $p1 = $stmt->fetch();

        $stmt->execute(['id_devoir' => $idDevoir, 'id_etudiant' => $idEtudiant2]);
        $p2 = $stmt->fetch();

        if (!$p1 || !$p2) {
            return false;
        }

        // Interversion
        $update = $pdo->prepare(
            'UPDATE placement
                SET id_salle = :id_salle, place_x = :place_x, place_y = :place_y
              WHERE id_devoir = :id_devoir AND id_etudiant = :id_etudiant'
        );

        $update->execute([
            'id_salle'    => $p2['id_salle'],
            'place_x'     => $p2['place_x'],
            'place_y'     => $p2['place_y'],
            'id_devoir'   => $idDevoir,
            'id_etudiant' => $idEtudiant1,
        ]);

        $update->execute([
            'id_salle'    => $p1['id_salle'],
            'place_x'     => $p1['place_x'],
            'place_y'     => $p1['place_y'],
            'id_devoir'   => $idDevoir,
            'id_etudiant' => $idEtudiant2,
        ]);

        return true;
    }

    /**
     * Supprime tous les placements d'un devoir (pour regénérer).
     *
     * @param PDO $pdo
     * @param int $idDevoir
     * @return bool
     */
    public static function deleteByDevoir(PDO $pdo, int $idDevoir): bool
    {
        $stmt = $pdo->prepare('DELETE FROM placement WHERE id_devoir = :id_devoir');
        return $stmt->execute(['id_devoir' => $idDevoir]);
    }
}
