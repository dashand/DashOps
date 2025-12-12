<?php
// config.php
// Fichier de configuration principale de l'application DashOps
// Ce fichier gère la connexion à la base de données PostgreSQL et initialise la session.

// Démarrage de la session PHP pour gérer l'état de l'utilisateur connecté
session_start();

// --- Configuration de la Base de Données (PostgreSQL) ---
// Ces constantes définissent les paramètres de connexion.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'ielo_db');
define('DB_USER', getenv('DB_USER') ?: 'ielo_user');
define('DB_PASS', getenv('DB_PASS') ?: 'ielo_password');

// --- Configuration LDAP (Variables d'environnement) ---
// Support pour l'authentification via un serveur LDAP externe.
// Si LDAP_ENABLED est 'true', l'application tentera de connecter les utilisateurs via LDAP.
define('LDAP_ENABLED', getenv('LDAP_ENABLED') === 'true');
define('LDAP_HOST', getenv('LDAP_HOST') ?: 'ldap.example.com');
define('LDAP_PORT', getenv('LDAP_PORT') ?: 389);
define('LDAP_DN', getenv('LDAP_DN') ?: 'cn=admin,dc=example,dc=com');
define('LDAP_PASS', getenv('LDAP_PASS') ?: 'secret');
define('LDAP_BASE_DN', getenv('LDAP_BASE_DN') ?: 'dc=example,dc=com');

try {
    // Connexion à la base de données via PDO (PHP Data Objects)
    // Utilisation de PDO pour sécuriser les requêtes et prévenir les injections SQL.
    $dsn = "pgsql:host=" . DB_HOST . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Lance une exception en cas d'erreur SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Retourne les résultats sous forme de tableau associatif
    ]);
} catch (PDOException $e) {
    // En cas d'erreur critique de connexion, on arrête tout et on affiche le message.
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>