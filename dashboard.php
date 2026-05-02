<?php
// dashboard.php
session_start();
require_once 'Auth.php';
require_once 'Inventory.php';

$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$inventory = new Inventory();
$userId = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $itemId = $_GET['delete'];
    $inventory->deleteItem($itemId, $userId);
    header("Location: dashboard.php?msg=deleted");
    exit();
}

$items = $inventory->getItems($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Premium Gadget</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <div class="header-actions">
                <a href="index.php" class="btn btn-secondary">Back to Shop</a>
                <a href="my_orders.php" class="btn btn-primary" style="background: var(--primary); color: white; border:none; text-decoration:none; padding: 12px 20px; border-radius: 8px;">My Orders</a>
                <a href="add_item.php" class="btn btn-secondary">+ Add New Item</a>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Item deleted successfully!</div>
        <?php endif; ?>

        <div class="form-group" style="margin-bottom: 20px;">
            <input type="text" id="dashboardSearch" class="form-control" placeholder="Quick search inventory..." onkeyup="filterTable()">
        </div>

        <div class="card">
            <?php if(count($items) > 0): ?>
                <table id="inventoryTable">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Price ($)</th>
                            <th>Added On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td class="item-name"><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td class="item-cat"><span class="badge"><?php echo htmlspecialchars($item['category']); ?></span></td>
                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="dashboard.php?delete=<?php echo $item['id']; ?>" class="btn-danger" style="text-decoration:none; border-radius:6px;" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No items found</h3>
                    <p style="margin-top: 10px;">Click the "Add New Item" button to start building your inventory.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function filterTable() {
        let input = document.getElementById('dashboardSearch');
        let filter = input.value.toLowerCase();
        let table = document.getElementById('inventoryTable');
        let tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let name = tr[i].querySelector('.item-name').innerText.toLowerCase();
            let cat = tr[i].querySelector('.item-cat').innerText.toLowerCase();
            if (name.indexOf(filter) > -1 || cat.indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
    </script>
</body>
</html>
