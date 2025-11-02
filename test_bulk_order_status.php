<?php

// Test bulk order status update
echo "🧪 TESTING BULK ORDER STATUS UPDATE\n";
echo "===================================\n";

// Database connection
$host = '127.0.0.1';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get any bulk order to test
    $stmt = $pdo->query("SELECT * FROM bulk_orders ORDER BY created_at DESC LIMIT 1");
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "📦 Found Bulk Order:\n";
        echo "ID: {$order['id']}\n";
        echo "Status: {$order['status']}\n";
        echo "Customer: {$order['customer_name']}\n";
        echo "User ID: {$order['user_id']}\n";
        echo "Current Delivery Date: {$order['delivery_date']}\n\n";
        
        // Test status update via API
        $testUrl = "http://192.168.100.4:8080/api/v1/bulk-orders/{$order['id']}/status";
        
        $data = json_encode([
            'status' => 'processing'
        ]);
        
        echo "🔄 Testing status update to 'processing'...\n";
        echo "URL: $testUrl\n";
        echo "Data: $data\n\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "📬 Response Code: $httpCode\n";
        echo "📬 Response: $response\n\n";
        
        if ($httpCode == 200) {
            echo "✅ Status update successful!\n";
            
            // Check if notification was sent
            echo "🔔 Checking if notification was sent...\n";
            
            // Check updated order
            $stmt = $pdo->prepare("SELECT * FROM bulk_orders WHERE id = ?");
            $stmt->execute([$order['id']]);
            $updatedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "📦 Updated Status: {$updatedOrder['status']}\n";
            echo "📦 Delivery Date: {$updatedOrder['delivery_date']}\n";
            echo "📦 Total Amount: {$updatedOrder['total_amount']}\n";
            
        } else {
            echo "❌ Status update failed\n";
            echo "Check Laravel logs for errors\n";
        }
        
    } else {
        echo "❌ No bulk orders found in database\n";
        echo "Create a bulk order first to test status updates\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Test completed!\n";
?>
