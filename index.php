<?php
session_start();
require_once 'Inventory.php';
require_once 'Auth.php';

$inventory = new Inventory();
$auth = new Auth();

// Search and Filter Params
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$items = $inventory->getItems($search, $category);
$categories = $inventory->getCategories();

$isLoggedIn = $auth->isLoggedIn();
$isAdmin = $auth->isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Gadget Inventory - Shop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .hero {
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1468436139062-f60a71c5c892?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            border-radius: 16px;
            margin-bottom: 40px;
        }
        .hero h1 { color: white; font-size: 3rem; margin-bottom: 10px; }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        .product-card {
            background: var(--surface);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover { transform: translateY(-5px); }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-info { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
        .product-name { font-size: 1.2rem; font-weight: 600; margin-bottom: 8px; }
        .product-price { color: var(--primary); font-size: 1.5rem; font-weight: 700; margin-bottom: 15px; }
        .nav-bar {
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 15px 30px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .nav-logo { font-size: 1.5rem; font-weight: 800; color: var(--primary); text-decoration: none; }
        .nav-links { display: flex; gap: 20px; align-items: center; }

        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            background: var(--surface);
            backdrop-filter: blur(16px);
            padding: 15px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .search-bar input { flex: 3; margin-bottom: 0; }
        .search-bar select { flex: 1; margin-bottom: 0; }
        .search-bar button { flex: 0.5; width: auto; margin-top: 0; }
    </style>
</head>
<body>
    <div class="container">
        <nav class="nav-bar">
            <a href="index.php" class="nav-logo">PREMIUM GADGETS</a>
            <div class="nav-links">
                <?php if($isAdmin): ?>
                    <a href="admin_dashboard.php" class="btn btn-secondary" style="padding: 8px 16px;">Admin Panel</a>
                <?php endif; ?>
                
                <?php if($isLoggedIn): ?>
                    <span>Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <?php if(!$isAdmin): ?>
                        <a href="my_orders.php" class="btn btn-secondary" style="padding: 8px 16px; width: auto;">My Orders</a>
                    <?php endif; ?>
                    <a href="logout.php" class="logout-link">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="logout-link">Login</a>
                    <a href="register.php" class="btn btn-primary" style="padding: 8px 16px; width: auto;">Sign Up</a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="hero">
            <h1>Unleash Next-Gen Power</h1>
            <p>Elevate your lifestyle with our exclusive, premium gadgets.</p>
        </div>

        <form class="search-bar" method="GET">
            <input type="text" name="search" class="form-control" placeholder="Search for gadgets..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo ($category == $cat) ? 'selected' : ''; ?>>
                        <?php echo $cat; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if (!empty($search) && !empty($items)): ?>
            <div style="margin-bottom: 20px;">
                <h2 style="margin-bottom: 5px;">Search Results</h2>
                <p style="color: #64748b; font-size: 0.95rem;">Showing the best matches for "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
            </div>
        <?php else: ?>
            <h2><?php echo empty($search) && empty($category) ? 'Latest Inventory' : 'Search Results'; ?></h2>
        <?php endif; ?>
        <div class="product-grid">
            <?php foreach($items as $item): ?>
            <div class="product-card">
                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="Gadget" class="product-image">
                <div class="product-info">
                    <span class="badge"><?php echo htmlspecialchars($item['category']); ?></span>
                    <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                    <?php if($isAdmin): ?>
                        <button class="btn btn-secondary" disabled title="Admins cannot place orders. Please use a customer account.">Admin Cannot Order</button>
                    <?php elseif($item['quantity'] > 0): ?>
                        <a href="checkout.php?id=<?php echo $item['id']; ?>" class="btn btn-primary">Order Now</a>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($items)): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <h3>No gadgets found.</h3>
                    <p>Try searching for something else or browse all categories.</p>
                    <br>
                    <a href="index.php" class="btn btn-secondary" style="width: auto;">View All Inventory</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
