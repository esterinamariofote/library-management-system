<?php
  // ← Always asks for password
require 'protect.php';
session_start();
require 'db.php';
$page_title = "Reports";
include 'header.php';

// Search
$search = trim($_GET['search'] ?? '');
$where = $search ? "AND (b.title LIKE ? OR b.author LIKE ? OR u.username LIKE ?)" : "";
$params = $search ? ["%$search%", "%$search%", "%$search%"] : [];

// Query
$borrowed = $pdo->prepare("
    SELECT 
        b.title, 
        b.author, 
        u.username, 
        br.borrow_date, 
        br.due_date, 
        br.id AS borrowing_id,
        DATEDIFF(CURDATE(), br.due_date) AS days_overdue,
        GREATEST(0, DATEDIFF(CURDATE(), br.due_date)) * 500 AS fine_ugx,
        COALESCE(f.paid, 0) AS paid,
        f.payment_date
    FROM borrowings br
    JOIN books b ON br.book_id = b.id
    JOIN users u ON br.user_id = u.id
    LEFT JOIN fines f ON f.borrowing_id = br.id
    WHERE br.status = 'borrowed' $where
    ORDER BY br.borrow_date DESC
");
$borrowed->execute($params);
$borrowed = $borrowed->fetchAll() ?: [];

$overdue = array_filter($borrowed, fn($b) => $b['days_overdue'] > 0);

// Reservations
$reservations = $pdo->query("
    SELECT b.title, b.author, u.username, r.reservation_date
    FROM reservations r
    JOIN books b ON r.book_id = b.id
    JOIN users u ON r.user_id = u.id
    WHERE r.status = 'pending'
    ORDER BY r.reservation_date ASC
")->fetchAll();
?>

<div class="container mt-4">
    <h2>Library Reports (Admin Only)</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">← Back</a>
    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by book title, author or user..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <h4>Currently Borrowed Books (<?= count($borrowed) ?>)</h4>
    <?php if (empty($borrowed)): ?>
        <div class="alert alert-info">No matches found.</div>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th><th>Author</th><th>Borrowed By</th><th>Borrow Date</th>
                    <th>Due Date</th><th>Days Overdue</th><th>Fine (UGX)</th><th>Paid</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($borrowed as $b): ?>
                <tr <?= $b['days_overdue'] > 0 ? 'class="table-danger"' : '' ?>>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['author']) ?></td>
                    <td><?= htmlspecialchars($b['username']) ?></td>
                    <td><?= $b['borrow_date'] ?></td>
                    <td><?= $b['due_date'] ?></td>
                    <td><?= $b['days_overdue'] ?></td>
                    <td><?= number_format($b['fine_ugx']) ?></td>
                    <td><?= $b['paid'] ? 'Yes (on ' . $b['payment_date'] . ')' : 'No' ?></td>
                    <td>
                        <?php if ($b['days_overdue'] > 0 && !$b['paid']): ?>
                            <a href="?mark_paid=<?= $b['borrowing_id'] ?>" class="btn btn-success btn-sm">Mark Paid</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <hr class="my-5">
    <h4>Pending Reservations (<?= count($reservations) ?>)</h4>
    <?php if (empty($reservations)): ?>
        <div class="alert alert-info">No pending reservations.</div>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Reserved By</th>
                    <th>Reservation Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['title']) ?></td>
                    <td><?= htmlspecialchars($r['author']) ?></td>
                    <td><?= htmlspecialchars($r['username']) ?></td>
                    <td><?= $r['reservation_date'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
