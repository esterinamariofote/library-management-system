<?php
session_start();
require 'db.php';
$page_title = "Catalog";

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$role = $_SESSION['role'] ?? 'user';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// =============================================
//                  FORM SUBMISSION
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'admin') {
    $title  = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn   = trim($_POST['isbn'] ?? '');
    $cover  = $id ? $pdo->query("SELECT cover_image FROM books WHERE id = $id")->fetchColumn() : '';

    // Handle image upload
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
        $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, cover_image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $author, $isbn, $cover]);
    } elseif ($action === 'edit' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, isbn=?, cover_image=? WHERE id=?");
        $stmt->execute([$title, $author, $isbn, $cover, $id]);
    }

    header("Location: catalog.php");
    exit;
}

// =============================================
//                  DELETE
// =============================================
if ($action === 'delete' && $id > 0 && $role === 'admin') {
    $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);
    header("Location: catalog.php");
    exit;
}

// =============================================
//                  SEARCH & FILTER
// =============================================
$search       = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$author_filter = trim($_GET['author'] ?? '');

$where = [];
$params = [];

if ($search) {
    $where[] = "(title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status_filter && in_array($status_filter, ['available', 'borrowed'])) {
    $where[] = "status = ?";
    $params[] = $status_filter;
}
if ($author_filter) {
    $where[] = "author LIKE ?";
    $params[] = "%$author_filter%";
}

$whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";
$stmt = $pdo->prepare("SELECT * FROM books $whereClause ORDER BY title");
$stmt->execute($params);
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container mt-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-book-open me-2 text-primary"></i> Book Catalog
        </h2>
        <div>
            <a href="dashboard.php" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
            <?php if ($role === 'admin'): ?>
                <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add New Book
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search title, author or ISBN..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="available" <?= $status_filter === 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="borrowed" <?= $status_filter === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="author" class="form-control" placeholder="Filter by author" value="<?= htmlspecialchars($author_filter) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add / Edit Form -->
    <?php if ($action === 'add' || ($action === 'edit' && $id > 0)): ?>
        <?php
        $book = ($action === 'edit' && $id > 0)
            ? $pdo->query("SELECT * FROM books WHERE id = $id")->fetch()
            : ['title' => '', 'author' => '', 'isbn' => '', 'cover_image' => ''];
        ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><?= $action === 'add' ? 'Add New Book' : 'Edit Book' ?></h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($book['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Author <span class="text-danger">*</span></label>
                        <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($book['author']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ISBN</label>
                        <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cover Image</label>
                        <input type="file" name="cover" class="form-control" accept="image/*">
                        <?php if (!empty($book['cover_image'])): ?>
                            <div class="mt-2">
                                <small>Current:</small><br>
                                <img src="<?= htmlspecialchars($book['cover_image']) ?>" alt="Current cover" style="max-height:120px;">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> <?= $action === 'add' ? 'Add Book' : 'Update Book' ?>
                    </button>
                    <a href="catalog.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Book List -->
    <?php if (empty($books)): ?>
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i> No books found matching your criteria.
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 80px;">Cover</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Status</th>
                                <?php if ($role === 'admin'): ?>
                                    <th style="width: 150px;">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $b): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($b['cover_image'])): ?>
                                        <img src="<?= htmlspecialchars($b['cover_image']) ?>" alt="Cover" class="img-thumbnail" style="width: 60px; height: 80px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light text-muted text-center rounded" style="width: 60px; height: 80px; line-height: 80px;">
                                            <i class="fas fa-book fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($b['title']) ?></td>
                                <td><?= htmlspecialchars($b['author']) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($b['isbn'] ?? '—') ?></small></td>
                                <td>
                                    <span class="badge bg-<?= $b['status'] === 'available' ? 'success' : 'warning' ?> px-3 py-2">
                                        <?= ucfirst($b['status'] ?? 'unknown') ?>
                                    </span>
                                </td>
                                <?php if ($role === 'admin'): ?>
                                    <td>
                                        <a href="?action=edit&id=<?= $b['id'] ?>" class="btn btn-sm btn-warning me-1">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this book?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>