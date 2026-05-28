<?php
// cart.php
session_start();
require_once 'Inventory.php';
require_once 'Auth.php';

$inventory = new Inventory();
$auth = new Auth();

$isLoggedIn = $auth->isLoggedIn();
$isAdmin = $auth->isAdmin();

// Check if cart exists and is not empty
$cartItems = [];
$totalPrice = 0.00;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        $item = $inventory->getItemById($id);
        if ($item) {
            $item['cart_qty'] = $qty;
            $item['subtotal'] = $item['price'] * $qty;
            $totalPrice += $item['subtotal'];
            $cartItems[] = $item;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Premium Gadgets</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .cart-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            margin-top: 20px;
        }
        @media (max-width: 900px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }
        }
        .qty-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .qty-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border);
            color: var(--text-primary);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .qty-btn:hover {
            background: var(--primary);
            border-color: var(--primary-hover);
        }
        .qty-val {
            font-weight: 600;
            width: 30px;
            text-align: center;
        }
        .summary-card {
            background: var(--surface);
            backdrop-filter: blur(16px);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            height: fit-content;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-top: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation Header -->
        <nav class="nav-bar">
            <a href="index.php" class="nav-logo">PREMIUM GADGETS</a>
            <div class="nav-links">
                <a href="index.php" class="btn-edit" style="text-decoration:none;">Shop Home</a>
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

        <div class="dashboard-header" style="margin-top: 20px;">
            <div>
                <h1>Your Premium Shopping Cart</h1>
                <p>Review your selected gadgets before checking out</p>
            </div>
            <?php if(!empty($cartItems)): ?>
                <a href="cart_action.php?action=clear" class="btn-danger" style="text-decoration:none; border-radius:10px; padding: 12px 20px;" onclick="return confirm('Clear your entire cart?');">Clear Cart</a>
            <?php endif; ?>
        </div>

        <?php if(isset($_GET['msg'])): ?>
            <?php if($_GET['msg'] == 'added'): ?>
                <div class="alert alert-success">Item added to your cart!</div>
            <?php elseif($_GET['msg'] == 'removed'): ?>
                <div class="alert alert-danger">Item removed from cart.</div>
            <?php elseif($_GET['msg'] == 'updated'): ?>
                <div class="alert alert-success">Cart quantities updated.</div>
            <?php elseif($_GET['msg'] == 'cleared'): ?>
                <div class="alert alert-danger">All items removed from your cart.</div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($cartItems)): ?>
            <div class="cart-grid">
                <!-- Cart Items Table -->
                <div class="card" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="product" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border);">
                                            <div>
                                                <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                                <span class="badge" style="margin-top: 5px; display: inline-block;"><?php echo htmlspecialchars($item['category']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>
                                        <div class="qty-controls">
                                            <!-- Decrease Qty -->
                                            <a href="cart_action.php?action=update&id=<?php echo $item['id']; ?>&qty=<?php echo $item['cart_qty'] - 1; ?>" class="qty-btn" style="text-decoration: none;">-</a>
                                            <span class="qty-val"><?php echo $item['cart_qty']; ?></span>
                                            <!-- Increase Qty -->
                                            <a href="cart_action.php?action=update&id=<?php echo $item['id']; ?>&qty=<?php echo $item['cart_qty'] + 1; ?>" class="qty-btn" style="text-decoration: none;">+</a>
                                        </div>
                                    </td>
                                    <td><strong>$<?php echo number_format($item['subtotal'], 2); ?></strong></td>
                                    <td>
                                        <a href="cart_action.php?action=remove&id=<?php echo $item['id']; ?>" class="btn-danger" style="text-decoration:none; padding: 6px 12px; border-radius: 8px; font-size: 0.85rem;" onclick="return confirm('Remove this product?');">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Panel -->
                <div class="summary-card">
                    <h3>Order Summary</h3>
                    <br>
                    <div class="summary-row">
                        <span>Items Subtotal</span>
                        <span>$<?php echo number_format($totalPrice, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span style="color: var(--secondary);">FREE</span>
                    </div>
                    <div class="summary-row">
                        <span>Estimated Tax</span>
                        <span>$0.00</span>
                    </div>
                    <div class="summary-total">
                        <span>Grand Total</span>
                        <span>$<?php echo number_format($totalPrice, 2); ?></span>
                    </div>

                    <?php if ($isAdmin): ?>
                        <button class="btn btn-primary" disabled style="opacity: 0.6; cursor: not-allowed;">Admin Cannot Checkout</button>
                    <?php elseif ($isLoggedIn): ?>
                        <a href="checkout.php" class="btn btn-primary" style="display: block; text-decoration: none; text-align: center;">Proceed to Checkout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary" style="display: block; text-decoration: none; text-align: center;">Sign In to Checkout</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card empty-state">
                <div style="font-size: 4rem; margin-bottom: 20px;">🛒</div>
                <h3>Your shopping cart is empty!</h3>
                <p style="margin-top: 10px; color: var(--text-secondary);">Browse our catalog and choose premium tech gadgets to build your setup.</p>
                <br>
                <a href="index.php" class="btn btn-primary" style="width: auto; padding: 12px 30px; text-decoration:none;">Go Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
