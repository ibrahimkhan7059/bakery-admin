<?php

// Test order status update with notification
echo "📦 TESTING ORDER STATUS UPDATE WITH NOTIFICATION\n";
echo "===============================================\n";

// Database connection
$host = '127.0.0.1';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get a bulk order from user 15
    $stmt = $pdo->prepare("
        SELECT id, user_id, status, order_number 
        FROM bulk_orders 
        WHERE user_id = 15 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "📋 Found Order:\n";
        echo "  ID: {$order['id']}\n";
        echo "  Number: {$order['order_number']}\n";
        echo "  User ID: {$order['user_id']}\n";
        echo "  Current Status: {$order['status']}\n\n";
        
        // Test status update via API
        $orderId = $order['id'];
        $newStatus = ($order['status'] == 'pending') ? 'confirmed' : 'processing';
        
        echo "🔄 Updating status to: $newStatus\n";
        
        $testUrl = "http://10.110.10.28:8080/v1/bulk-orders/$orderId/status";
        
        $data = http_build_query([
            'status' => $newStatus,
            'total_price' => 1500,
            'delivery_date' => date('Y-m-d', strtotime('+3 days'))
        ]);
        
        echo "📤 Sending status update...\n";
        echo "URL: $testUrl\n";
        echo "Data: $data\n\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "📬 Response Code: $httpCode\n";
        echo "📬 Response: $response\n\n";
        
        if ($httpCode == 200) {
            echo "✅ Order status updated successfully!\n";
            echo "🔔 Notification should be sent to user!\n";
        } else {
            echo "❌ Failed to update order status\n";
        }
        
    } else {
        echo "❌ No bulk order found for user 15\n";
        echo "💡 Create a bulk order first or test with different user\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Test completed!\n";
echo "📱 Check Flutter app for notification!\n";
?>
