<?php
require_once 'Database.php';

class Auth {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($username, $password) {
        // Basic validation
        if (empty($username) || empty($password)) {
            return "Username and password are required.";
        }

        // Check if username exists
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return "Username already taken.";
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);

        if ($stmt->execute()) {
            return true;
        } else {
            return "Registration failed. Please try again.";
        }
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                if(session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                // Generate CSRF token on login
                $this->generateCSRFToken();
                return true;
            } else {
                return "Incorrect password.";
            }
        } else {
            return "User not found.";
        }
    }

    public function isAdmin() {
        if(session_status() === PHP_SESSION_NONE) session_start();
        return (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
    }

    public function isLoggedIn() {
        if(session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }

    // CSRF Protection Methods
    public function generateCSRFToken() {
        if(session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function checkCSRFToken($token) {
        if(session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
?>
