<?php
// admin_users.php
// Page d'administration système : Gestion des utilisateurs et configuration du tableau de bord (Colonnes).
// Requiert les droits administrateur (vérifié par requireAdmin()).

require_once 'config.php';
require_once 'auth.php';

// Vérification de sécurité
requireAdmin();

$message = '';
$error = '';

/**
 * Traitement des formulaires POST
 * Action détermine l'opération à effectuer.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ==========================================
    // GESTION UTILISATEURS
    // ==========================================
    if ($action === 'create') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if ($username && $password) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Cet utilisateur existe déjà.";
            } else {
                // Création d'un utilisateur local (mot de passe haché)
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $passwordHash, $role])) {
                    $message = "Utilisateur créé avec succès.";
                } else {
                    $error = "Erreur lors de la création de l'utilisateur.";
                }
            }
        } else {
            $error = "Veuillez remplir tous les champs.";
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? null;
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if ($id && $username) {
            if ($password) {
                // Mise à jour AVEC changement de mot de passe
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password_hash = ?, role = ? WHERE id = ?");
                $result = $stmt->execute([$username, $passwordHash, $role, $id]);
            } else {
                // Mise à jour SANS changement de mot de passe
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $result = $stmt->execute([$username, $role, $id]);
            }

            if ($result) {
                $message = "Utilisateur mis à jour avec succès.";
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        } else {
            $error = "Données invalides.";
        }

        // ==========================================
        // GESTION COLONNES (FAMILLES)
        // ==========================================
    } elseif ($action === 'create_family') {
        $name = trim($_POST['name'] ?? '');

        // Calcul automatique de l'ordre : dernier + 1
        $stmt = $pdo->query("SELECT MAX(display_order) FROM task_families");
        $maxOrder = $stmt->fetchColumn();
        $order = ($maxOrder !== false) ? $maxOrder + 1 : 1;

        if (empty($name)) {
            $error = "Le nom de la colonne est requis.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO task_families (name, display_order) VALUES (?, ?)");
                $stmt->execute([$name, $order]);
                $message = "Colonne '$name' ajoutée avec succès.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23505) { // Erreur de contrainte unique (doublon)
                    $error = "Cette colonne existe déjà.";
                } else {
                    $error = "Erreur lors de l'ajout : " . $e->getMessage();
                }
            }
        }
    } elseif ($action === 'update_family') {
        // Modification du NOM d'une famille -> Impact sur les tâches existantes
        $id = intval($_POST['id'] ?? 0);
        $newName = trim($_POST['name'] ?? '');

        if ($id && $newName) {
            try {
                // 1. Récupérer l'ancien nom pour le suivi
                $stmt = $pdo->prepare("SELECT name FROM task_families WHERE id = ?");
                $stmt->execute([$id]);
                $oldName = $stmt->fetchColumn();

                if ($oldName) {
                    $pdo->beginTransaction();

                    // 2. Mettre à jour le nom dans la table familles
                    $stmt = $pdo->prepare("UPDATE task_families SET name = ? WHERE id = ?");
                    $stmt->execute([$newName, $id]);

                    // 3. CASCADE MANUEL : Mettre à jour les tâches qui utilisaient l'ancien nom
                    if ($oldName !== $newName) {
                        $stmt = $pdo->prepare("UPDATE tasks SET family = ? WHERE family = ?");
                        $stmt->execute([$newName, $oldName]);
                    }

                    $pdo->commit();
                    $message = "Colonne mise à jour (et tâches associées migrées).";
                } else {
                    $error = "Colonne introuvable.";
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Erreur lors de la mise à jour : " . $e->getMessage();
            }
        } else {
            $error = "Données invalides.";
        }

    } elseif ($action === 'reorder_family') {
        // Réorganisation verticale (Monter/Descendre)
        $id = intval($_POST['id'] ?? 0);
        $direction = $_POST['direction'] ?? ''; // 'up' ou 'down'

        if ($id && ($direction === 'up' || $direction === 'down')) {
            try {
                $pdo->beginTransaction();

                // Récupérer l'élément courant
                $stmt = $pdo->prepare("SELECT id, display_order FROM task_families WHERE id = ?");
                $stmt->execute([$id]);
                $current = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($current) {
                    $currentOrder = $current['display_order'];

                    // Trouver l'élément adjacent à échanger
                    if ($direction === 'up') {
                        // Chercher celui juste AVANT (display_order inférieur le plus proche)
                        $stmt = $pdo->prepare("SELECT id, display_order FROM task_families WHERE display_order < ? ORDER BY display_order DESC LIMIT 1");
                    } else {
                        // Chercher celui juste APRÈS (display_order supérieur le plus proche)
                        $stmt = $pdo->prepare("SELECT id, display_order FROM task_families WHERE display_order > ? ORDER BY display_order ASC LIMIT 1");
                    }
                    $stmt->execute([$currentOrder]);
                    $adjacent = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($adjacent) {
                        // SWAP des valeurs display_order
                        $stmtUpdate = $pdo->prepare("UPDATE task_families SET display_order = ? WHERE id = ?");
                        $stmtUpdate->execute([$adjacent['display_order'], $current['id']]); // Courant prend la place de l'adjacent
                        $stmtUpdate->execute([$currentOrder, $adjacent['id']]); // Adjacent prend la place du courant

                        $message = "Ordre mis à jour.";
                    }
                }
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Erreur lors du réagencement.";
            }
        }

    } elseif ($action === 'delete_family') {
        $id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM task_families WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Colonne supprimée.";
        } catch (PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

// Récupération de la liste des utilisateurs (sans la colonne is_active qui a causé des soucis précédemment)
$stmt = $pdo->query("SELECT id, username, role, auth_source, created_at FROM users ORDER BY username ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération de la liste des colonnes triées par ordre d'affichage
$stmtFamilies = $pdo->query("SELECT * FROM task_families ORDER BY display_order ASC");
$familiesList = $stmtFamilies->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="auto">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau d'administration - IELO</title>
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <script src="js/theme.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        /* Style des boutons de tri */
        .order-btn {
            border: none;
            background: none;
            color: #6c757d;
            padding: 0 5px;
            transition: color 0.2s;
        }

        .order-btn:hover {
            color: #0d6efd;
        }

        .order-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">DashOps Admin</a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">Admin: <?= htmlspecialchars(getCurrentUser()) ?></span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Retour au Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">

        <h2 class="fw-bold mb-4">Panneau d'administration</h2>

        <!-- Alertes de Notification -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- ========================== -->
        <!-- SECTION 1: UTILISATEURS    -->
        <!-- ========================== -->
        <div class="card mb-5 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary">Gestion des Utilisateurs</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="bi bi-person-plus-fill"></i> Nouvel Utilisateur
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Utilisateur</th>
                                <th>Rôle</th>
                                <th>Source</th>
                                <th>Date de création</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <i class="bi bi-person-circle me-2 text-secondary"></i>
                                        <?= htmlspecialchars($user['username']) ?>
                                    </td>
                                    <td>
                                        <?php
                                        // Badge couleur selon le rôle
                                        $roleBadges = [
                                            'admin' => 'bg-danger',
                                            'moniteur' => 'bg-info text-dark',
                                            'user' => 'bg-secondary'
                                        ];
                                        $badgeClass = $roleBadges[$user['role']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= ucfirst($user['role']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (($user['auth_source'] ?? 'local') === 'ldap'): ?>
                                            <span class="badge bg-primary">LDAP</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border">Local</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary"
                                            onclick="openEditModal(<?= htmlspecialchars(json_encode($user)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================== -->
        <!-- SECTION 2: COLONNES        -->
        <!-- ========================== -->
        <div class="card mb-5 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-warning">Gestion des Colonnes du Dashboard</h5>
                <button class="btn btn-warning btn-sm text-dark" data-bs-toggle="modal"
                    data-bs-target="#createFamilyModal">
                    <i class="bi bi-plus-lg"></i> Nouvelle Colonne
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 80px;" class="text-center">Ordre</th>
                                <th>Nom de la colonne</th>
                                <th style="width: 150px;" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($familiesList as $index => $fam): ?>
                                <tr>
                                    <!-- Logique des flèches Monter/Descendre -->
                                    <td class="text-center">
                                        <div class="d-flex flex-column align-items-center">
                                            <!-- Bouton UP (caché si premier élément) -->
                                            <?php if ($index > 0): ?>
                                                <form method="POST" class="m-0 p-0" style="line-height: 0;">
                                                    <input type="hidden" name="action" value="reorder_family">
                                                    <input type="hidden" name="id" value="<?= $fam['id'] ?>">
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="order-btn" title="Monter">
                                                        <i class="bi bi-caret-up-fill"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted"
                                                    style="width: 16px; height: 16px; display: inline-block;"></span>
                                            <?php endif; ?>

                                            <!-- Bouton DOWN (caché si dernier élément) -->
                                            <?php if ($index < count($familiesList) - 1): ?>
                                                <form method="POST" class="m-0 p-0" style="line-height: 0;">
                                                    <input type="hidden" name="action" value="reorder_family">
                                                    <input type="hidden" name="id" value="<?= $fam['id'] ?>">
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="order-btn" title="Descendre">
                                                        <i class="bi bi-caret-down-fill"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted"
                                                    style="width: 16px; height: 16px; display: inline-block;"></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium px-2"><?= htmlspecialchars($fam['name']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                            onclick="openEditFamilyModal(<?= htmlspecialchars(json_encode($fam)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form method="POST" class="d-inline"
                                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette colonne ?');">
                                            <input type="hidden" name="action" value="delete_family">
                                            <input type="hidden" name="id" value="<?= $fam['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- === MODALS === -->

    <!-- Create User Modal -->
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ajouter un utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <select name="role" class="form-select">
                                <option value="user">Utilisateur</option>
                                <option value="moniteur">Moniteur</option>
                                <option value="admin">Administrateur</option>
                            </select>
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

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Modifier l'utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" name="username" id="edit_username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe (laisser vide pour ne pas changer)</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="Nouveau mot de passe">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <select name="role" id="edit_role" class="form-select">
                                <option value="user">Utilisateur</option>
                                <option value="moniteur">Moniteur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Family Modal -->
    <div class="modal fade" id="createFamilyModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <input type="hidden" name="action" value="create_family">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nouvelle Colonne</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom de la colonne</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <p class="text-muted small">La nouvelle colonne sera ajoutée à la fin.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning">Ajouter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Family Modal -->
    <div class="modal fade" id="editFamilyModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST">
                <input type="hidden" name="action" value="update_family">
                <input type="hidden" name="id" id="edit_family_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Renommer la Colonne</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <small><i class="bi bi-info-circle"></i> Renommer une colonne mettra automatiquement à jour
                                toutes les tâches.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom de la colonne</label>
                            <input type="text" name="name" id="edit_family_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        const editFamilyModal = new bootstrap.Modal(document.getElementById('editFamilyModal'));

        function openEditModal(user) {
            document.getElementById('edit_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_role').value = user.role;
            editModal.show();
        }

        function openEditFamilyModal(family) {
            document.getElementById('edit_family_id').value = family.id;
            document.getElementById('edit_family_name').value = family.name;
            editFamilyModal.show();
        }
    </script>
</body>

</html>