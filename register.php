<?php
// register.php
session_start();
require_once 'Auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Username Validation
    if (strlen($username) <= 4) {
        $error = "Username must be more than 4 characters long.";
    } elseif (strpos($username, '@') !== false) {
        $error = "Username cannot contain an '@' symbol.";
    }
    // Password Validation
    elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
        $suggestion = 'G@dget' . bin2hex(random_bytes(2)) . '!';
        $error = "Password is too weak. It must be at least 8 characters long and include a mix of uppercase, lowercase, numbers, and symbols.<br><br>💡 <strong>Suggestion:</strong> <span style='background:#e2e8f0; padding:4px 8px; border-radius:4px; user-select:all;'>$suggestion</span>";
    }
    // Confirm Password
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $register_result = $auth->register($username, $password);
        if ($register_result === true) {
            $success = "Account created! You can now <a href='login.php'>Login here</a> to start browsing gadgets.";
        } else {
            $error = $register_result; // E.g., Username already taken
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Premium Gadget</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <h2>Create an Account</h2>
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="e.g. johndoe" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group" style="position: relative;">
                <label>Password</label>
                <input type="password" name="password" id="reg-password" class="form-control" style="padding-right: 40px;" required>
                <span onclick="togglePassword('reg-password', 'eye-icon-1')" style="position: absolute; right: 15px; top: 38px; cursor: pointer; color: #64748b; font-size: 1.2rem;" id="eye-icon-1">👁️</span>
            </div>
            <div class="form-group" style="position: relative;">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="reg-confirm" class="form-control" style="padding-right: 40px;" required>
                <span onclick="togglePassword('reg-confirm', 'eye-icon-2')" style="position: absolute; right: 15px; top: 38px; cursor: pointer; color: #64748b; font-size: 1.2rem;" id="eye-icon-2">👁️</span>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
            <div class="link-text">
                Already have an account? <a href="index.php">Login here</a>
            </div>
        </form>
        <?php endif; ?>
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
