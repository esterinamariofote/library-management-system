<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'user';
$username = $_SESSION['username'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .dashboard-card {
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">

<?php include 'includes/header.php'; ?>

<div class="container mt-4">

    <div class="text-center mb-5">
        <h1 class="display-5">Welcome Back, <?= htmlspecialchars($username) ?>!</h1>
        <p class="lead text-muted">What would you like to do today?</p>
    </div>

    <div class="row g-4">

        <!-- Catalog Card -->
        <div class="col-md-6 col-lg-4">
            <a href="catalog.php" class="text-decoration-none">
                <div class="card dashboard-card h-100 text-center shadow">
                    <div class="card-body">
                        <i class="fas fa-book fa-4x text-primary mb-3"></i>
                        <h4>Book Catalog</h4>
                        <p class="text-muted">Browse, search and manage all books</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Circulation Card -->
        <div class="col-md-6 col-lg-4">
            <a href="circulation.php" class="text-decoration-none">
                <div class="card dashboard-card h-100 text-center shadow">
                    <div class="card-body">
                        <i class="fas fa-exchange-alt fa-4x text-success mb-3"></i>
                        <h4>Circulation</h4>
                        <p class="text-muted">Issue and return books</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Reports Card -->
        <div class="col-md-6 col-lg-4">
            <a href="reports.php" class="text-decoration-none">
                <div class="card dashboard-card h-100 text-center shadow">
                    <div class="card-body">
                        <i class="fas fa-chart-bar fa-4x text-info mb-3"></i>
                        <h4>Reports</h4>
                        <p class="text-muted">View library statistics</p>
                    </div>
                </div>
            </a>
        </div>

        <?php if ($role === 'admin'): ?>
        <!-- Acquisitions -->
        <div class="col-md-6 col-lg-4">
            <a href="acquisitions.php" class="text-decoration-none">
                <div class="card dashboard-card h-100 text-center shadow">
                    <div class="card-body">
                        <i class="fas fa-cart-plus fa-4x text-warning mb-3"></i>
                        <h4>Acquisitions</h4>
                        <p class="text-muted">Add new books to library</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Members -->
        <div class="col-md-6 col-lg-4">
            <a href="members.php" class="text-decoration-none">
                <div class="card dashboard-card h-100 text-center shadow">
                    <div class="card-body">
                        <i class="fas fa-users fa-4x text-info mb-3"></i>
                        <h4>Member Management</h4>
                        <p class="text-muted">Manage library users</p>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?>

    </div>

    <div class="text-center mt-5">
        <a href="logout.php" class="btn btn-outline-danger btn-lg">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
