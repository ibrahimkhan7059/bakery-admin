<?php
// Direct database check for order 19 status
require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bakery_db_new', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CHECKING ORDER 19 STATUS ===\n";
    
    // Check directly from database
    $stmt = $pdo->prepare("SELECT id, order_number, customer_name, status FROM bulk_orders WHERE id = 19");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "Database Result:\n";
        echo "ID: " . $order['id'] . "\n";
        echo "Order Number: " . $order['order_number'] . "\n";
        echo "Customer: " . $order['customer_name'] . "\n";
        echo "Status: '" . $order['status'] . "' (Type: " . gettype($order['status']) . ")\n";
        echo "Status Length: " . strlen($order['status']) . "\n";
        echo "Status Bytes: ";
        for ($i = 0; $i < strlen($order['status']); $i++) {
            echo ord($order['status'][$i]) . " ";
        }
        echo "\n\n";
    } else {
        echo "Order 19 not found in database\n";
    }
    
    // Test API endpoint
    echo "=== TESTING API ENDPOINT ===\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.100.4:8080/api/v1/bulk-orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data) {
            foreach ($data as $order) {
                if ($order['id'] == 19) {
                    echo "API Result for Order 19:\n";
                    echo "ID: " . $order['id'] . "\n";
                    echo "Status: '" . $order['status'] . "' (Type: " . gettype($order['status']) . ")\n";
                    echo "Status Length: " . strlen($order['status']) . "\n";
                    break;
                }
            }
        }
    } else {
        echo "API Error: $response\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
