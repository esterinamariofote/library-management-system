<?php
// Security & session
require 'protect.php';          // ← assuming this checks if logged in + admin
session_start();
require 'db.php';               // ← your PDO connection ($pdo)

// Only admins should reach this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Acquisitions";
include 'header.php';

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title  = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn   = trim($_POST['isbn'] ?? '');
    $price  = floatval($_POST['price'] ?? 0);

    // Basic validation
    if (empty($title) || empty($author) || $price <= 0) {
        $error_message = "Title, author and valid price are required.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO books 
                (title, author, isbn, price, acquisition_date, status) 
                VALUES (?, ?, ?, ?, CURDATE(), 'available')
            ");
            
            $stmt->execute([$title, $author, $isbn, $price]);
            
            $success_message = "Book '$title' by $author acquired successfully!";
            
            // Optional: clear form after success
            $_POST = [];
            
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <h2>Acquisitions (Add New Books)</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-3">← Back</a>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" 
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Author <span class="text-danger">*</span></label>
                <input type="text" name="author" class="form-control" 
                       value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ISBN (optional)</label>
                <input type="text" name="isbn" class="form-control" 
                       value="<?= htmlspecialchars($_POST['isbn'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Price (UGX) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="price" class="form-control" 
                       value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Acquire Book</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>