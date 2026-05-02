<?php
session_start();
require_once 'Auth.php';
require_once 'Inventory.php';

$auth = new Auth();
if (!$auth->isAdmin()) {
    header("Location: login.php");
    exit();
}

$inventory = new Inventory();
$error = '';
$success = '';
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$item = $inventory->getItemById($itemId);
if (!$item) {
    header("Location: admin_dashboard.php");
    exit();
}

// Generate CSRF Token
$csrf_token = $auth->generateCSRFToken();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check CSRF Token
    if (!$auth->checkCSRFToken($_POST['csrf_token'] ?? '')) {
        die("CSRF token validation failed.");
    }

    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $image_url = trim($_POST['image_url']);

    // Handle File Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_path)) {
                // Delete old file if it was an uploaded file
                if (strpos($item['image_url'], 'uploads/') === 0 && file_exists($item['image_url'])) {
                    unlink($item['image_url']);
                }
                $image_url = $upload_path;
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        }
    }

    if (empty($error)) {
        if ($inventory->updateItem($itemId, $name, $category, $quantity, $price, $image_url)) {
            $success = "Inventory updated! <a href='admin_dashboard.php'>Back to Dashboard</a>";
            $item = $inventory->getItemById($itemId);
        } else {
            $error = "Update failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gadget - Premium Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" style="text-decoration:none; color: var(--primary);">&larr; Back to Admin Panel</a>
        <div class="auth-container" style="max-width: 600px; margin-top: 20px;">
            <h2>Edit Gadget</h2>
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($item['name']); ?>">
                </div>

                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px dashed #cbd5e1;">
                    <div style="margin-bottom: 10px;">
                        <label>Current Preview:</label><br>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; margin-top: 5px;">
                    </div>
                    <div class="form-group">
                        <label>Replace with New Upload</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*">
                    </div>
                    <div style="text-align: center; color: #94a3b8; font-size: 0.8rem; margin: 5px 0;">— OR —</div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Change Image URL</label>
                        <input type="text" name="image_url" class="form-control" value="<?php echo htmlspecialchars($item['image_url']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control" required>
                        <option value="Laptops" <?php if($item['category'] == 'Laptops') echo 'selected'; ?>>Laptops</option>
                        <option value="Phones" <?php if($item['category'] == 'Phones') echo 'selected'; ?>>Phones</option>
                        <option value="Audio" <?php if($item['category'] == 'Audio') echo 'selected'; ?>>Audio</option>
                        <option value="Tablets" <?php if($item['category'] == 'Tablets') echo 'selected'; ?>>Tablets</option>
                        <option value="Wearables" <?php if($item['category'] == 'Wearables') echo 'selected'; ?>>Wearables</option>
                        <option value="Cameras" <?php if($item['category'] == 'Cameras') echo 'selected'; ?>>Cameras</option>
                        <option value="Drones" <?php if($item['category'] == 'Drones') echo 'selected'; ?>>Drones</option>
                        <option value="E-Readers" <?php if($item['category'] == 'E-Readers') echo 'selected'; ?>>E-Readers</option>
                    </select>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="0" required value="<?php echo (int)$item['quantity']; ?>">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" min="0" required value="<?php echo (float)$item['price']; ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Item</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
