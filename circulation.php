<?php
session_start();
require 'db.php';
$page_title = "Circulation";
include 'header.php';

// All PHP logic must be here - BEFORE any HTML
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$author_filter = trim($_GET['author'] ?? '');

$where = "";
$params = [];

if ($search) {
    $where .= "AND (b.title LIKE ? OR b.author LIKE ?) ";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status) {
    $where .= "AND b.status = ? ";
    $params[] = $status;
}

if ($author_filter) {
    $where .= "AND b.author LIKE ? ";
    $params[] = "%$author_filter%";
}

$books = $pdo->prepare("SELECT b.*, br.borrow_date, br.due_date 
                        FROM books b 
                        LEFT JOIN borrowings br ON b.id = br.book_id AND br.status = 'borrowed' 
                        WHERE 1=1 $where 
                        ORDER BY b.title");
$books->execute($params);
$books = $books->fetchAll();

// Reserve book
if (isset($_GET['reserve']) && $id = (int)$_GET['reserve']) {
    $pdo->prepare("INSERT INTO reservations (user_id, book_id, reservation_date) VALUES (?, ?, CURDATE())")->execute([$user_id, $id]);
    header("Location: circulation.php");
    exit;
}

// Checkout (set due_date = borrow_date + 14 days)
if (isset($_GET['checkout']) && $id = (int)$_GET['checkout']) {
    $stmt = $pdo->prepare("UPDATE books SET status='borrowed' WHERE id=? AND status='available'");
    $stmt->execute([$id]);

    $stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, borrow_date, due_date) 
                           VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY))");
    $stmt->execute([$user_id, $id]);

    header("Location: circulation.php");
    exit;
}

// Checkin
if (isset($_GET['checkin']) && $id = (int)$_GET['checkin']) {
    $pdo->prepare("UPDATE books SET status='available' WHERE id=?")->execute([$id]);
    $pdo->prepare("UPDATE borrowings SET status='returned', return_date=CURDATE() WHERE book_id=? AND status='borrowed'")->execute([$id]);
    header("Location: circulation.php");
    exit;
}

// Fetch books with borrowing info
$books = $pdo->query("SELECT b.*, br.borrow_date, br.due_date 
                      FROM books b 
                      LEFT JOIN borrowings br ON b.id = br.book_id AND br.status = 'borrowed' 
                      ORDER BY b.title")->fetchAll();
?>

<div class="container mt-4">
    <h2>Circulation (Check In / Out)</h2>

<form method="GET" class="mb-4">
    <div class="row">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search title/author..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="borrowed" <?= $status === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="author" class="form-control" placeholder="Author filter" value="<?= htmlspecialchars($author_filter) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </div>
</form>

    <a href="dashboard.php" class="btn btn-secondary mb-3">← Back</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><span class="badge bg-<?= $book['status']=='available' ? 'success' : 'warning' ?>"><?= ucfirst($book['status']) ?></span></td>
                <td><?= $book['due_date'] ? $book['due_date'] : '—' ?></td>
                <td>
                    <?php if ($book['status'] === 'available'): ?>
                        <a href="?checkout=<?= $book['id'] ?>" class="btn btn-success btn-sm">Check Out</a>
                    <?php elseif ($role === 'admin' || (isset($book['user_id']) && $book['user_id'] == $user_id)): ?>
                        <a href="?checkin=<?= $book['id'] ?>" class="btn btn-info btn-sm">Check In</a>
                    <?php else: ?>
                        Borrowed
                    <?php endif; ?>
                </td>

<td>
    <?php if ($book['status'] === 'available'): ?>
        <a href="?checkout=<?= $book['id'] ?>" class="btn btn-success btn-sm">Check Out</a>
    <?php elseif ($role === 'admin' || $book['user_id'] == $user_id): ?>
        <a href="?checkin=<?= $book['id'] ?>" class="btn btn-info btn-sm">Check In</a>
    <?php else: ?>
        Borrowed
        <a href="?reserve=<?= $book['id'] ?>" class="btn btn-secondary btn-sm">Reserve</a>
    <?php endif; ?>
</td>

            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
