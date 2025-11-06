<?php
// Test dedicated status update route
require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bakery_db_new', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TESTING DEDICATED STATUS UPDATE ROUTE ===\n";
    
    // First check current status
    $stmt = $pdo->prepare("SELECT id, status FROM bulk_orders WHERE id = 19");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current status: " . $order['status'] . "\n";
    
    // Test dedicated status update route
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.100.4:8080/admin/bulk-orders/19/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    
    // Simple status update data
    $postData = json_encode([
        'status' => 'ready'
    ]);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status Update Response Code: $httpCode\n";
    echo "Response: $response\n";
    
    // Check if status actually changed in database
    sleep(1); // Give it a moment
    $stmt->execute();
    $updatedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Status after update attempt: " . $updatedOrder['status'] . "\n";
    
    if ($order['status'] !== $updatedOrder['status']) {
        echo "✅ Status changed successfully from '{$order['status']}' to '{$updatedOrder['status']}'!\n";
    } else {
        echo "❌ Status did NOT change - still '{$updatedOrder['status']}'\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
