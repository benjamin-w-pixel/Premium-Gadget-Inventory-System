<?php
session_start();
require_once 'Auth.php';
require_once 'Inventory.php';

$auth = new Auth();
if (!$auth->isAdmin()) {
    die("Unauthorized access.");
}

$inventory = new Inventory();
$items = $inventory->getItems();

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_export_' . date('Y-m-d') . '.csv');

// Create file pointer
$output = fopen('php://output', 'w');

// Set column headers
fputcsv($output, ['ID', 'Name', 'Category', 'Quantity', 'Price ($)', 'Created At']);

// Add data rows
foreach ($items as $item) {
    fputcsv($output, [
        $item['id'],
        $item['name'],
        $item['category'],
        $item['quantity'],
        $item['price'],
        $item['created_at']
    ]);
}

fclose($output);
exit();
?>
