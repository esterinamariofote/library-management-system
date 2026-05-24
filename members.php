<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = $_GET['success'] ?? '';

// Handle Add / Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';

    if ($action === 'add') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed, $role]);
        header("Location: members.php?success=New member added successfully!");
        exit;
    } 
    elseif ($action === 'edit' && $id > 0) {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, password=?, role=? WHERE id=?");
            $stmt->execute([$username, $email, $hashed, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
            $stmt->execute([$username, $email, $role, $id]);
        }
        header("Location: members.php?success=Member updated successfully!");
        exit;
    }
}

// Handle Delete
if ($action === 'delete' && $id > 0) {
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    header("Location: members.php?success=Member deleted successfully!");
    exit;
}

// Fetch all users
$users = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY username")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2 text-info"></i> Member Management</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Add New Member Button -->
    <a href="?action=add" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Add New Member
    </a>

    <!-- Add / Edit Form -->
    <?php if ($action === 'add' || ($action === 'edit' && $id > 0)): ?>
        <?php
        $user = ($action === 'edit' && $id > 0) 
            ? $pdo->query("SELECT * FROM users WHERE id = $id")->fetch() 
            : ['username' => '', 'email' => '', 'role' => 'user'];
        ?>
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5><?= $action === 'add' ? 'Add New Member' : 'Edit Member' ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Password <?= $action === 'add' ? '(Required)' : '(Leave blank to keep current)' ?></label>
                        <input type="password" name="password" class="form-control" <?= $action === 'add' ? 'required' : '' ?>>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select">
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Save Member</button>
                    <a href="members.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Members Table -->
    <div class="card shadow">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'info' ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><?= date('Y-m-d', strtotime($u['created_at'])) ?></td>
                        <td>
                            <a href="?action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Delete this member?')">
                                    Delete
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
