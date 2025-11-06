<?php
// Test Laravel admin panel status update functionality
require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bakery_db_new', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TESTING LARAVEL STATUS UPDATE ===\n";
    
    // First check current status
    $stmt = $pdo->prepare("SELECT id, status FROM bulk_orders WHERE id = 19");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current status: " . $order['status'] . "\n";
    
    // Test Laravel route with POST request (simulate form submission)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.100.4:8080/admin/bulk-orders/19');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    ]);
    
    // Simulate form data
    $postData = http_build_query([
        '_token' => 'test', // This will fail but we can see the response
        '_method' => 'PUT',
        'customer_name' => 'ibrahim khan',
        'customer_phone' => '03001234567',
        'delivery_address' => 'Test Address',
        'delivery_date' => '2025-11-10',
        'order_type' => 'birthday',
        'payment_method' => 'cash',
        'status' => 'ready', // Change to ready
        'payment_status' => 'pending',
        'products' => [
            [
                'id' => 1,
                'quantity' => 5
            ]
        ]
    ]);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Laravel Update Response Code: $httpCode\n";
    
    if ($httpCode == 419) {
        echo "CSRF Token issue - normal for direct API call\n";
    } elseif ($httpCode == 200 || $httpCode == 302) {
        echo "Update request processed\n";
    }
    
    // Check if status actually changed in database
    $stmt->execute();
    $updatedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Status after Laravel update attempt: " . $updatedOrder['status'] . "\n";
    
    if ($order['status'] !== $updatedOrder['status']) {
        echo "✅ Status changed successfully!\n";
    } else {
        echo "❌ Status did NOT change - Laravel update is not working!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
