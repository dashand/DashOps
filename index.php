<?php
// index.php
// Point d'entrée principal : Tableau de bord Kanban
// Affiche les tâches triées par colonne (Famille) et permet de filtrer par date.

require_once 'config.php';
require_once 'auth.php';

// Sécurité : Seuls les utilisateurs connectés ont accès
requireLogin();

// --- Logique du filtre par date ---
// Par défaut : affiche les tâches des 3 derniers jours + toutes les tâches non terminées.
$defaultStart = date('Y-m-d', strtotime('-3 days'));
$defaultEnd = date('Y-m-d'); // Aujourd'hui

$startDate = $_GET['start_date'] ?? $defaultStart;
$endDate = $_GET['end_date'] ?? $defaultEnd;

// --- Helper pour les initiales des avatars ---
function getInitials($name)
{
    if (!$name)
        return '?';
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        $initials .= strtoupper(substr($word, 0, 1));
    }
    return substr($initials, 0, 4); // Max 4 caractères
}

// --- Récupération des Données ---

// 1. Récupération des tâches
// On récupère :
// - Toutes les tâches créées dans l'intervalle de dates choisi
// - OU les tâches qui ne sont pas "terminées" (pour qu'elles restent visibles même si vieilles)
// - Tri par date de création décroissante
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE created_at <= ? AND (created_at >= ? OR status != 'termine') ORDER BY created_at DESC");
$stmt->execute([$endDate . ' 23:59:59', $startDate . ' 00:00:00']);
$tasks = $stmt->fetchAll();

// 2. Récupération dynamique des colonnes (Familles)
$stmtFamilies = $pdo->query("SELECT name FROM task_families ORDER BY display_order ASC");
$familyNames = $stmtFamilies->fetchAll(PDO::FETCH_COLUMN);

// 3. Récupération de la liste des utilisateurs pour les listes déroulantes
$stmtUsers = $pdo->query("SELECT username FROM users ORDER BY username ASC");
$userList = $stmtUsers->fetchAll(PDO::FETCH_COLUMN);

// --- Préparation des Données pour l'Affichage ---

// Initialisation des groupes (Familles) pour s'assurer qu'elles apparaissent même vides
$families = [];
foreach ($familyNames as $name) {
    $families[$name] = [];
}
// Fallback si aucune famille n'est définie en base
if (empty($families)) {
    $families['IG'] = [];
}

// Distribution des tâches dans leur famille respective
foreach ($tasks as $task) {
    $family = $task['family'] ?? 'IG';
    if (!isset($families[$family])) {
        // Si la famille de la tâche n'existe plus, on la met dans une catégorie par défaut ou la première dispo
        $family = array_key_first($families) ?: 'IG';
    }
    $families[$family][] = $task;
}

// --- Tri Personnalisé à l'intérieur des colonnes ---
// Ordre de priorité : En cours > Bloqué > Inconnu > Terminé
$statusWeights = [
    'en_cours' => 1,
    'bloque' => 2,
    'inconnu' => 3,
    'termine' => 4
];

foreach ($families as $key => &$items) {
    usort($items, function ($a, $b) use ($statusWeights) {
        $weightA = $statusWeights[$a['status'] ?? 'en_cours'] ?? 99;
        $weightB = $statusWeights[$b['status'] ?? 'en_cours'] ?? 99;

        // Si même priorité de statut, on trie par date de création (plus récent en haut)
        if ($weightA === $weightB) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        }
        return $weightA - $weightB;
    });
}
unset($items); // Cassure de la référence

// Configuration visuelle des statuts (Badges)
$statusLabels = [
    'en_cours' => 'En cours',
    'termine' => 'Terminé',
    'inconnu' => 'Inconnu',
    'bloque' => 'Bloqué'
];
$statusColors = [
    'en_cours' => 'primary',
    'termine' => 'success',
    'inconnu' => 'secondary',
    'bloque' => 'danger'
];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DashOps</title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .kanban-col {
            min-height: 80vh;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
        }

        @media (max-width: 768px) {
            .kanban-col {
                min-height: auto;
            }
        }

        .task-card {
            background: white;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }

        /* Dark mode overrides */
        [data-bs-theme="dark"] .kanban-col {
            background: #2b3035;
        }

        [data-bs-theme="dark"] .task-card {
            background: #212529;
            border: 1px solid #373b3e;
        }

        [data-bs-theme="dark"] .avatar {
            background: #343a40;
            color: #e9ecef;
        }
    </style>
    <script src="js/theme.js"></script>
</head>

