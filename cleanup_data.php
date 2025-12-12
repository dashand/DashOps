<?php
// cleanup_data.php
// Script de nettoyage pour préparer l'environnement (Docker ou autre)
// - Vide les tâches et l'historique
// - Vide les utilisateurs
// - Recrée l'utilisateur 'admin' avec le mot de passe 'root'

require_once 'config.php';

try {
    echo "--- Début du nettoyage ---\n";

    // 1. Vidage des tables (CASCADE pour gérer les clés étrangères)
    // RESTART IDENTITY remet les compteurs d'ID à 1
    echo "Vidage des tables tasks, history, users...\n";
    $pdo->exec("TRUNCATE TABLE history, tasks, users RESTART IDENTITY CASCADE");

    // 2. Création de l'utilisateur Admin par défaut
    echo "Création de l'administrateur par défaut...\n";
    $username = 'admin';
    $password = 'root';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';

    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hash, $role]);

    echo "✅ Nettoyage terminé avec succès.\n";
    echo "Utilisateur créé : admin / root\n";

} catch (PDOException $e) {
    die("❌ Erreur : " . $e->getMessage() . "\n");
}
?>