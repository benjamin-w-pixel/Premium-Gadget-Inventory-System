<?php
// cart_action.php
session_start();
require_once 'Inventory.php';

$action = $_GET['action'] ?? '';
$itemId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$inventory = new Inventory();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        if ($itemId > 0) {
            $item = $inventory->getItemById($itemId);
            if ($item && $item['quantity'] > 0) {
                // If item already in cart, check stock limits
                $currentQty = $_SESSION['cart'][$itemId] ?? 0;
                if ($currentQty < $item['quantity']) {
                    $_SESSION['cart'][$itemId] = $currentQty + 1;
                    header("Location: cart.php?msg=added");
                } else {
                    header("Location: index.php?msg=outofstock");
                }
            } else {
                header("Location: index.php?msg=invalid");
            }
        } else {
            header("Location: index.php");
        }
        exit();

    case 'remove':
        if ($itemId > 0 && isset($_SESSION['cart'][$itemId])) {
            unset($_SESSION['cart'][$itemId]);
        }
        header("Location: cart.php?msg=removed");
        exit();

    case 'update':
        $qty = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
        if ($itemId > 0 && isset($_SESSION['cart'][$itemId])) {
            $item = $inventory->getItemById($itemId);
            if ($item) {
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$itemId]);
                } elseif ($qty <= $item['quantity']) {
                    $_SESSION['cart'][$itemId] = $qty;
                } else {
                    $_SESSION['cart'][$itemId] = $item['quantity']; // Cap at maximum stock
                }
            }
        }
        header("Location: cart.php?msg=updated");
        exit();

    case 'clear':
        $_SESSION['cart'] = [];
        header("Location: cart.php?msg=cleared");
        exit();

    default:
        header("Location: index.php");
        exit();
}
?>
