<?php
session_start();
require_once 'Auth.php';
require_once 'Inventory.php';
require_once 'Database.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// CSRF Check
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$auth->checkCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }
} else {
    // If accessed via GET without ID, redirect
    if (!isset($_GET['id'])) {
        header("Location: index.php");
        exit();
    }
}

$itemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$userId = $_SESSION['user_id'];

$inventory = new Inventory();
$item = $inventory->getItemById($itemId);

$error = '';
$success = '';

if ($item && $item['quantity'] > 0) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $conn->beginTransaction();

        // 1. Create the Order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, item_id, status) VALUES (:user_id, :item_id, 'completed')");
        $stmt->execute(['user_id' => $userId, 'item_id' => $itemId]);

        // 2. Reduce Stock
        $stmt = $conn->prepare("UPDATE items SET quantity = quantity - 1 WHERE id = :id");
        $stmt->execute(['id' => $itemId]);

        $conn->commit();
        $success = "Purchase successful!";
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Order failed: " . $e->getMessage();
    }
} else {
    $error = "Item is out of stock!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status - Premium Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <?php if($success): ?>
            <div style="text-align: center;">
                <div style="font-size: 50px; margin-bottom: 20px;">🎉</div>
                <h2><?php echo $success; ?></h2>
                <p>Your order for <strong><?php echo htmlspecialchars($item['name']); ?></strong> has been processed successfully.</p>
                <br>
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                <a href="my_orders.php" class="link-text" style="display:block; margin-top: 15px;">View My Orders</a>
            </div>
        <?php else: ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <a href="index.php" class="btn btn-secondary">Back to Shop</a>
        <?php endif; ?>
    </div>
</body>
</html>
