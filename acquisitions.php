<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$success = $_GET['success'] ?? '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title   = trim($_POST['title'] ?? '');
    $author  = trim($_POST['author'] ?? '');
    $isbn    = trim($_POST['isbn'] ?? '');
    $price   = floatval($_POST['price'] ?? 0);
    $acq_date = $_POST['acquisition_date'] ?? date('Y-m-d');

    if (empty($title) || empty($author) || $price <= 0) {
        $error = "Title, Author and Price are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO books 
            (title, author, isbn, price, acquisition_date, status) 
            VALUES (?, ?, ?, ?, ?, 'available')");
        
        $stmt->execute([$title, $author, $isbn, $price, $acq_date]);
        
        header("Location: acquisitions.php?success=Book acquired successfully!");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acquisitions - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-cart-plus me-2 text-warning"></i> Acquisitions</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Add New Book Form -->
    <div class="card shadow mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Add New Acquired Book</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Book Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Author <span class="text-danger">*</span></label>
                        <input type="text" name="author" class="form-control" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ISBN (Optional)</label>
                        <input type="text" name="isbn" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Price (UGX) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Acquisition Date</label>
                        <input type="date" name="acquisition_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-plus"></i> Acquire Book
                </button>
            </form>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
