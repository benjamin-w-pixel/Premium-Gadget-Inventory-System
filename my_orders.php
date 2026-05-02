<?php
session_start();
require_once 'Auth.php';
require_once 'Database.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$db = new Database();
$conn = $db->getConnection();

$query = "SELECT orders.*, items.name, items.price, items.image_url 
          FROM orders 
          JOIN items ON orders.item_id = items.id 
          WHERE orders.user_id = :user_id 
          ORDER BY orders.order_date DESC";

$stmt = $conn->prepare($query);
$stmt->execute(['user_id' => $userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Premium Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>My Purchases</h1>
            <a href="index.php" class="btn btn-secondary">Back to Shop</a>
        </div>

        <div class="card">
            <?php if(count($orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <img src="<?php echo htmlspecialchars($order['image_url']); ?>" style="width: 40px; height: 40px; border-radius: 4px;">
                                    <strong><?php echo htmlspecialchars($order['name']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></td>
                            <td>$<?php echo number_format($order['price'], 2); ?></td>
                            <td><span class="badge" style="background: #D1FAE5; color: #065F46;"><?php echo ucfirst($order['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>You haven't ordered anything yet.</h3>
                    <a href="index.php">Browse Gadgets</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
