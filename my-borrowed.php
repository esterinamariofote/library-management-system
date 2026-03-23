<?php
session_start();
require 'db.php';
$page_title = "My Borrowed Books";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// If admin, show all borrowed books; else only user's
$where = ($role === 'admin') ? "" : "AND br.user_id = ?";
$params = ($role === 'admin') ? [] : [$user_id];

$borrowed = $pdo->prepare("
    SELECT b.title, b.author, b.cover_image, br.borrow_date, br.due_date,
           DATEDIFF(CURDATE(), br.due_date) AS days_overdue,
           GREATEST(0, DATEDIFF(CURDATE(), br.due_date)) * 500 AS fine_ugx
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    WHERE br.status = 'borrowed' $where
    ORDER BY br.borrow_date DESC
");
$borrowed->execute($params);
$borrowed = $borrowed->fetchAll();

?>

<div class="container mt-4">
    <h2><?= ($role === 'admin') ? 'All Borrowed Books' : 'My Borrowed Books' ?></h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">← Back to Dashboard</a>

    <?php if (empty($borrowed)): ?>
        <div class="alert alert-info">No borrowed books at the moment.</div>
    <?php else: ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Cover</th>
            <th>Title</th>
            <th>Author</th>
            <th>Borrow Date</th>
            <th>Due Date</th>
            <th>Days Overdue</th>
            <th>Fine (UGX)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($borrowed as $b): ?>
        <tr <?= $b['days_overdue'] > 0 ? 'class="table-danger"' : '' ?>>
            <td>
                <?php if ($b['cover_image']): ?>
                    <img src="<?= htmlspecialchars($b['cover_image']) ?>" alt="cover" width="60" class="img-thumbnail">
                <?php else: ?>
                    No cover
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($b['title']) ?></td>
            <td><?= htmlspecialchars($b['author']) ?></td>
            <td><?= $b['borrow_date'] ?></td>
            <td><?= $b['due_date'] ?></td>
            <td><?= $b['days_overdue'] ?></td>
            <td><?= number_format($b['fine_ugx']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
