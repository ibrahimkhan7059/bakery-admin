<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Bulk Order Notification Test ===\n";

// Test 1: Check if we can find a bulk order with email
$bulkOrder = \App\Models\BulkOrder::whereNotNull('customer_email')
    ->where('status', '!=', 'completed')
    ->first();

if (!$bulkOrder) {
    echo "No suitable bulk order found for testing\n";
    exit;
}

echo "Testing with Bulk Order ID: {$bulkOrder->id}\n";
echo "Current user_id: {$bulkOrder->user_id}\n";
echo "Customer email: {$bulkOrder->customer_email}\n";
echo "Current status: {$bulkOrder->status}\n";

// Test 2: Check if we can find user by email
if ($bulkOrder->customer_email) {
    $user = \App\Models\User::where('email', $bulkOrder->customer_email)->first();
    if ($user) {
        echo "User found by email: ID {$user->id}, Name: {$user->name}\n";
        
        // Test 3: Check if user has FCM token
        $fcmToken = \App\Models\FcmToken::where('user_id', $user->id)->first();
        if ($fcmToken) {
            echo "FCM token exists for user: {$fcmToken->token}\n";
        } else {
            echo "No FCM token found for user\n";
        }
    } else {
        echo "No user found with email: {$bulkOrder->customer_email}\n";
    }
}

// Test 4: Simulate a status update via HTTP request
echo "\n=== Simulating Status Update via HTTP ===\n";

try {
    // Use HTTP client to make actual API call
    $baseUrl = 'http://10.110.11.230:8080';
    
    // Prepare request data
    $requestData = [
        'status' => 'processing',
        'admin_notes' => 'Test notification from automated system'
    ];
    
    // Make HTTP request to update status
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/api/v1/bulk-orders/{$bulkOrder->id}/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        echo "cURL Error: " . curl_error($ch) . "\n";
    } else {
        echo "HTTP Status: $httpCode\n";
        echo "Response: $response\n";
    }
    
    curl_close($ch);
    
} catch (Exception $e) {
    echo "Error during HTTP request: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
