<?php
session_start();
require 'db.php';
$page_title = "Dashboard";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #343a40; }
        .sidebar .nav-link { color: white; }
        .sidebar .nav-link:hover { background: #495057; }
    </style>
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar col-md-3 col-lg-2 p-3">
        <h4 class="text-white text-center mb-4">Library System</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="catalog.php">Cataloging</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="circulation.php">Circulation</a>
            </li>
            <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="acquisitions.php">Acquisitions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="members.php">Members</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">Reports</a>
                </li>
            <?php endif; ?>
            <li class="nav-item mt-4">
                <a class="nav-link" href="profile.php">Change Password</a>
            </li>


<li class="nav-item">
    <a class="nav-link" href="my-borrowed.php"><i class="fas fa-book-reader me-1"></i> My Borrowed Books</a>
</li>
        </ul>
        <div class="mt-auto p-3">
            <p class="text-white">Logged in as: <?= htmlspecialchars($username) ?> (<?= $role ?>)</p>
            <a href="logout.php" class="btn btn-outline-light w-100">Logout</a>
        </div>
    </div>

    <!-- Main content -->
    <div class="col p-4">
        <h2>Welcome </h2>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<?php include 'footer.php'; ?>
