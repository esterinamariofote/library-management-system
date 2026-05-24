<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Library System' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { padding-top: 70px; }
        .navbar-brand { font-weight: bold; }
        .nav-link { padding: 10px 15px !important; }
        @media (max-width: 768px) {
            .table td, .table th { font-size: 0.9rem; padding: 8px; }
            .btn { margin: 3px 0; font-size: 0.9rem; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-book"></i> Library
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="catalog.php"><i class="fas fa-book"></i> Catalog</a></li>
                <li class="nav-item"><a class="nav-link" href="circulation.php"><i class="fas fa-exchange-alt"></i> Circulation</a></li>
                <li class="nav-item"><a class="nav-link" href="mybooks.php"><i class="fas fa-hand-holding"></i> My Books</a></li>
                <?php if ($role === 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="acquisitions.php"><i class="fas fa-cart-plus"></i> Acquisitions</a></li>
                <li class="nav-item"><a class="nav-link" href="members.php"><i class="fas fa-users"></i> Members</a></li>
                <li class="nav-item"><a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
