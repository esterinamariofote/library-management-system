<?php
  // ← Always asks for password
require 'protect.php';
session_start();
require 'db.php';
$page_title = "Member Management";
include 'header.php';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $pass = $_POST['password'] ?? '';

    if ($action === 'add') {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)")
            ->execute([$username, $email, $hash, $role]);
    } elseif ($action === 'edit' && $id) {
        if ($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET username=?, email=?, password=?, role=? WHERE id=?")
                ->execute([$username, $email, $hash, $role, $id]);
        } else {
            $pdo->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?")
                ->execute([$username, $email, $role, $id]);
        }
    }
    header("Location: members.php");
    exit();
}

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    header("Location: members.php");
    exit();
}

$users = $pdo->query("SELECT * FROM users ORDER BY username")->fetchAll();
?>

<div class="container mt-4">
    <h2>Member Management</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">← Back</a>
    <a href="?action=add" class="btn btn-primary mb-3">+ Add New Member</a>

    <?php if ($action === 'add' || $action === 'edit'): ?>
        <?php $u = ($action==='edit' && $id) ? $pdo->query("SELECT * FROM users WHERE id=$id")->fetch() : []; ?>
        <div class="card p-4">
            <form method="POST">
                <div class="mb-3"><input type="text" name="username" class="form-control" value="<?= htmlspecialchars($u['username']??'') ?>" required></div>
                <div class="mb-3"><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']??'') ?>" required></div>
                <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="New password (leave blank to keep old)"></div>
                <div class="mb-3">
                    <select name="role" class="form-control">
                        <option value="user" <?= ($u['role']??'')==='user'?'selected':'' ?>>User</option>
                        <option value="admin" <?= ($u['role']??'')==='admin'?'selected':'' ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Save</button>
            </form>
        </div>
    <?php else: ?>
        <table class="table">
            <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['role'] ?></td>
                    <td>
                        <a href="?action=edit&id=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete user?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
