<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Direct Bulk Order Notification Test ===\n";

// Find a suitable bulk order
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

// Test the user ID logic directly
$userId = $bulkOrder->user_id;

echo "\nTesting user ID resolution logic...\n";

// If user_id is null or admin (1), try to find user by email
if (!$userId || $userId == 1) {
    echo "User ID is null or admin (1), looking up by email...\n";
    if ($bulkOrder->customer_email) {
        $user = \App\Models\User::where('email', $bulkOrder->customer_email)->first();
        if ($user) {
            $oldUserId = $userId;
            $userId = $user->id;
            echo "Found user by email! Changed user_id from {$oldUserId} to {$userId}\n";
            
            // Update the bulk order with correct user_id
            $bulkOrder->update(['user_id' => $userId]);
            echo "Updated bulk order with correct user_id\n";
        } else {
            echo "No user found with email: {$bulkOrder->customer_email}\n";
        }
    }
} else {
    echo "User ID looks good: {$userId}\n";
}

// Check if user has FCM token
if ($userId) {
    $fcmToken = \App\Models\FcmToken::where('user_id', $userId)->first();
    if ($fcmToken) {
        echo "FCM token exists: {$fcmToken->token}\n";
        
        // Test notification service directly
        echo "\nTesting notification service...\n";
        
        try {
            $notificationService = app(\App\Services\NotificationMessageService::class);
            
            // Send test notification
            $result = $notificationService->sendBulkOrderUpdate(
                $userId, 
                $bulkOrder->id, 
                'processing',
                'Test notification: Your bulk order is now being prepared!'
            );
            
            echo "Notification sent successfully: " . json_encode($result) . "\n";
            
        } catch (Exception $e) {
            echo "Error sending notification: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
        
    } else {
        echo "No FCM token found for user ID: {$userId}\n";
        
        // Create a test FCM token for testing
        echo "Creating test FCM token...\n";
        $testToken = 'test_token_' . time();
        
        \App\Models\FcmToken::create([
            'user_id' => $userId,
            'token' => $testToken,
            'device_type' => 'test'
        ]);
        
        echo "Test FCM token created: {$testToken}\n";
    }
} else {
    echo "No valid user ID found!\n";
}

echo "\n=== Test Complete ===\n";
