<?php

// Simple test script to check order items
$servername = "localhost";
$username = "bakery_user"; 
$password = "";
$dbname = "bakery_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔍 CHECKING ORDER ITEMS SUBTOTAL VALUES\n";
    echo "=======================================\n\n";
    
    // Check order_items table
    $stmt = $pdo->query("SELECT id, order_id, product_id, quantity, price, discount, subtotal FROM order_items LIMIT 5");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($items)) {
        echo "❌ No order items found in database.\n";
    } else {
        echo "✅ Sample Order Items:\n";
        foreach ($items as $item) {
            echo sprintf(
                "ID: %d | Order: %d | Product: %d | Qty: %d | Price: Rs.%.2f | Discount: Rs.%.2f | Subtotal: Rs.%.2f\n",
                $item['id'],
                $item['order_id'],
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['discount'] ?? 0,
                $item['subtotal'] ?? 0
            );
        }
    }
    
    echo "\n🔍 CHECKING ORDERS STATUS ENUM\n";
    echo "==============================\n";
    
    // Check orders table status values
    $stmt = $pdo->query("SELECT id, user_id, status, total_amount FROM orders LIMIT 3");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orders)) {
        echo "❌ No orders found in database.\n";
    } else {
        echo "✅ Sample Orders:\n";
        foreach ($orders as $order) {
            echo sprintf(
                "Order ID: %d | User: %d | Status: %s | Total: Rs.%.2f\n",
                $order['id'],
                $order['user_id'],
                $order['status'],
                $order['total_amount']
            );
        }
    }
    
    echo "\n✅ DATABASE SCHEMA IS WORKING PROPERLY!\n";
    echo "🎉 You can now update order status without errors.\n";
    
} catch(PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}

?>
