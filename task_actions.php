<?php
// task_actions.php
// Traitement des actions liées aux tâches : Création, Modification, Suppression, Workflow.
// Ce script attend des requêtes POST et redirige vers index.php après traitement.

require_once 'config.php';
require_once 'auth.php';

// Seuls les utilisateurs connectés peuvent agir sur les tâches
requireLogin();

// --- Récupération des données du formulaire ---
// Utilisation de l'opérateur '??' pour définir des valeurs par défaut si les champs sont manquants.
$action = $_POST['action'] ?? '';
if (isset($_POST['delete'])) {
    $action = 'delete';
}
$taskId = $_POST['task_id'] ?? $_POST['id'] ?? null;
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$family = $_POST['family'] ?? 'IG'; // Famille par défaut
$assignedTo = $_POST['assigned_to'] ?? null; // Null si "Personne" sélectionné
$status = $_POST['status'] ?? '';
$link = $_POST['external_link'] ?? '';
$currentUser = getCurrentUser();

/**
 * Fonction utilitaire pour enregistrer une action dans l'historique.
 * @param PDO $pdo Connexion à la base de données
 * @param int $taskId ID de la tâche
 * @param string $actionType Type d'action (ex: 'creation', 'modification', 'changement_statut')
 * @param string $details Détails de l'action
 * @param string $oldValue Ancienne valeur (optionnel)
 * @param string $newValue Nouvelle valeur (optionnel)
 */
function logHistory($pdo, $taskId, $actionType, $details, $oldValue = null, $newValue = null)
{
    global $currentUser;
    $stmt = $pdo->prepare("INSERT INTO history (task_id, user_id, action_type, details, old_value, new_value) VALUES (?, (SELECT id FROM users WHERE username = ?), ?, ?, ?, ?)");
    $stmt->execute([$taskId, $currentUser, $actionType, $details, $oldValue, $newValue]);
}

try {
    // --- Validation de la famille ---
    // On s'assure que la famille soumise existe bien en base de données pour éviter les incohérences.
    if ($family) {
        $stmtCheckFamily = $pdo->prepare("SELECT COUNT(*) FROM task_families WHERE name = ?");
        $stmtCheckFamily->execute([$family]);
        if ($stmtCheckFamily->fetchColumn() == 0) {
            $family = 'IG'; // Fallback de sécurité si la famille est invalide
        }
    }

    if ($action === 'create') {
        // --- CRÉATION DE TÂCHE ---
        if ($title) {
            // Assignation par défaut à l'utilisateur courant si non spécifié
            if (empty($assignedTo)) {
                $assignedTo = $currentUser;
            }

            $stmt = $pdo->prepare("INSERT INTO tasks (title, description, family, assigned_to, external_link, status, created_by) VALUES (?, ?, ?, ?, ?, 'en_cours', ?)");
            $stmt->execute([$title, $description, $family, $assignedTo, $link, $currentUser]);

            $newTaskId = $pdo->lastInsertId();
            logHistory($pdo, $newTaskId, 'creation', "Tâche créée par $currentUser");
        }
    } elseif ($action === 'update') {
        // --- MISE À JOUR DE TÂCHE ---
        if ($taskId && canEditTask()) {
            // Récupération des anciennes valeurs pour l'historique
            $oldTaskStmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
            $oldTaskStmt->execute([$taskId]);
            $oldTask = $oldTaskStmt->fetch();

            if ($oldTask) {
                // Mise à jour de la tâche
                $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, family = ?, assigned_to = ?, external_link = ?, status = ? WHERE id = ?");
                $stmt->execute([$title, $description, $family, $assignedTo, $link, $status, $taskId]);

                // Logs des changements
                $changes = [];
                if ($oldTask['title'] !== $title) {
                    $changes[] = "Titre : {$oldTask['title']} -> $title";
                }
                if ($oldTask['status'] !== $status) {
                    $changes[] = "Statut : " . ($oldTask['status'] ?? 'N/A') . " -> $status";
                }
                if ($oldTask['assigned_to'] !== $assignedTo) {
                    $changes[] = "Assigné à : " . ($oldTask['assigned_to'] ?? 'Personne') . " -> " . ($assignedTo ?? 'Personne');
                }
                if ($oldTask['family'] !== $family) {
                    $changes[] = "Famille : {$oldTask['family']} -> $family";
                }
                if ($oldTask['description'] !== $description) {
                    $changes[] = "Description modifiée";
                }
                if ($oldTask['external_link'] !== $link) {
                    $changes[] = "Lien externe modifié";
                }

                if (!empty($changes)) {
                    $details = implode("\n", $changes);
                    // On passe null pour old_value/new_value car tout est dans details
                    logHistory($pdo, $taskId, 'modification', $details);
                }
            }
        }
    } elseif ($action === 'delete') {
        // --- SUPPRESSION DE TÂCHE ---
        if ($taskId && canEditTask()) {
            // Note: canEditTask est actuellement permissif, voir auth.php
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            // Pas d'historique possible car la tâche n'existe plus (FK contrainte cascade probablement)
        }
    } elseif ($action === 'move_task') {
        // --- CHANGEMENT DE STATUT (Drag & Drop) ---
        // Cette action est souvent appelée via AJAX
        $newStatus = $_POST['new_status'] ?? 'en_cours';
        if ($taskId && $newStatus) {
            $oldTaskStmt = $pdo->prepare("SELECT status FROM tasks WHERE id = ?");
            $oldTaskStmt->execute([$taskId]);
            $oldStatus = $oldTaskStmt->fetchColumn();

            if ($oldStatus !== $newStatus) {
                $stmt = $pdo->prepare("UPDATE tasks SET status = ? WHERE id = ?");
                $stmt->execute([$newStatus, $taskId]);
                logHistory($pdo, $taskId, 'changement_statut', "Statut changé", $oldStatus, $newStatus);
            }
        }
    }

    // Redirection après traitement pour éviter la resoumission du formulaire
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    // En production, on loggerait l'erreur dans un fichier.
    // Ici, on arrête simplement avec un message pour le débogage.
    die("Erreur lors de l'action : " . $e->getMessage());
}
?>