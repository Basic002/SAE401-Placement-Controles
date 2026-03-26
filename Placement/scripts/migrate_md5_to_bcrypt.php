<?php
/**
 * Script de migration one-shot : remplace les hashs MD5 existants
 * en base par des mots de passe temporaires hashés en BCrypt.
 * 
 * CONTEXTE : La BDD d'origine stockait les mots de passe en MD5
 * (hash = 3e66c584cf93e3b487922bb7405b0cc4 = md5("mot_de_passe_original")).
 * MD5 étant réversible par rainbow table, on réinitialise tous
 * les comptes avec un mot de passe temporaire BCrypt.
 * 
 * USAGE : php scripts/migrate_md5_to_bcrypt.php
 * À exécuter UNE SEULE FOIS après déploiement en production.
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Accès interdit.");
}

require_once __DIR__ . '/../config/connexion.php';

// Mot de passe temporaire imposé à tous les comptes migrés.
// Chaque enseignant devra le changer à sa première connexion.
$motDePasseTemporaire = 'Placement2025!';
$hash = password_hash($motDePasseTemporaire, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    // Récupérer tous les enseignants dont le hash ressemble à du MD5
    // (32 caractères hexadécimaux) — les hashs BCrypt commencent par '$2y$'
    $stmt = $pdo->query(
        "SELECT id_ens, login FROM enseignant WHERE pass NOT LIKE '\$2y\$%'"
    );
    $enseignants = $stmt->fetchAll();

    if (empty($enseignants)) {
        echo "[INFO] Aucun hash MD5 trouvé. Migration déjà effectuée ou base vide.\n";
        exit(0);
    }

    $update = $pdo->prepare(
        "UPDATE enseignant SET pass = :hash WHERE id_ens = :id"
    );

    foreach ($enseignants as $ens) {
        $update->execute([
            'hash' => $hash,
            'id'   => $ens['id_ens'],
        ]);
        echo "[MIGRÉ] Compte '{$ens['login']}' → mot de passe temporaire appliqué.\n";
    }

    echo "\n[OK] Migration terminée. " . count($enseignants) . " compte(s) mis à jour.\n";
    echo "[ACTION REQUISE] Communiquer le mot de passe temporaire "
       . "'{$motDePasseTemporaire}' à chaque enseignant par canal sécurisé.\n";

} catch (PDOException $e) {
    error_log('[MIGRATION] Erreur : ' . $e->getMessage());
    echo "[ERREUR] " . $e->getMessage() . "\n";
}
