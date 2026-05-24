<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'user';
$user_id = $_SESSION['user_id'];
$success = $_GET['success'] ?? '';

// Borrow Book
if (isset($_GET['action']) && $_GET['action'] === 'checkout' && isset($_GET['book_id'])) {
    $book_id = (int)$_GET['book_id'];
    
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND status = 'available'");
    $stmt->execute([$book_id]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, borrow_date, due_date) 
                              VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY))");
        $stmt->execute([$user_id, $book_id]);
        
        $pdo->prepare("UPDATE books SET status = 'borrowed' WHERE id = ?")->execute([$book_id]);
        
        header("Location: circulation.php?success=Book borrowed successfully! Due in 14 days.");
        exit;
    }
}

// Return Book
if (isset($_GET['action']) && $_GET['action'] === 'checkin' && isset($_GET['book_id']) && $role === 'admin') {
    $book_id = (int)$_GET['book_id'];
    $pdo->prepare("UPDATE borrowings SET status = 'returned', return_date = CURDATE() WHERE book_id = ? AND status = 'borrowed'")->execute([$book_id]);
    $pdo->prepare("UPDATE books SET status = 'available' WHERE id = ?")->execute([$book_id]);
    header("Location: circulation.php?success=Book returned successfully!");
    exit;
}

// My Borrowed Books with Due Date
$my_borrowed = $pdo->prepare("SELECT b.*, bk.title, bk.author, DATEDIFF(b.due_date, CURDATE()) as days_left 
                             FROM borrowings b 
                             JOIN books bk ON b.book_id = bk.id 
                             WHERE b.user_id = ? AND b.status = 'borrowed' 
                             ORDER BY b.due_date ASC");
$my_borrowed->execute([$user_id]);
$my_books = $my_borrowed->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circulation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<div class="container mt-4">

    <h2><i class="fas fa-exchange-alt"></i> Circulation</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- My Borrowed Books -->
    <h5 class="mt-4">📖 My Borrowed Books</h5>
    <?php if (empty($my_books)): ?>
        <p class="text-muted">You have no borrowed books at the moment.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($my_books as $b): ?>
            <div class="col-md-6 mb-3">
                <div class="card <?= $b['days_left'] < 3 ? 'border-danger' : '' ?>">
                    <div class="card-body">
                        <h6><?= htmlspecialchars($b['title']) ?></h6>
                        <p><?= htmlspecialchars($b['author']) ?></p>
                        <p><strong>Due Date:</strong> <?= $b['due_date'] ?></p>
                        <?php if ($b['days_left'] <= 0): ?>
                            <span class="badge bg-danger">OVERDUE (<?= abs($b['days_left']) ?> days)</span>
                        <?php else: ?>
                            <span class="badge bg-warning"><?= $b['days_left'] ?> days left</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Available Books -->
    <h5 class="mt-5">📚 Available Books</h5>
    <div class="row">
        <?php foreach ($pdo->query("SELECT * FROM books WHERE status = 'available'")->fetchAll() as $book): ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6><?= htmlspecialchars($book['title']) ?></h6>
                    <p class="text-muted"><?= htmlspecialchars($book['author']) ?></p>
                    <a href="?action=checkout&book_id=<?= $book['id'] ?>" 
                       class="btn btn-success" onclick="return confirm('Borrow this book?')">
                        Borrow Book
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-4">← Back to Dashboard</a>
</div>

</body>
</html>

