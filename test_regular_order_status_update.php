<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Regular Order Status Update ===\n";

$order = \App\Models\Order::find(67);
if (!$order) {
    echo "Order 67 not found\n";
    exit;
}

echo "Order ID: {$order->id}\n";
echo "User ID: {$order->user_id}\n";
echo "Current Status: {$order->status}\n";
echo "Customer Email: {$order->customer_email}\n";

// Test the controller status update
echo "\n=== Testing Controller Status Update ===\n";

try {
    // Create the notification service and controller
    $notificationService = app(\App\Services\NotificationMessageService::class);
    $controller = new \App\Http\Controllers\OrderController($notificationService);
    
    // Test changing status from 'ready' to 'completed'
    $newStatus = $order->status === 'ready' ? 'completed' : 'processing';
    
    echo "Changing status from '{$order->status}' to '$newStatus'\n";
    
    // Create mock request
    $request = new \Illuminate\Http\Request();
    $request->setMethod('POST');
    $request->merge([
        'status' => $newStatus
    ]);
    
    // Call updateStatus method
    $response = $controller->updateStatus($request, $order);
    
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $responseData = $response->getData(true);
        echo "Controller response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "Controller returned redirect response\n";
    }
    
    // Check if status was actually updated
    $order->refresh();
    echo "New status in database: {$order->status}\n";
    
} catch (Exception $e) {
    echo "❌ Error in controller test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
