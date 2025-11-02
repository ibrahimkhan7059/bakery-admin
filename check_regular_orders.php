<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Regular Orders for User ID 14 ===\n";

// Check orders for user 14
$user = \App\Models\User::find(14);
if ($user) {
    echo "User: {$user->name} ({$user->email})\n";
    
    // Check orders
    $orders = \App\Models\Order::where('user_id', 14)->get();
    echo "Orders with user_id = 14: " . $orders->count() . "\n";
    
    foreach ($orders as $order) {
        echo "Order ID: {$order->id}, Status: {$order->status}, Total: {$order->total_amount}\n";
    }
    
    // Check orders by email
    $ordersByEmail = \App\Models\Order::where('customer_email', $user->email)->get();
    echo "\nOrders with customer_email = {$user->email}: " . $ordersByEmail->count() . "\n";
    
    foreach ($ordersByEmail as $order) {
        echo "Order ID: {$order->id}, User ID: {$order->user_id}, Status: {$order->status}, Email: {$order->customer_email}\n";
    }
    
    // Check FCM token
    $fcmToken = \App\Models\FcmToken::where('user_id', 14)->first();
    if ($fcmToken) {
        echo "\nFCM Token: " . substr($fcmToken->token, 0, 50) . "...\n";
    } else {
        echo "\nNo FCM token found for user 14\n";
    }
    
} else {
    echo "User 14 not found\n";
}

echo "\n=== Complete ===\n";
