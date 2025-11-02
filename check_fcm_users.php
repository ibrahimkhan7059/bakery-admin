<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Current FCM Token Status ===\n";

// Check all FCM tokens
$fcmTokens = \App\Models\FcmToken::with('user')->get();

echo "Total FCM tokens registered: " . $fcmTokens->count() . "\n\n";

if ($fcmTokens->count() > 0) {
    foreach ($fcmTokens as $token) {
        echo "User ID: {$token->user_id}\n";
        echo "User Name: " . ($token->user ? $token->user->name : 'Unknown') . "\n";
        echo "Email: " . ($token->user ? $token->user->email : 'Unknown') . "\n";
        echo "Token: " . substr($token->token, 0, 50) . "...\n";
        echo "Device Type: {$token->device_type}\n";
        echo "Created: {$token->created_at}\n";
        echo "---\n";
    }
} else {
    echo "No FCM tokens found!\n";
}

// Check if there are any bulk orders for users with FCM tokens
echo "\n=== Checking Bulk Orders for Users with FCM Tokens ===\n";

$userIdsWithTokens = $fcmTokens->pluck('user_id')->unique();

if ($userIdsWithTokens->count() > 0) {
    $bulkOrdersWithTokens = \App\Models\BulkOrder::whereIn('user_id', $userIdsWithTokens)
        ->where('status', '!=', 'completed')
        ->get();
    
    echo "Bulk orders for users with FCM tokens: " . $bulkOrdersWithTokens->count() . "\n";
    
    foreach ($bulkOrdersWithTokens as $order) {
        echo "Order ID: {$order->id}, User ID: {$order->user_id}, Status: {$order->status}, Email: {$order->customer_email}\n";
    }
} else {
    echo "No users with FCM tokens found.\n";
}

// Suggest a test with an available user
echo "\n=== Suggestion for Testing ===\n";
if ($userIdsWithTokens->count() > 0) {
    $testUserId = $userIdsWithTokens->first();
    echo "Use user ID $testUserId for testing notifications.\n";
    
    // Check if this user has any bulk orders
    $userOrders = \App\Models\BulkOrder::where('user_id', $testUserId)->get();
    echo "This user has " . $userOrders->count() . " bulk orders.\n";
    
    if ($userOrders->count() > 0) {
        $testOrder = $userOrders->first();
        echo "Test with bulk order ID: {$testOrder->id}\n";
    } else {
        echo "Consider creating a test bulk order for this user.\n";
    }
} else {
    echo "Please login to the app to register an FCM token first.\n";
}

echo "\n=== Complete ===\n";
