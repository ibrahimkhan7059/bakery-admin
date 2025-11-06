<?php
// Check current user_id for order 19
require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bakery_db_new', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CHECKING ORDER 19 USER_ID ===\n";
    
    $stmt = $pdo->prepare("SELECT id, order_number, customer_name, customer_email, user_id, status FROM bulk_orders WHERE id = 19");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "ID: " . $order['id'] . "\n";
        echo "Order Number: " . $order['order_number'] . "\n";
        echo "Customer: " . $order['customer_name'] . "\n";
        echo "Email: " . ($order['customer_email'] ?? 'NULL') . "\n";
        echo "User ID: " . $order['user_id'] . "\n";
        echo "Status: " . $order['status'] . "\n";
    } else {
        echo "Order 19 not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
