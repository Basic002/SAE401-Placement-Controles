<?php

/**
 * Modèle pour la table `etudiant`.
 *
 * Schéma : id_etudiant (PK), nom_etudiant, prenom_etudiant,
 *          id_groupe (FK), demigr, tiers_temps, mob_reduite
 */
class EtudiantModel
{
    // ------------------------------------------------------------------
    // LECTURE
    // ------------------------------------------------------------------

    /**
     * Retourne tous les étudiants d'un groupe, triés par nom puis prénom.
     *
     * @param PDO $pdo
     * @param int $idGroupe
     * @return array<int, array<string, mixed>>
     */
    public static function findByGroupe(PDO $pdo, int $idGroupe): array
    {
        $stmt = $pdo->prepare(
            'SELECT id_etudiant, nom_etudiant, prenom_etudiant,
                    id_groupe, demigr, tiers_temps, mob_reduite
               FROM etudiant
              WHERE id_groupe = :id_groupe
              ORDER BY nom_etudiant ASC, prenom_etudiant ASC'
        );
        $stmt->execute(['id_groupe' => $idGroupe]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne tous les étudiants d'une promotion (via la jointure groupe).
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @return array<int, array<string, mixed>>
     */
    public static function findByPromo(PDO $pdo, int $idPromo): array
    {
        $stmt = $pdo->prepare(
            'SELECT e.id_etudiant, e.nom_etudiant, e.prenom_etudiant,
                    e.id_groupe, g.nom_groupe,
                    e.demigr, e.tiers_temps, e.mob_reduite
               FROM etudiant e
               JOIN groupe g ON e.id_groupe = g.id_groupe
              WHERE g.id_promo = :id_promo
              ORDER BY g.nom_groupe ASC, e.nom_etudiant ASC, e.prenom_etudiant ASC'
        );
        $stmt->execute(['id_promo' => $idPromo]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne un étudiant par son identifiant.
     *
     * @param PDO $pdo
     * @param int $idEtudiant
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idEtudiant): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT id_etudiant, nom_etudiant, prenom_etudiant,
                    id_groupe, demigr, tiers_temps, mob_reduite
               FROM etudiant
              WHERE id_etudiant = :id_etudiant'
        );
        $stmt->execute(['id_etudiant' => $idEtudiant]);
        return $stmt->fetch();
    }

    /**
     * Retourne le nom et le prénom d'un étudiant (utilisé pour l'affichage du plan).
     *
     * @param PDO $pdo
     * @param int $idEtudiant
     * @return array<string, mixed>|false
     */
    public static function getNomPrenomById(PDO $pdo, int $idEtudiant): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT nom_etudiant, prenom_etudiant
               FROM etudiant
              WHERE id_etudiant = :id_etudiant'
        );
        $stmt->execute(['id_etudiant' => $idEtudiant]);
        return $stmt->fetch();
    }

    /**
     * Retourne les caractéristiques spéciales d'une liste d'étudiants
     * (mob_reduite, demigr, tiers_temps) pour l'algorithme de placement.
     *
     * @param PDO        $pdo
     * @param list<int>  $idsEtudiants
     * @return array<int, array<string, mixed>>
     */
    public static function getCaracteristiques(PDO $pdo, array $idsEtudiants): array
    {
        if (empty($idsEtudiants)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($idsEtudiants), '?'));
        $stmt = $pdo->prepare(
            "SELECT id_etudiant, mob_reduite, demigr, tiers_temps
               FROM etudiant
              WHERE id_etudiant IN ($placeholders)"
        );
        $stmt->execute($idsEtudiants);
        return $stmt->fetchAll();
    }

    /**
     * Retourne la liste des IDs d'étudiants d'une promo ou d'un groupe.
     * Si $idGroupe vaut 0, retourne tous les étudiants de la promo.
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @param int $idGroupe  0 = toute la promo
     * @return list<int>
     */
    public static function getIdsByPromoOrGroupe(PDO $pdo, int $idPromo, int $idGroupe): array
    {
        if ($idGroupe === 0) {
            $stmt = $pdo->prepare(
                'SELECT e.id_etudiant
                   FROM etudiant e
                   JOIN groupe g ON e.id_groupe = g.id_groupe
                  WHERE g.id_promo = :id_promo'
            );
            $stmt->execute(['id_promo' => $idPromo]);
        } else {
            $stmt = $pdo->prepare(
                'SELECT id_etudiant
                   FROM etudiant
                  WHERE id_groupe = :id_groupe'
            );
            $stmt->execute(['id_groupe' => $idGroupe]);
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Compte les étudiants d'une promo ou d'un groupe.
     * Si $idGroupe vaut 0, compte toute la promo.
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @param int $idGroupe  0 = toute la promo
     * @return int
     */
    public static function countByPromoOrGroupe(PDO $pdo, int $idPromo, int $idGroupe): int
    {
        if ($idGroupe === 0) {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM etudiant e
                   JOIN groupe g ON e.id_groupe = g.id_groupe
                  WHERE g.id_promo = :id_promo'
            );
            $stmt->execute(['id_promo' => $idPromo]);
        } else {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*) FROM etudiant
                  WHERE id_groupe = :id_groupe'
            );
            $stmt->execute(['id_groupe' => $idGroupe]);
        }
        return (int) $stmt->fetchColumn();
    }

    // ------------------------------------------------------------------
    // ÉCRITURE
    // ------------------------------------------------------------------

    /**
     * Crée un nouvel étudiant et incrémente nb_etud dans son groupe.
     * Retourne l'id_etudiant créé, ou false si un doublon est détecté.
     *
     * @param PDO    $pdo
     * @param string $nomEtudiant
     * @param string $prenomEtudiant
     * @param int    $idGroupe
     * @param int    $tiers_temps    0 ou 1
     * @param int    $mob_reduite    0 ou 1
     * @param int    $demigr         0 ou 1
     * @return int|false  id_etudiant ou false si doublon
     */
    public static function create(
        PDO $pdo,
        string $nomEtudiant,
        string $prenomEtudiant,
        int $idGroupe,
        int $tiers_temps = 0,
        int $mob_reduite = 0,
        int $demigr = 0
    ): int|false {
        // Vérification doublon
        $check = $pdo->prepare(
            'SELECT COUNT(*) FROM etudiant
              WHERE nom_etudiant = :nom AND prenom_etudiant = :prenom AND id_groupe = :id_groupe'
        );
        $check->execute([
            'nom'       => strtoupper($nomEtudiant),
            'prenom'    => ucfirst($prenomEtudiant),
            'id_groupe' => $idGroupe,
        ]);
        if ((int) $check->fetchColumn() > 0) {
            return false;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO etudiant (nom_etudiant, prenom_etudiant, id_groupe, tiers_temps, mob_reduite, demigr)
             VALUES (:nom, :prenom, :id_groupe, :tiers_temps, :mob_reduite, :demigr)'
        );
        $stmt->execute([
            'nom'         => strtoupper($nomEtudiant),
            'prenom'      => ucfirst($prenomEtudiant),
            'id_groupe'   => $idGroupe,
            'tiers_temps' => $tiers_temps,
            'mob_reduite' => $mob_reduite,
            'demigr'      => $demigr,
        ]);
        $newId = (int) $pdo->lastInsertId();

        // Mise à jour du compteur dans le groupe
        $pdo->prepare('UPDATE groupe SET nb_etud = nb_etud + 1 WHERE id_groupe = :id_groupe')
            ->execute(['id_groupe' => $idGroupe]);

        return $newId;
    }

    /**
     * Met à jour les données d'un étudiant.
     * Gère le cas où le groupe change (mise à jour de nb_etud dans les deux groupes).
     *
     * @param PDO    $pdo
     * @param int    $idEtudiant
     * @param string $nomEtudiant
     * @param string $prenomEtudiant
     * @param int    $idGroupe
     * @param int    $tiers_temps
     * @param int    $mob_reduite
     * @param int    $demigr
     * @return bool
     */
    public static function update(
        PDO $pdo,
        int $idEtudiant,
        string $nomEtudiant,
        string $prenomEtudiant,
        int $idGroupe,
        int $tiers_temps,
        int $mob_reduite,
        int $demigr
    ): bool {
        // Récupère l'ancien groupe pour mettre à jour nb_etud si nécessaire
        $ancien = self::findById($pdo, $idEtudiant);

        $stmt = $pdo->prepare(
            'UPDATE etudiant
                SET nom_etudiant    = :nom,
                    prenom_etudiant = :prenom,
                    id_groupe       = :id_groupe,
                    tiers_temps     = :tiers_temps,
                    mob_reduite     = :mob_reduite,
                    demigr          = :demigr
              WHERE id_etudiant = :id_etudiant'
        );
        $ok = $stmt->execute([
            'nom'          => strtoupper($nomEtudiant),
            'prenom'       => ucfirst($prenomEtudiant),
            'id_groupe'    => $idGroupe,
            'tiers_temps'  => $tiers_temps,
            'mob_reduite'  => $mob_reduite,
            'demigr'       => $demigr,
            'id_etudiant'  => $idEtudiant,
        ]);

        // Si le groupe a changé, mettre à jour les compteurs
        if ($ok && $ancien && (int) $ancien['id_groupe'] !== $idGroupe) {
            $pdo->prepare('UPDATE groupe SET nb_etud = nb_etud - 1 WHERE id_groupe = :id_groupe')
                ->execute(['id_groupe' => $ancien['id_groupe']]);
            $pdo->prepare('UPDATE groupe SET nb_etud = nb_etud + 1 WHERE id_groupe = :id_groupe')
                ->execute(['id_groupe' => $idGroupe]);
        }

        return $ok;
    }

    /**
     * Supprime un étudiant et décrémente nb_etud dans son groupe.
     *
     * @param PDO $pdo
     * @param int $idEtudiant
     * @return bool
     */
    public static function delete(PDO $pdo, int $idEtudiant): bool
    {
        $etud = self::findById($pdo, $idEtudiant);

        $stmt = $pdo->prepare('DELETE FROM etudiant WHERE id_etudiant = :id_etudiant');
        $ok = $stmt->execute(['id_etudiant' => $idEtudiant]);

        if ($ok && $etud) {
            $pdo->prepare('UPDATE groupe SET nb_etud = nb_etud - 1 WHERE id_groupe = :id_groupe')
                ->execute(['id_groupe' => $etud['id_groupe']]);
        }

        return $ok;
    }

    /**
     * Supprime tous les étudiants d'une promotion (avant un ré-import CSV).
     * Remet également nb_etud à 0 pour tous les groupes concernés.
     *
     * @param PDO $pdo
     * @param int $idPromo
     * @return bool
     */
    public static function deleteByPromo(PDO $pdo, int $idPromo): bool
    {
        // Suppression (la FK CASCADE sur groupe ne couvre pas cette direction,
        // il faut donc supprimer explicitement)
        $stmt = $pdo->prepare(
            'DELETE e FROM etudiant e
               JOIN groupe g ON e.id_groupe = g.id_groupe
              WHERE g.id_promo = :id_promo'
        );
        $ok = $stmt->execute(['id_promo' => $idPromo]);

        // RAZ des compteurs
        $pdo->prepare(
            'UPDATE groupe SET nb_etud = 0 WHERE id_promo = :id_promo'
        )->execute(['id_promo' => $idPromo]);

        return $ok;
    }
}
