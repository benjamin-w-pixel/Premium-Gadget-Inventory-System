<?php
// order.php
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
    // Redirect if no direct checkout action is provided
    if (!isset($_GET['id']) && (!isset($_SESSION['cart']) || empty($_SESSION['cart']))) {
        header("Location: index.php");
        exit();
    }
}

$singleItemId = isset($_POST['item_id']) ? (int)$_POST['item_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$userId = $_SESSION['user_id'];
$inventory = new Inventory();

$checkoutItems = [];
$error = '';
$success = '';

if ($singleItemId > 0) {
    // Single Item Checkout Processing
    $item = $inventory->getItemById($singleItemId);
    if ($item) {
        $checkoutItems[] = [
            'id' => $item['id'],
            'name' => $item['name'],
            'qty' => 1,
            'price' => $item['price']
        ];
    }
} else {
    // Cart-wide Checkout Processing
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $qty) {
            $item = $inventory->getItemById($id);
            if ($item) {
                $checkoutItems[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'qty' => $qty,
                    'price' => $item['price']
                ];
            }
        }
    }
}

if (!empty($checkoutItems)) {
    $db = new Database();
    $conn = $db->getConnection();
    
    try {
        $conn->beginTransaction();
        
        // Statements prepared once for maximum performance
        $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, item_id, status) VALUES (:user_id, :item_id, 'completed')");
        $stmtStock = $conn->prepare("UPDATE items SET quantity = quantity - 1 WHERE id = :id");
        
        foreach ($checkoutItems as $ci) {
            // Re-fetch item inside the transaction to verify current fresh stock
            $freshItem = $inventory->getItemById($ci['id']);
            if (!$freshItem || $freshItem['quantity'] < $ci['qty']) {
                throw new Exception("Product '" . $ci['name'] . "' has insufficient stock (Only " . ($freshItem['quantity'] ?? 0) . " left).");
            }
            
            // Execute order insertion and stock reduction for each unit ordered
            for ($i = 0; $i < $ci['qty']; $i++) {
                $stmtOrder->execute(['user_id' => $userId, 'item_id' => $ci['id']]);
                $stmtStock->execute(['id' => $ci['id']]);
            }
        }
        
        $conn->commit();
        $success = "Purchase successful!";
        
        // If it was a cart checkout, empty the cart
        if ($singleItemId == 0) {
            $_SESSION['cart'] = [];
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Order failed: " . $e->getMessage();
    }
} else {
    $error = "No items selected for checkout.";
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
    <div class="auth-container" style="max-width: 500px;">
        <?php if($success): ?>
            <div style="text-align: center;">
                <div style="font-size: 50px; margin-bottom: 20px;">🎉</div>
                <h2><?php echo $success; ?></h2>
                <p style="margin-top: 10px; color: var(--text-secondary);">Your payment has been processed successfully.</p>
                
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin: 20px 0; text-align: left;">
                    <h4 style="margin-bottom: 10px; color: var(--primary);">Purchased Items:</h4>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach($checkoutItems as $ci): ?>
                            <li style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem;">
                                <span><strong><?php echo $ci['qty']; ?> ×</strong> <?php echo htmlspecialchars($ci['name']); ?></span>
                                <span>$<?php echo number_format($ci['price'] * $ci['qty'], 2); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                <a href="my_orders.php" class="link-text" style="display:block; margin-top: 15px;">View My Orders</a>
            </div>
        <?php else: ?>
            <div style="text-align: center;">
                <div class="alert alert-danger" style="margin-bottom: 25px;"><?php echo $error; ?></div>
                <a href="<?php echo ($singleItemId > 0) ? 'index.php' : 'cart.php'; ?>" class="btn btn-secondary">Return and Try Again</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
