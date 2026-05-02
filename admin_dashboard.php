<?php
session_start();
require_once 'Database.php';
require_once 'Auth.php';
require_once 'Inventory.php';

$db = new Database();
$conn = $db->getConnection();

$auth = new Auth();
if (!$auth->isAdmin()) {
    header("Location: login.php");
    exit();
}

$inventory = new Inventory();

// Handle Delete
if (isset($_GET['delete'])) {
    $inventory->deleteItem((int)$_GET['delete']);
    header("Location: admin_dashboard.php?msg=deleted");
    exit();
}

// Search and Filter Params
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$items = $inventory->getItems($search, $category);
$categories = $inventory->getCategories();

// Basic Stats for Report
$total_value = 0;
foreach($items as $item) {
    $total_value += ($item['price'] * $item['quantity']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Premium Inventory</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-section {
            background: var(--surface);
            backdrop-filter: blur(16px);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            display: flex;
            gap: 15px;
            align-items: flex-end;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: var(--surface);
            backdrop-filter: blur(16px);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            text-align: center;
        }
        .stat-value { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
        .low-stock { color: #ef4444; font-weight: 700; background: #fee2e2; padding: 2px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <div>
                <h1>Admin Panel</h1>
                <p>Manage Your Premium Inventory</p>
            </div>
            <div class="header-actions">
                <a href="export.php" class="btn btn-primary" style="background: #10b981; border:none;">Download CSV</a>
                <a href="index.php" class="btn-edit" style="text-decoration:none;">View Shop</a>
                <a href="add_item.php" class="btn btn-secondary">+ Add New Gadget</a>
                <a href="logout.php" class="logout-link">Logout</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Inventory Value</div>
                <div class="stat-value">$<?php echo number_format($total_value, 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Gadgets Listed</div>
                <div class="stat-value"><?php echo count($items); ?></div>
            </div>
        </div>

        <form class="filter-section" method="GET">
            <div class="form-group" style="flex: 2; margin-bottom: 0;">
                <label>Search Gadgets</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label>Category</label>
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($category == $cat) ? 'selected' : ''; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: auto; padding: 12px 25px;">Filter</button>
            <a href="admin_dashboard.php" class="btn btn-secondary" style="width: auto; padding: 12px 25px; text-decoration:none;">Reset</a>
        </form>

        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Item removed from inventory.</div>
        <?php endif; ?>

        <div class="card">
            <h3>Recent Sales</h3>
            <br>
            <?php
            $stmt = $conn->prepare("SELECT orders.*, items.name as item_name, users.username 
                                    FROM orders 
                                    JOIN items ON orders.item_id = items.id 
                                    JOIN users ON orders.user_id = users.id 
                                    ORDER BY orders.order_date DESC LIMIT 5");
            $stmt->execute();
            $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if(count($all_orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Gadget</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($all_orders as $o): ?>
                        <tr>
                            <td>#<?php echo $o['id']; ?></td>
                            <td><strong>@<?php echo htmlspecialchars($o['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($o['item_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($o['order_date'])); ?></td>
                            <td><span class="badge"><?php echo $o['status']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No sales yet.</p>
            <?php endif; ?>
        </div>

        <br>

        <div class="card">
            <h3>Current Inventory Items</h3>
            <?php if(count($items) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Gadget Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($item['image_url']); ?>" style="width: 50px; height: 50px; border-radius: 4px; object-fit: cover;"></td>
                            <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                            <td><span class="badge"><?php echo htmlspecialchars($item['category']); ?></span></td>
                            <td>
                                <?php if($item['quantity'] < 5): ?>
                                    <span class="low-stock"><?php echo (int)$item['quantity']; ?> pcs (Low)</span>
                                <?php else: ?>
                                    <?php echo (int)$item['quantity']; ?> pcs
                                <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn-edit">Edit</a>
                                    <a href="admin_dashboard.php?delete=<?php echo $item['id']; ?>" class="btn-danger" style="text-decoration:none; border-radius:6px;" onclick="return confirm('Remove this gadget from inventory?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No items match your search.</h3>
                    <p>Try different keywords or categories.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
