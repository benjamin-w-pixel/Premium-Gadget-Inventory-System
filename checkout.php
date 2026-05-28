<?php
// checkout.php
session_start();
require_once 'Auth.php';
require_once 'Inventory.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Block admins from making consumer purchases
if ($auth->isAdmin()) {
    echo "<script>alert('Administrators cannot make consumer purchases. Please use a regular customer account.'); window.location.href='index.php';</script>";
    exit();
}

$inventory = new Inventory();
$checkoutItems = [];
$totalPrice = 0.00;
$singleItemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($singleItemId > 0) {
    // Single Item Checkout
    $item = $inventory->getItemById($singleItemId);
    if (!$item || $item['quantity'] <= 0) {
        header("Location: index.php");
        exit();
    }
    $item['checkout_qty'] = 1;
    $item['subtotal'] = $item['price'];
    $totalPrice = $item['price'];
    $checkoutItems[] = $item;
} else {
    // Cart-wide Checkout
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        header("Location: cart.php");
        exit();
    }
    
    foreach ($_SESSION['cart'] as $id => $qty) {
        $item = $inventory->getItemById($id);
        if ($item) {
            $item['checkout_qty'] = $qty;
            $item['subtotal'] = $item['price'] * $qty;
            $totalPrice += $item['subtotal'];
            $checkoutItems[] = $item;
        }
    }
    
    if (empty($checkoutItems)) {
        header("Location: cart.php");
        exit();
    }
}

// Generate CSRF Token
$csrf_token = $auth->generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - Premium Gadgets</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-top: 20px;
        }
        @media (max-width: 900px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }
        .payment-card {
            background: var(--surface);
            backdrop-filter: blur(16px);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }
        .order-summary {
            background: var(--surface);
            backdrop-filter: blur(16px);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            height: fit-content;
        }
        .card-visual {
            background: linear-gradient(135deg, #1e293b, #334155);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .card-visual::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            transform: rotate(30deg);
        }
        .card-number {
            font-size: 1.4rem;
            letter-spacing: 4px;
            margin: 20px 0;
            font-family: monospace;
        }
        .card-footer {
            display: flex;
            justify-content: space-between;
            text-transform: uppercase;
            font-size: 0.8rem;
            opacity: 0.8;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-top: 10px;
        }
        .checkout-items-list {
            max-height: 240px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>Secure Checkout</h1>
            <a href="<?php echo ($singleItemId > 0) ? 'index.php' : 'cart.php'; ?>" class="btn btn-secondary">Cancel</a>
        </div>

        <div class="checkout-grid">
            <!-- Payment Form -->
            <div class="payment-card">
                <h3>Payment Information</h3>
                <br>
                <div class="card-visual">
                    <div style="font-size: 1.2rem; font-weight: 700;">PREMIUM CARD</div>
                    <div class="card-number">**** **** **** 4242</div>
                    <div class="card-footer">
                        <div>Card Holder<br><strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></div>
                        <div>Expires<br><strong>12/28</strong></div>
                    </div>
                </div>

                <form action="order.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <?php if ($singleItemId > 0): ?>
                        <input type="hidden" name="item_id" value="<?php echo $singleItemId; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Cardholder Name</label>
                        <input type="text" class="form-control" placeholder="Full Name as on card" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" class="form-control" placeholder="MM / YY" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="password" class="form-control" placeholder="***" maxlength="3" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px; font-size: 1.1rem; padding: 15px;">
                        Pay $<?php echo number_format($totalPrice, 2); ?> Now
                    </button>
                    <p style="text-align: center; font-size: 0.8rem; color: var(--text-secondary); margin-top: 15px;">
                        🔒 Your payment is secured with 256-bit encryption.
                    </p>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h3>Order Summary</h3>
                <br>
                
                <div class="checkout-items-list">
                    <?php foreach ($checkoutItems as $item): ?>
                        <div style="display: flex; gap: 15px; margin-bottom: 20px; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 15px;">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border);">
                            <div style="flex-grow: 1;">
                                <div style="font-weight: 700; font-size: 0.95rem;"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div style="font-size: 0.85rem; color: var(--text-secondary);"><?php echo $item['checkout_qty']; ?> × $<?php echo number_format($item['price'], 2); ?></div>
                            </div>
                            <div style="font-weight: 700;">$<?php echo number_format($item['subtotal'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-item">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($totalPrice, 2); ?></span>
                </div>
                <div class="summary-item">
                    <span>Shipping</span>
                    <span style="color: var(--secondary);">FREE</span>
                </div>
                <div class="summary-item">
                    <span>Tax (GST/VAT)</span>
                    <span>$0.00</span>
                </div>
                
                <div class="total-row">
                    <span>Total</span>
                    <span>$<?php echo number_format($totalPrice, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
