<?php
/**
 * Connexion à la base de données via PDO.
 */

// Chemin vers fichier .env
$envPath = __DIR__ . '/../../.env';

if (file_exists($envPath)) {
    // Lit le fichier ligne par ligne
    $lignes = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        // Ignorer les lignes de commentaire (commençant par #)
        if (strpos(trim($ligne), '#') === 0) {
            continue;
        }
        // Séparer nom et valeur sur le premier '='
        if (strpos($ligne, '=') !== false) {
            [$nom, $valeur] = explode('=', $ligne, 2);
            // Supprimer les guillemets optionnels et les commentaires inline (# ...)
            $valeur = trim($valeur);
            $valeur = preg_replace('/\s+#.*$/', '', $valeur); // supprime commentaire inline
            $valeur = trim($valeur, '"\'');                   // supprime guillemets éventuels
            $_ENV[trim($nom)] = $valeur;
        }
    }
} else {
    die("Fichier .env introuvable.");
}

// Les mdp et login ne sont pas écrits en dur dans le code. 
// On utilise des variables d'environnement fichier .env exclu du dépôt Git
$host     = $_ENV['DB_HOST'] ?? 'devbdd.iutmetz.univ-lorraine.fr';
$dbname   = $_ENV['DB_NAME'] ?? 'e40250u_sae401';
$username = $_ENV['DB_USER'] ?? 'e40250u_appli';
$password = $_ENV['DB_PASS'] ?? '';  // Valeur réelle définie dans .env

try {
    // Le charset utf8mb4 est déclaré directement dans le DSN.
    // C'est la méthode recommandée : elle garantit l'encodage
    // AVANT l'établissement de la connexion, contrairement à
    // SET NAMES qui s'exécute après.
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

    $options = [
        // Lance une exception PDOException sur toute erreur SQL.
        // Sans ça, les erreurs échouent silencieusement.
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

        // Retourne les résultats sous forme de tableau associatif
        // par défaut (pas besoin de le préciser à chaque fetch).
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

        // DÉSACTIVE l'émulation des requêtes préparées côté PHP.
        // Avec false, c'est MySQL lui-même qui traite les paramètres,
        // ce qui garantit une séparation stricte code/données
        // et bloque les injections SQL de second ordre.
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    // SÉCURITÉ : On ne transmet JAMAIS le message d'erreur technique
    // à l'utilisateur (il peut révéler la structure de la BDD,
    // le nom du serveur, etc.).
    // On le journalise côté serveur pour le débogage.
    error_log('[CONNEXION BDD] Erreur : ' . $e->getMessage());
    die("Erreur de connexion à la base de données. Contactez l'administrateur.");
}