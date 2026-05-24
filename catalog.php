<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'user';
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = $_GET['success'] ?? '';

// Search
$search = trim($_GET['search'] ?? '');

// Handle Add & Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'admin') {
    $title  = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn   = trim($_POST['isbn'] ?? '');
    $cover  = '';

    if (!empty($_FILES['cover']['name'])) {
        $uploadDir = 'uploads/covers/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = time() . '_' . basename($_FILES['cover']['name']);
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $targetPath)) {
            $cover = $targetPath;
        }
    }

    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, cover_image, status) VALUES (?, ?, ?, ?, 'available')");
        $stmt->execute([$title, $author, $isbn, $cover]);
        header("Location: catalog.php?success=Book added successfully!");
        exit;
    } elseif ($action === 'edit' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, isbn=?, cover_image=? WHERE id=?");
        $stmt->execute([$title, $author, $isbn, $cover, $id]);
        header("Location: catalog.php?success=Book updated successfully!");
        exit;
    }
}

// Delete
if ($action === 'delete' && $id > 0 && $role === 'admin') {
    $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
    header("Location: catalog.php?success=Book deleted successfully!");
    exit;
}

// Fetch books with search
$where = $search ? "WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?" : "";
$params = $search ? ["%$search%", "%$search%", "%$search%"] : [];
$stmt = $pdo->prepare("SELECT * FROM books $where ORDER BY title");
$stmt->execute($params);
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<div class="container mt-4">

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Search -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-12 col-md-8">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by title, author or ISBN..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-6 col-md-2">
                    <a href="catalog.php" class="btn btn-secondary w-100">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
        <a href="?action=add" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Add New Book
        </a>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <?php if ($action === 'add' || ($action === 'edit' && $id > 0)): ?>
        <?php
        $book = ($action === 'edit' && $id > 0) 
            ? $pdo->query("SELECT * FROM books WHERE id = $id")->fetch() 
            : ['title'=>'', 'author'=>'', 'isbn'=>'', 'cover_image'=>''];
        ?>
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5><?= $action === 'add' ? 'Add New Book' : 'Edit Book' ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Author <span class="text-danger">*</span></label>
                        <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>ISBN</label>
                        <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label>Book Cover Image</label>
                        <input type="file" name="cover" class="form-control" accept="image/*">
                        <?php if (!empty($book['cover_image'])): ?>
                            <div class="mt-2">
                                <img src="<?= htmlspecialchars($book['cover_image']) ?>" style="max-height:180px;" class="img-thumbnail">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-success">Save Book</button>
                    <a href="catalog.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Book List -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Status</th>
                    <?php if ($role === 'admin'): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $b): ?>
                <tr>
                    <td>
                        <?php if (!empty($b['cover_image'])): ?>
                            <img src="<?= htmlspecialchars($b['cover_image']) ?>" style="width:70px;height:90px;object-fit:cover;" class="img-thumbnail">
                        <?php else: ?>
                            <i class="fas fa-book fa-2x text-muted"></i>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['author']) ?></td>
                    <td><?= htmlspecialchars($b['isbn'] ?? '-') ?></td>
                    <td>
                        <span class="badge bg-<?= $b['status'] === 'available' ? 'success' : 'warning' ?>">
                            <?= ucfirst($b['status'] ?? 'Unknown') ?>
                        </span>
                    </td>
                    <?php if ($role === 'admin'): ?>
                    <td>
                        <a href="?action=edit&id=<?= $b['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?action=delete&id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this book?')">Delete</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
