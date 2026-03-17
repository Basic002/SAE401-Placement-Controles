<?php
// On inclut ptrim.php avec le bon chemin
require_once(__DIR__ . "/../utils/ptrim.php");

try {
    // Configuration pour le serveur IUT (basée sur votre ancien projet qui marchait)
    $host = 'devbdd.iutmetz.univ-lorraine.fr'; // Le serveur BDD de l'IUT
    $dbname = 'e40250u_sae401';                // Votre nouvelle base pour la SAE 401
    $username = 'e40250u_appli';               // Votre utilisateur BDD
    $password = '32408231';                    // Votre mot de passe BDD
    
    // Création du DSN (Data Source Name)
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
    
    // Options pour gérer les erreurs et s'assurer de la compatibilité UTF-8
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ];

    // Création de la connexion PDO
    $pdo = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    // En cas d'erreur, on affiche un message propre
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>