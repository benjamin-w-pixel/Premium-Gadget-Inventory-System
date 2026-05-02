<?php
session_start();
require_once 'Auth.php';

$auth = new Auth();
if ($auth->isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $login_result = $auth->login($username, $password);
    
    if ($login_result === true) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = $login_result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Premium Gadget Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <a href="index.php" style="text-decoration: none; color: var(--text-secondary);">&larr; Back to Shop</a>
        <h2 style="margin-top: 20px;">Welcome Back</h2>
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group" style="position: relative;">
                <label>Password</label>
                <input type="password" name="password" id="login-password" class="form-control" style="padding-right: 40px;" required>
                <span onclick="togglePassword('login-password', 'eye-icon')" style="position: absolute; right: 15px; top: 38px; cursor: pointer; color: #64748b; font-size: 1.2rem;" id="eye-icon">👁️</span>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
            <div class="link-text">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </form>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.textContent = "🙈";
            } else {
                input.type = "password";
                icon.textContent = "👁️";
            }
        }
    </script>
</body>
</html>
