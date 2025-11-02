<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Bulk Order Notification with Real FCM Token ===\n";

// Test with User ID 15 (Ibrahim Khan) who has a real FCM token
$testUserId = 15;
$bulkOrder = \App\Models\BulkOrder::where('user_id', $testUserId)
    ->where('status', '!=', 'completed')
    ->first();

if (!$bulkOrder) {
    echo "No suitable bulk order found for user ID $testUserId\n";
    exit;
}

echo "Testing with:\n";
echo "User ID: $testUserId\n";
echo "Bulk Order ID: {$bulkOrder->id}\n";
echo "Current Status: {$bulkOrder->status}\n";
echo "Customer Email: {$bulkOrder->customer_email}\n";

// Check FCM token
$fcmToken = \App\Models\FcmToken::where('user_id', $testUserId)->first();
if (!$fcmToken) {
    echo "No FCM token found for user $testUserId\n";
    exit;
}

echo "FCM Token: " . substr($fcmToken->token, 0, 50) . "...\n";

// Test the notification service directly
echo "\n=== Testing Notification Service ===\n";

try {
    $notificationService = app(\App\Services\NotificationMessageService::class);
    
    // Send test notification
    $result = $notificationService->sendBulkOrderUpdate(
        $testUserId, 
        $bulkOrder->id, 
        'processing',
        'Test notification: Your bulk order is now being prepared by our team!'
    );
    
    if ($result) {
        echo "✅ Notification sent successfully!\n";
        echo "Result: " . json_encode($result) . "\n";
    } else {
        echo "❌ Notification failed to send\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error sending notification: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Now test the actual controller logic
echo "\n=== Testing Controller Status Update ===\n";

try {
    // Simulate updating the status to trigger notification
    $oldStatus = $bulkOrder->status;
    $newStatus = $oldStatus === 'pending' ? 'processing' : 'pending';
    
    echo "Changing status from '$oldStatus' to '$newStatus'\n";
    
    // Create the notification service and controller
    $notificationService = app(\App\Services\NotificationMessageService::class);
    $controller = new \App\Http\Controllers\BulkOrderController($notificationService);
    
    // Create mock request
    $request = new \Illuminate\Http\Request();
    $request->setMethod('POST');
    $request->merge([
        'status' => $newStatus,
        'admin_notes' => 'Test status update with notification'
    ]);
    
    // Call updateStatus method
    $response = $controller->updateStatus($request, $bulkOrder);
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $responseData = $response->getData(true);
        echo "Controller response: " . json_encode($responseData) . "\n";
    } else {
        echo "Controller returned redirect response\n";
    }
    
    // Check if status was actually updated
    $bulkOrder->refresh();
    echo "New status in database: {$bulkOrder->status}\n";
    
} catch (Exception $e) {
    echo "❌ Error in controller test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
