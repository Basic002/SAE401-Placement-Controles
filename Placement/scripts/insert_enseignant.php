<?php
/**
 * Script d'insertion d'un enseignant avec mot de passe BCrypt.
 * USAGE : php scripts/insert_enseignant.php
 * 
 * À exécuter en ligne de commande uniquement.
 * Ne doit JAMAIS être accessible via le navigateur web.
 */

// Sécurité : on bloque l'exécution via HTTP
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Accès interdit. Ce script s'exécute uniquement en ligne de commande.");
}

require_once __DIR__ . '/../config/connexion.php';

// ============================================================
// Données du nouvel enseignant à insérer
// Modifier ces valeurs avant exécution
// ============================================================
$data = [
    'nom'    => 'Dupont',
    'prenom' => 'Jean',
    'sexe'   => 'M',
    'login'  => 'jdupont',
    'pass'   => '1234',
    'admin'  => 1,
];

// ============================================================
// CORRECTION BDD4-HACHAGE :
// password_hash() avec PASSWORD_BCRYPT :
// - Génère automatiquement un salt aléatoire unique
// - Intègre le salt dans la chaîne de hash résultante
// - Le paramètre 'cost' => 12 définit le nombre d'itérations
//   (2^12 = 4096). Plus il est élevé, plus c'est lent pour
//   un attaquant. 12 est le standard recommandé en 2025.
//   La valeur par défaut PHP est 10 (datant de 2012).
// ============================================================
$hash = password_hash($data['pass'], PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $sql = '
        INSERT INTO enseignant (nom_ens, prenom_ens, sexe, login, pass, admin)
        VALUES (:nom, :prenom, :sexe, :login, :pass, :admin)
    ';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nom'    => $data['nom'],
        'prenom' => $data['prenom'],
        'sexe'   => $data['sexe'],
        'login'  => $data['login'],
        'pass'   => $hash,   // On stocke UNIQUEMENT le hash, jamais le mot de passe clair
        'admin'  => $data['admin'],
    ]);

    echo "[OK] Enseignant '{$data['login']}' inséré avec succès.\n";
    echo "[INFO] Hash BCrypt généré : {$hash}\n";
    echo "[INFO] Communiquer le mot de passe initial par canal sécurisé, "
       . "puis forcer le changement à la première connexion.\n";

} catch (PDOException $e) {
    // Le UNIQUE KEY sur login (ajouté en BDD v2) lèvera une erreur
    // si le login existe déjà — comportement attendu et souhaité.
    error_log('[INSERT ENSEIGNANT] Erreur : ' . $e->getMessage());
    echo "[ERREUR] Impossible d'insérer l'enseignant : " . $e->getMessage() . "\n";
}
