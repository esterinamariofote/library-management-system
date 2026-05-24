<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// For admin-only pages
if (isset($require_admin) && $require_admin === true && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}
?>
