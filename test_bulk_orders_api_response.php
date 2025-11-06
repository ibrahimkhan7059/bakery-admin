<?php

echo "=== Testing Bulk Orders API Response ===\n";

$url = "http://192.168.100.4:8080/api/v1/bulk-orders";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
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
echo "Response: $response\n\n";

if ($httpCode === 200) {
    $orders = json_decode($response, true);
    if (!empty($orders)) {
        echo "📦 Found " . count($orders) . " bulk orders:\n";
        foreach ($orders as $index => $order) {
            echo "Order " . ($index + 1) . ":\n";
            echo "  - ID: {$order['id']}\n";
            echo "  - Status: \"{$order['status']}\" (" . gettype($order['status']) . ")\n";
            echo "  - Customer: {$order['customer_name']}\n";
            echo "  - Created: {$order['created_at']}\n";
            echo "  - Updated: {$order['updated_at']}\n\n";
        }
    } else {
        echo "No orders found\n";
    }
} else {
    echo "❌ Failed to fetch orders\n";
}

echo "=== Test Complete ===\n";
