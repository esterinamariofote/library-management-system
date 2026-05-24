<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Export All Books
if (isset($_GET['type']) && $_GET['type'] === 'books') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="library_books.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Title', 'Author', 'ISBN', 'Status']);

    $books = $pdo->query("SELECT * FROM books ORDER BY title")->fetchAll();
    foreach ($books as $b) {
        fputcsv($output, [$b['id'], $b['title'], $b['author'], $b['isbn'], $b['status']]);
    }
    exit;
}

// Export Borrowed Books
if (isset($_GET['type']) && $_GET['type'] === 'borrowed') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="borrowed_books.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Book Title', 'Author', 'Borrowed By', 'Borrow Date', 'Due Date']);

    $borrowed = $pdo->query("SELECT bk.title, bk.author, u.username, b.borrow_date, b.due_date 
                            FROM borrowings b 
                            JOIN books bk ON b.book_id = bk.id 
                            JOIN users u ON b.user_id = u.id 
                            WHERE b.status = 'borrowed'")->fetchAll();

    foreach ($borrowed as $b) {
        fputcsv($output, [$b['title'], $b['author'], $b['username'], $b['borrow_date'], $b['due_date']]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <h2><i class="fas fa-file-export me-2"></i> Export Data</h2>

    <div class="row g-4 mt-4">
        <div class="col-md-6">
            <div class="card shadow text-center">
                <div class="card-body py-5">
                    <i class="fas fa-book fa-3x text-primary mb-3"></i>
                    <h5>All Books</h5>
                    <a href="export.php?type=books" class="btn btn-primary btn-lg mt-3">
                        Download Books (CSV)
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow text-center">
                <div class="card-body py-5">
                    <i class="fas fa-hand-holding fa-3x text-warning mb-3"></i>
                    <h5>Borrowed Books</h5>
                    <a href="export.php?type=borrowed" class="btn btn-warning btn-lg mt-3">
                        Download Borrowed Books (CSV)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-5">← Back to Dashboard</a>
</div>

</body>
</html>