<body>
    <!-- SVG Icons Definitions (Hidden) -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check2" viewBox="0 0 16 16">
            <path
                d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
        </symbol>
        <symbol id="circle-half" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z" />
        </symbol>
        <symbol id="moon-stars-fill" viewBox="0 0 16 16">
            <path
                d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
            <path
                d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
        </symbol>
        <symbol id="sun-fill" viewBox="0 0 16 16">
            <path
                d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
        </symbol>
    </svg>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">DashOps</a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">Bonjour, <?= htmlspecialchars(getCurrentUser()) ?></span>
                <a href="profile.php" class="btn btn-outline-light btn-sm me-2">Mon Compte</a>
                <?php if (isAdmin()): ?>
                    <a href="admin_users.php" class="btn btn-outline-info btn-sm me-2">Panneau Admin</a>
                <?php endif; ?>

                <!-- Theme Switcher (Bootstrap Toggle) -->
                <div class="dropdown me-2">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center" id="bd-theme"
                        type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
                        <svg class="bi my-1 theme-icon-active" width="1em" height="1em">
                            <use href="#circle-half"></use>
                        </svg>
                        <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center"
                                data-bs-theme-value="light" aria-pressed="false">
                                <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em">
                                    <use href="#sun-fill"></use>
                                </svg>
                                Light
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center"
                                data-bs-theme-value="dark" aria-pressed="false">
                                <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em">
                                    <use href="#moon-stars-fill"></use>
                                </svg>
                                Dark
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center active"
                                data-bs-theme-value="auto" aria-pressed="true">
                                <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em">
                                    <use href="#circle-half"></use>
                                </svg>
                                Auto
                            </button>
                        </li>
                    </ul>
                </div>

                <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid px-4">
        <!-- Header & Controls -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h2 class="fw-bold m-0">Tableau de suivi</h2>

            <form class="d-flex gap-2 align-items-center" method="GET">
                <div class="input-group">
                    <span class="input-group-text">Du</span>
                    <input type="date" name="start_date" class="form-control"
                        value="<?= htmlspecialchars($startDate) ?>">
                </div>
                <div class="input-group">
                    <span class="input-group-text">Au</span>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
                </div>
                <button type="submit" class="btn btn-outline-primary">Filtrer</button>
                <?php if ($startDate !== $defaultStart || $endDate !== $defaultEnd): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Réinitialiser"><i
                            class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>

            <div>
                <button class="btn btn-secondary me-2" onclick="openHistoryModal()">
                    <i class="bi bi-clock-history"></i> Historique Global
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                    <i class="bi bi-plus-lg"></i> Nouvelle Tâche
                </button>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="row g-4 flex-md-nowrap overflow-auto pb-4">
            <?php foreach ($families as $family => $items): ?>
                <div class="col-md-2" style="min-width: 250px;">
                    <div class="kanban-col">
                        <h5 class="mb-3 text-uppercase fw-bold small text-center border-bottom pb-2">
                            <?= $family ?> <span class="badge bg-secondary rounded-pill ms-1"><?= count($items) ?></span>
                        </h5>
                        <?php foreach ($items as $task): ?>
                            <div class="task-card" onclick="openTaskModal(<?= htmlspecialchars(json_encode($task)) ?>)">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <?php
                                    $st = $task['status'] ?? 'en_cours';
                                    $badgeColor = $statusColors[$st] ?? 'secondary';
                                    $badgeLabel = $statusLabels[$st] ?? $st;
                                    ?>
                                    <span class="badge bg-<?= $badgeColor ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                                    <small class="text-muted ms-auto"><?= date('d/m', strtotime($task['created_at'])) ?></small>

                                </div>
                                <h6 class="fw-bold mb-2"><?= htmlspecialchars($task['title']) ?></h6>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="avatar"
                                        title="Assigné à : <?= htmlspecialchars($task['assigned_to'] ?: 'Personne') ?>">
                                        <?= getInitials($task['assigned_to']) ?>
                                    </div>
                                    <?php if ($task['external_link']):
                                        $linkUrl = htmlspecialchars($task['external_link']);
                                        if (!preg_match("~^(?:f|ht)tps?://~i", $linkUrl)) {
                                            $linkUrl = "https://" . $linkUrl;
                                        }
                                        ?>
                                        <a href="<?= $linkUrl ?>" target="_blank" onclick="event.stopPropagation();"
                                            title="Ouvrir le lien">
                                            <i class="bi bi-link-45deg text-primary"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- === MODALS === -->

    <!-- Create Task Modal -->
    <div class="modal fade" id="createTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="task_actions.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouvelle Tâche</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Titre</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Famille</label>
                            <select name="family" class="form-select">
                                <?php foreach ($familyNames as $name): ?>
                                    <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assigné à</label>
                                <select name="assigned_to" class="form-select">
                                    <option value="">-- Personne --</option>
                                    <?php foreach ($userList as $u): ?>
                                        <option value="<?= htmlspecialchars($u) ?>" <?= ($u === getCurrentUser()) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($u) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lien externe</label>
                            <input type="text" name="external_link" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Créer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="task_actions.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier la Tâche</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Titre</label>
                            <input type="text" name="title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Famille</label>
                                <select name="family" id="edit_family" class="form-select">
                                    <?php foreach ($familyNames as $name): ?>
                                        <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Statut</label>
                                <select name="status" id="edit_status" class="form-select bg-light fw-bold">
                                    <option value="en_cours">En cours</option>
                                    <option value="termine">Terminé</option>
                                    <option value="inconnu">Inconnu</option>
                                    <option value="bloque">Bloqué</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Assigné à</label>
                            <select name="assigned_to" id="edit_assigned_to" class="form-select">
                                <option value="">-- Personne --</option>
                                <?php foreach ($userList as $u): ?>
                                    <option value="<?= htmlspecialchars($u) ?>"><?= htmlspecialchars($u) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lien externe</label>
                            <input type="text" name="external_link" id="edit_external_link" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="submit" name="delete" value="1" class="btn btn-danger"
                            onclick="return confirm('Supprimer cette tâche ?')">Supprimer</button>
                        <div>
                            <button type="button" class="btn text-white me-2"
                                style="background-color: #fdb06d; border-color: #fdb06d;"
                                onclick="openHistoryModalFromEdit()">
                                <i class="bi bi-clock-history"></i> Historique
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- History Modal -->
    <div class="modal fade" id="historyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Historique des Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex gap-2 mb-3">
                        <input type="date" id="history_start" class="form-control" value="<?= date('Y-m-d') ?>">
                        <input type="date" id="history_end" class="form-control" value="<?= date('Y-m-d') ?>">
                        <button class="btn btn-primary" onclick="loadHistory()">Filtrer</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Utilisateur</th>
                                    <th style="width: 60%">Détails</th>
                                </tr>
                            </thead>
                            <tbody id="history_table_body">
                                <!-- Loaded via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Initialisation des modales Bootstrap
        const editModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
        const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));

        let currentHistoryTaskId = null;

        /**
         * Ouvre la modale d'historique.
         * Si taskId est fourni, filtre l'historique pour cette tâche spécifique.
         */
        function openHistoryModal(taskId = null) {
            currentHistoryTaskId = taskId;
            console.log("Lecture historique pour la tâche:", taskId);

            const title = document.querySelector('#historyModal .modal-title');
            if (taskId) {
                title.textContent = 'Historique de la tâche';
            } else {
                title.textContent = 'Historique Global';
            }

            historyModal.show();
            loadHistory();
        }

        /**
         * Wrapper pour ouvrir l'historique depuis la modale d'édition
         */
        function openHistoryModalFromEdit() {
            const id = document.getElementById('edit_id').value;
            if (id) {
                openHistoryModal(id);
            } else {
                alert("Erreur: Impossible de récupérer l'ID de la tâche.");
            }
        }

        /**
         * Charge l'historique via AJAX en appelant get_history.php
         */
        function loadHistory() {
            const start = document.getElementById('history_start').value;
            const end = document.getElementById('history_end').value;
            const tbody = document.getElementById('history_table_body');

            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Chargement...</td></tr>';

            // Construction de l'URL avec les paramètres
            let url = `get_history.php?start_date=${start}&end_date=${end}`;
            if (currentHistoryTaskId) {
                url += `&task_id=${currentHistoryTaskId}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    tbody.innerHTML = '';
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="3" class="text-center">Aucun historique trouvé.</td></tr>';
                        return;
                    }
                    data.forEach(log => {
                        // Affichage conditionnel du titre de la tâche si on est en vue globale
                        const taskTitleDisplay = (log.task_title && !currentHistoryTaskId)
                            ? `<strong>${log.task_title}</strong><br>`
                            : '';

                        const row = `
                            <tr>
                                <td>${new Date(log.created_at).toLocaleString('fr-FR')}</td>
                                <td>${log.username || 'Inconnu'}</td>
                                <td>
                                    ${taskTitleDisplay}
                                    <small class="text-muted">${log.details || ''}</small>
                                </td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    tbody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Erreur de chargement.</td></tr>';
                });
        }

        /**
         * Remplit et ouvre la modale d'édition avec les données d'une tâche
         */
        function openTaskModal(task) {
            document.getElementById('edit_id').value = task.id;
            document.getElementById('edit_title').value = task.title;
            document.getElementById('edit_description').value = task.description || '';
            document.getElementById('edit_status').value = task.status;
            // Si la famille de la tâche n'existe pas, fallback sur IG
            document.getElementById('edit_family').value = task.family || 'IG';

            // Si la tâche a un assigné qui n'est plus dans la liste (ex: supprimé), cela affichera vide
            // On pourrait ajouter une option temporaire dynamiquement si besoin, mais ici on laisse vide.
            document.getElementById('edit_assigned_to').value = task.assigned_to || '';

            document.getElementById('edit_external_link').value = task.external_link || '';
            editModal.show();
        }

        <?php if (isMonitor()): ?>
            // Rafraîchissement automatique pour les moniteurs (toutes les 5 min)
            // Empêche le refresh si une modale est ouverte pour ne pas perdre la saisie en cours.
            setInterval(() => {
                if (!document.querySelector('.modal.show')) {
                    window.location.reload();
                }
            }, 300000); // 300 000 ms = 5 minutes
        <?php endif; ?>
    </script>
</body>

</html>