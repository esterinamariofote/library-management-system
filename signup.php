<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hashed]);
            $_SESSION['success'] = "Account created successfully! Please login.";
            header("Location: login.php");
            exit;
        } catch (Exception $e) {
            $error = "Username or email already exists.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
        }
        .signup-card { 
            max-width: 420px; 
            margin: 8% auto; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.2); 
        }
    </style>
</head>
<body>
<div class="container">
    <div class="signup-card card">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fas fa-book fa-4x text-primary mb-3"></i>
                <h2 class="fw-bold">Create New Account</h2>
                <p class="text-muted">Join our Library Management System</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control form-control-lg" required>
                </div>
                <div class="mb-3">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control form-control-lg" required>
                </div>
                <div class="mb-4">
                    <label>Password (min 6 characters)</label>
                    <input type="password" name="password" class="form-control form-control-lg" required>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100">Create Account</button>
            </form>

            <p class="text-center mt-4">
                Already have an account? <a href="login.php" class="text-decoration-none">Login here</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
