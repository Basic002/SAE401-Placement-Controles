<?php

/**
 * Modèle pour la table `salle` (et la table `plan` associée).
 *
 * Schéma salle : id_salle (PK), nom_salle, etage, id_bat (FK),
 *                id_dpt (FK), id_plan (FK), capacite, intercal
 * Schéma plan  : id_plan (PK), donnee (TEXT — chaîne encodant la grille de la salle)
 */
class SalleModel
{
    // ------------------------------------------------------------------
    // LECTURE
    // ------------------------------------------------------------------

    /**
     * Retourne toutes les salles avec les informations de leur bâtiment et département.
     *
     * @param PDO $pdo
     * @return array<int, array<string, mixed>>
     */
    public static function findAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT s.id_salle, s.nom_salle, s.etage, s.capacite, s.intercal,
                    b.nom_bat, b.ad_bat,
                    d.nom_dpt,
                    s.id_bat, s.id_dpt, s.id_plan
               FROM salle s
               JOIN batiment b ON s.id_bat = b.id_bat
               JOIN departement d ON s.id_dpt = d.id_dpt
              ORDER BY s.nom_salle ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne une salle par son identifiant (avec bâtiment et département).
     *
     * @param PDO $pdo
     * @param int $idSalle
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idSalle): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT s.id_salle, s.nom_salle, s.etage, s.capacite, s.intercal,
                    b.nom_bat, b.ad_bat,
                    d.nom_dpt,
                    s.id_bat, s.id_dpt, s.id_plan
               FROM salle s
               JOIN batiment b ON s.id_bat = b.id_bat
               JOIN departement d ON s.id_dpt = d.id_dpt
              WHERE s.id_salle = :id_salle'
        );
        $stmt->execute(['id_salle' => $idSalle]);
        return $stmt->fetch();
    }

    /**
     * Retourne la donnée brute du plan d'une salle (champ `plan.donnee`).
     * Format : chaîne de caractères délimitée par '-', ex : "111-010-111"
     * 0 = couloir, 1 = place normale, 2 = place handicapée, 3 = place inexistante.
     *
     * @param PDO $pdo
     * @param int $idSalle
     * @return array<string, mixed>|false  Contient les champs de `salle` + `plan.donnee`
     */
    public static function getPlanBySalleId(PDO $pdo, int $idSalle): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT s.id_salle, s.nom_salle, s.etage, s.capacite, s.intercal,
                    s.type_salle, s.id_bat, s.id_dpt, s.id_plan,
                    p.donnee, p.capacite_max
               FROM salle s
               JOIN plan p ON s.id_plan = p.id_plan
              WHERE s.id_salle = :id_salle'
        );
        $stmt->execute(['id_salle' => $idSalle]);
        return $stmt->fetch();
    }

    /**
     * Retourne toutes les salles triées par nom (liste simplifiée pour les selects).
     *
     * @param PDO $pdo
     * @return array<int, array<string, mixed>>
     */
    public static function findAllForSelect(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT id_salle, nom_salle, capacite, intercal
               FROM salle
              ORDER BY nom_salle ASC'
        );
        return $stmt->fetchAll();
    }

    // ------------------------------------------------------------------
    // PLAN
    // ------------------------------------------------------------------

    /**
     * Crée un nouveau plan et retourne son id_plan.
     *
     * @param PDO    $pdo
     * @param string $donnee  Chaîne encodant la grille
     * @return int
     */
    public static function createPlan(PDO $pdo, string $donnee): int
    {
        $stmt = $pdo->prepare('INSERT INTO plan (donnee) VALUES (:donnee)');
        $stmt->execute(['donnee' => $donnee]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour la donnée d'un plan existant.
     *
     * @param PDO    $pdo
     * @param int    $idPlan
     * @param string $donnee
     * @return bool
     */
    public static function updatePlan(PDO $pdo, int $idPlan, string $donnee): bool
    {
        $stmt = $pdo->prepare('UPDATE plan SET donnee = :donnee WHERE id_plan = :id_plan');
        return $stmt->execute(['donnee' => $donnee, 'id_plan' => $idPlan]);
    }

    /**
     * Supprime un plan (appelé lors de la suppression d'une salle).
     *
     * @param PDO $pdo
     * @param int $idPlan
     * @return bool
     */
    public static function deletePlan(PDO $pdo, int $idPlan): bool
    {
        $stmt = $pdo->prepare('DELETE FROM plan WHERE id_plan = :id_plan');
        return $stmt->execute(['id_plan' => $idPlan]);
    }

    // ------------------------------------------------------------------
    // ÉCRITURE SALLE
    // ------------------------------------------------------------------

    /**
     * Crée une nouvelle salle (et son plan associé).
     * Retourne l'id_salle créé.
     *
     * @param PDO    $pdo
     * @param string $nomSalle
     * @param int    $etage
     * @param int    $idBat
     * @param int    $idDpt
     * @param string $donnePlan  Chaîne encodant la grille
     * @param int    $capacite
     * @param int    $intercal   1 = intercaler les places, 0 = non
     * @return int
     */
    public static function create(
        PDO $pdo,
        string $nomSalle,
        int $etage,
        int $idBat,
        int $idDpt,
        string $donnePlan,
        int $capacite,
        int $intercal = 1
    ): int {
        $idPlan = self::createPlan($pdo, $donnePlan);

        $stmt = $pdo->prepare(
            'INSERT INTO salle (nom_salle, etage, id_bat, id_dpt, id_plan, capacite, intercal)
             VALUES (:nom_salle, :etage, :id_bat, :id_dpt, :id_plan, :capacite, :intercal)'
        );
        $stmt->execute([
            'nom_salle' => $nomSalle,
            'etage'     => $etage,
            'id_bat'    => $idBat,
            'id_dpt'    => $idDpt,
            'id_plan'   => $idPlan,
            'capacite'  => $capacite,
            'intercal'  => $intercal,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour les métadonnées d'une salle (sans toucher au plan).
     *
     * @param PDO    $pdo
     * @param int    $idSalle
     * @param string $nomSalle
     * @param int    $etage
     * @param int    $idBat
     * @param int    $idDpt
     * @param int    $capacite
     * @param int    $intercal
     * @return bool
     */
    public static function update(
        PDO $pdo,
        int $idSalle,
        string $nomSalle,
        int $etage,
        int $idBat,
        int $idDpt,
        int $capacite,
        int $intercal
    ): bool {
        $stmt = $pdo->prepare(
            'UPDATE salle
                SET nom_salle = :nom_salle,
                    etage     = :etage,
                    id_bat    = :id_bat,
                    id_dpt    = :id_dpt,
                    capacite  = :capacite,
                    intercal  = :intercal
              WHERE id_salle = :id_salle'
        );
        return $stmt->execute([
            'nom_salle' => $nomSalle,
            'etage'     => $etage,
            'id_bat'    => $idBat,
            'id_dpt'    => $idDpt,
            'capacite'  => $capacite,
            'intercal'  => $intercal,
            'id_salle'  => $idSalle,
        ]);
    }

    /**
     * Supprime une salle et son plan associé.
     *
     * @param PDO $pdo
     * @param int $idSalle
     * @return bool
     */
    public static function delete(PDO $pdo, int $idSalle): bool
    {
        // Récupère l'id_plan avant suppression (la FK CASCADE ne supprime pas plan)
        $salle = self::findById($pdo, $idSalle);

        $stmt = $pdo->prepare('DELETE FROM salle WHERE id_salle = :id_salle');
        $ok = $stmt->execute(['id_salle' => $idSalle]);

        if ($ok && $salle) {
            self::deletePlan($pdo, (int) $salle['id_plan']);
        }

        return $ok;
    }
}
