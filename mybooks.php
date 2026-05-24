<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch books borrowed by this user
$stmt = $pdo->prepare("SELECT b.*, bk.title, bk.author, bk.isbn, b.due_date,
                       DATEDIFF(b.due_date, CURDATE()) as days_left 
                       FROM borrowings b 
                       JOIN books bk ON b.book_id = bk.id 
                       WHERE b.user_id = ? AND b.status = 'borrowed' 
                       ORDER BY b.due_date ASC");
$stmt->execute([$user_id]);
$my_books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Borrowed Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<div class="container mt-4">

    <h2><i class="fas fa-hand-holding me-2"></i> My Borrowed Books</h2>
    <p class="text-muted">Books you currently have borrowed</p>

    <?php if (empty($my_books)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="fas fa-book fa-3x mb-3 text-muted"></i>
            <h5>You have no borrowed books at the moment.</h5>
            <a href="catalog.php" class="btn btn-primary mt-3">Browse Available Books</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($my_books as $b): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 <?= $b['days_left'] < 3 ? 'border-danger' : '' ?>">
                    <div class="card-body">
                        <h5><?= htmlspecialchars($b['title']) ?></h5>
                        <p class="text-muted"><?= htmlspecialchars($b['author']) ?></p>
                        <p><strong>ISBN:</strong> <?= htmlspecialchars($b['isbn'] ?? '-') ?></p>
                        <p><strong>Due Date:</strong> <?= $b['due_date'] ?></p>
                        <?php if ($b['days_left'] > 0): ?>
                            <span class="badge bg-warning"><?= $b['days_left'] ?> days left</span>
                        <?php else: ?>
                            <span class="badge bg-danger">OVERDUE (<?= abs($b['days_left']) ?> days)</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-4">← Back to Dashboard</a>
</div>

</body>
</html>
