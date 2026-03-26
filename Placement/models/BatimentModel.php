<?php

/**
 * Modèle pour la table `batiment`.
 *
 * Schéma : id_bat (PK), nom_bat, ad_bat
 */
class BatimentModel
{
    /**
     * Retourne tous les bâtiments triés par nom.
     *
     * @param PDO $pdo
     * @return array<int, array<string, mixed>>
     */
    public static function findAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT id_bat, nom_bat, ad_bat FROM batiment ORDER BY nom_bat ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Retourne un bâtiment par son identifiant.
     *
     * @param PDO $pdo
     * @param int $idBat
     * @return array<string, mixed>|false
     */
    public static function findById(PDO $pdo, int $idBat): array|false
    {
        $stmt = $pdo->prepare(
            'SELECT id_bat, nom_bat, ad_bat FROM batiment WHERE id_bat = :id_bat'
        );
        $stmt->execute(['id_bat' => $idBat]);
        return $stmt->fetch();
    }

    /**
     * Crée un bâtiment.
     * Retourne l'id_bat créé, ou false si un doublon est détecté.
     *
     * @param PDO    $pdo
     * @param string $nomBat
     * @param string $adBat
     * @return int|false
     */
    public static function create(PDO $pdo, string $nomBat, string $adBat): int|false
    {
        $check = $pdo->prepare(
            'SELECT COUNT(*) FROM batiment WHERE nom_bat = :nom_bat AND ad_bat = :ad_bat'
        );
        $check->execute(['nom_bat' => $nomBat, 'ad_bat' => $adBat]);
        if ((int) $check->fetchColumn() > 0) {
            return false;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO batiment (nom_bat, ad_bat) VALUES (:nom_bat, :ad_bat)'
        );
        $stmt->execute(['nom_bat' => $nomBat, 'ad_bat' => $adBat]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour un bâtiment.
     *
     * @param PDO    $pdo
     * @param int    $idBat
     * @param string $nomBat
     * @param string $adBat
     * @return bool
     */
    public static function update(PDO $pdo, int $idBat, string $nomBat, string $adBat): bool
    {
        $stmt = $pdo->prepare(
            'UPDATE batiment SET nom_bat = :nom_bat, ad_bat = :ad_bat
              WHERE id_bat = :id_bat'
        );
        return $stmt->execute(['nom_bat' => $nomBat, 'ad_bat' => $adBat, 'id_bat' => $idBat]);
    }

    /**
     * Supprime un bâtiment (RESTRICT sur salle — vérifier qu'aucune salle n'utilise ce bâtiment).
     *
     * @param PDO $pdo
     * @param int $idBat
     * @return bool
     */
    public static function delete(PDO $pdo, int $idBat): bool
    {
        $stmt = $pdo->prepare('DELETE FROM batiment WHERE id_bat = :id_bat');
        return $stmt->execute(['id_bat' => $idBat]);
    }

    /**
     * Vérifie si un bâtiment est utilisé par au moins une salle.
     * Utile avant une tentative de suppression pour afficher un message d'erreur clair.
     *
     * @param PDO $pdo
     * @param int $idBat
     * @return bool
     */
    public static function isUsed(PDO $pdo, int $idBat): bool
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM salle WHERE id_bat = :id_bat');
        $stmt->execute(['id_bat' => $idBat]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
