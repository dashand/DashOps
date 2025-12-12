<?php
// get_history.php
// Endpoint API pour récupérer l'historique des tâches en format JSON.
// Appelé via AJAX par les modales d'historique.

require_once 'config.php';
require_once 'auth.php';

// Sécurité : Seuls les utilisateurs connectés peuvent voir l'historique
requireLogin();

header('Content-Type: application/json');

try {
    // --- Construction de la requête SQL ---
    // On veut récupérer l'historique avec :
    // - Le nom de l'utilisateur qui a fait l'action (JOIN users)
    // - Le titre de la tâche concernée (JOIN tasks)

    $query = "
        SELECT 
            h.*, 
            u.username as user_name,
            t.title as task_title
        FROM history h
        LEFT JOIN users u ON h.user_id = u.id
        LEFT JOIN tasks t ON h.task_id = t.id
        WHERE 1=1
    ";

    $params = [];

    // --- Filtres ---

    // 1. Filtrer par ID de tâche spécifique (pour l'historique "par tâche")
    if (isset($_GET['task_id']) && !empty($_GET['task_id'])) {
        $query .= " AND h.task_id = ?";
        $params[] = $_GET['task_id'];
    }

    // 2. Filtrer par date de début (optionnel)
    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $query .= " AND h.created_at >= ?";
        $params[] = $_GET['start_date'] . ' 00:00:00';
    }

    // 3. Filtrer par date de fin (optionnel)
    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $query .= " AND h.created_at <= ?";
        $params[] = $_GET['end_date'] . ' 23:59:59';
    }

    // Tri par défaut : du plus récent au plus ancien
    $query .= " ORDER BY h.created_at DESC LIMIT 50";

    // Exécution
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourne le résultat en JSON
    echo json_encode($history);

} catch (PDOException $e) {
    // En cas d'erreur, on retourne un JSON valide avec l'erreur
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>