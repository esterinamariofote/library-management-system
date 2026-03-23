<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System - <?= htmlspecialchars($page_title ?? 'Dashboard') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <img src="assets/logo.png" alt="Library Logo" width="120" height="40" class="me-2">
            Library System
        </a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-user me-1"></i> 
                    <?= htmlspecialchars($_SESSION['username']) ?> (<?= $_SESSION['role'] ?>)
                </span>
                <a href="logout.php" class="nav-link btn btn-outline-light ms-3">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<div class="container mt-4">
