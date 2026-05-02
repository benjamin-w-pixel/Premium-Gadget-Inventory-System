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
                $image_url = $upload_path;
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
        }
    }

    if (empty($error)) {
        if (empty($name) || empty($category)) {
            $error = "Name and Category are required.";
        } else {
            if (empty($image_url)) $image_url = 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
            
            if ($inventory->addItem($name, $category, $quantity, $price, $image_url)) {
                $success = "Item added to inventory! <a href='admin_dashboard.php'>Back to Dashboard</a>";
            } else {
                $error = "Failed to add item to database.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Gadget - Premium Inventory</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="admin_dashboard.php" style="text-decoration:none; color: var(--primary);">&larr; Back to Admin Panel</a>
        <div class="auth-container" style="max-width: 600px; margin-top: 20px;">
            <h2>Add New Gadget</h2>
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
                    <input type="text" name="name" class="form-control" required placeholder="e.g. iPhone 15 Pro">
                </div>
                
                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px dashed #cbd5e1;">
                    <div class="form-group">
                        <label>Option A: Upload Image File</label>
                        <input type="file" name="image_file" class="form-control" accept="image/*">
                    </div>
                    <div style="text-align: center; color: #94a3b8; font-size: 0.8rem; margin: 5px 0;">— OR —</div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Option B: Image URL</label>
                        <input type="text" name="image_url" class="form-control" placeholder="https://image-link.com/photo.jpg">
                    </div>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control" required>
                        <option value="Laptops">Laptops</option>
                        <option value="Phones">Phones</option>
                        <option value="Audio">Audio</option>
                        <option value="Tablets">Tablets</option>
                        <option value="Wearables">Wearables</option>
                        <option value="Cameras">Cameras</option>
                        <option value="Drones">Drones</option>
                        <option value="E-Readers">E-Readers</option>
                    </select>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="0" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" class="form-control" min="0" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add to Inventory</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
