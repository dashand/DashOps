<?php
// auth.php
// Gestion de l'authentification et des autorisations
// Contient les fonctions pour vérifier si l'utilisateur est connecté et gérer ses droits.

/**
 * Vérifie si l'utilisateur est connecté.
 * Redirige vers la page de login si ce n'est pas le cas.
 */
function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit; // Toujours utiliser exit après une redirection header()
    }
}

/**
 * Vérifie si l'utilisateur connecté est un administrateur.
 * Redirige vers la page d'accueil si l'utilisateur n'a pas les droits requis.
 */
function requireAdmin()
{
    requireLogin(); // On s'assure d'abord qu'il est connecté
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php'); // Redirection si pas admin
        exit;
    }
}

/**
 * Récupère le nom de l'utilisateur actuellement connecté.
 * @return string Le nom d'utilisateur ou 'Inconnu'
 */
function getCurrentUser()
{
    return $_SESSION['username'] ?? 'Inconnu';
}

/**
 * Vérifie si l'utilisateur est un administrateur.
 * @return bool
 */
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Vérifie si l'utilisateur est un moniteur.
 * @return bool
 */
function isMonitor()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'moniteur';
}

/**
 * Vérifie si l'utilisateur a le droit de modifier une tâche.
 * - Les admins peuvent tout modifier.
 * - Les utilisateurs normaux/moniteurs peuvent modifier n'importe quelle tâche (selon les règles actuelles).
 * - Cette fonction peut être étendue pour restreindre les droits à l'avenir.
 * 
 * @return bool True si autorisé
 */
function canEditTask()
{
    // Actuellement, tout utilisateur connecté peut modifier des tâches.
    return isset($_SESSION['user_id']);
}
?>