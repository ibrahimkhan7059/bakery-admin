<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== All FCM Tokens Analysis ===\n";

$tokens = \App\Models\FcmToken::with('user')->get();

foreach($tokens as $token) {
    $userName = $token->user ? $token->user->name : 'Unknown';
    $userEmail = $token->user ? $token->user->email : 'Unknown';
    echo "User ID: {$token->user_id} ({$userName} - {$userEmail})\n";
    echo "Token: " . substr($token->token, 0, 50) . "...\n";
    echo "Device Type: {$token->device_type}\n";
    echo "Created: {$token->created_at}\n";
    
    // Check if it's a test token
    if (strpos($token->token, 'test_token') !== false || strpos($token->token, 'flutter_test') !== false) {
        echo "⚠️  This is a TEST TOKEN\n";
    } else {
        echo "✅ This is a REAL FCM TOKEN\n";
    }
    echo "---\n";
}

echo "\n=== Testing Regular Order Notification ===\n";

// Test with the order we found
$order = \App\Models\Order::find(67);
if ($order) {
    echo "Testing notification for Order ID: {$order->id}\n";
    echo "User ID: {$order->user_id}\n";
    echo "Customer Email: {$order->customer_email}\n";
    echo "Current Status: {$order->status}\n";
    
    try {
        $notificationService = app(\App\Services\NotificationMessageService::class);
        
        // Test sending a notification
        $result = $notificationService->sendOrderProcessing(
            $order->user_id, 
            $order->id
        );
        
        if ($result) {
            echo "✅ Notification sent successfully!\n";
        } else {
            echo "❌ Notification failed to send\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error sending notification: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Complete ===\n";
