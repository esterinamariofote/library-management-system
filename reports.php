<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Statistics
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$available = $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'available'")->fetchColumn();
$borrowed = $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'borrowed'")->fetchColumn();
$total_members = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$overdue = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed' AND due_date < CURDATE()")->fetchColumn();

// Most Borrowed Books
$popular = $pdo->query("SELECT bk.title, bk.author, COUNT(*) as times 
                       FROM borrowings b 
                       JOIN books bk ON b.book_id = bk.id 
                       GROUP BY bk.id 
                       ORDER BY times DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<div class="container mt-4">

    <h2><i class="fas fa-chart-bar me-2 text-info"></i> Library Reports</h2>

    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h3 class="text-primary"><?= $total_books ?></h3>
                    <p class="text-muted">Total Books</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h3 class="text-success"><?= $available ?></h3>
                    <p class="text-muted">Available Books</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h3 class="text-warning"><?= $borrowed ?></h3>
                    <p class="text-muted">Borrowed Books</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow border-danger">
                <div class="card-body">
                    <h3 class="text-danger"><?= $overdue ?></h3>
                    <p class="text-muted">Overdue Books</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Popular Books -->
    <h4 class="mb-3">🔥 Most Borrowed Books</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Borrowed Times</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($popular as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['title']) ?></td>
                    <td><?= htmlspecialchars($p['author']) ?></td>
                    <td><strong><?= $p['times'] ?></strong> times</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-4">← Back to Dashboard</a>
</div>

</body>
</html>
