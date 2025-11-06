<?php

echo "=== Testing Bulk Order API Status Update ===\n";

// Get the first bulk order
$testUrl = "http://192.168.100.4:8080/api/v1/bulk-orders";

echo "🔍 Fetching bulk orders from API...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_error($ch)) {
    echo "❌ CURL Error: " . curl_error($ch) . "\n";
    curl_close($ch);
    exit(1);
}

curl_close($ch);

echo "HTTP Code: $httpCode\n";

if ($httpCode === 200) {
    $orders = json_decode($response, true);
    if (!empty($orders) && isset($orders[0])) {
        $order = $orders[0];
        $orderId = $order['id'];
        $currentStatus = $order['status'];
        
        echo "✅ Found order to test:\n";
        echo "Order ID: $orderId\n";
        echo "Current Status: $currentStatus\n\n";
        
        // Test status update
        $newStatus = $currentStatus === 'pending' ? 'ready' : 'pending';
        echo "🔄 Testing status update from '$currentStatus' to '$newStatus'...\n";
        
        $updateUrl = "http://192.168.100.4:8080/api/v1/bulk-orders/$orderId/status";
        $updateData = json_encode(['status' => $newStatus]);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $updateUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $updateData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $updateResponse = curl_exec($ch);
        $updateHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        echo "Update HTTP Code: $updateHttpCode\n";
        echo "Update Response: $updateResponse\n";
        
        if ($updateHttpCode === 200) {
            echo "✅ Status update successful!\n";
            
            // Verify the update by fetching the order again
            echo "\n🔍 Verifying update...\n";
            $verifyUrl = "http://192.168.100.4:8080/api/v1/bulk-orders/$orderId";
            
            curl_setopt($ch, CURLOPT_URL, $verifyUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            
            $verifyResponse = curl_exec($ch);
            $verifyHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($verifyHttpCode === 200) {
                $verifyData = json_decode($verifyResponse, true);
                echo "✅ Verified Status: {$verifyData['status']}\n";
            } else {
                echo "❌ Verification failed: $verifyHttpCode\n";
            }
        } else {
            echo "❌ Status update failed\n";
        }
        
        curl_close($ch);
    } else {
        echo "❌ No orders found\n";
    }
} else {
    echo "❌ Failed to fetch orders: $httpCode\n";
    echo "Response: $response\n";
}

echo "\n=== Test Complete ===\n";
