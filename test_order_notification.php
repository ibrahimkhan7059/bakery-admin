<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Services\NotificationMessageService;
use App\Services\FirebaseNotificationService;

echo "Order Status Change Test\n";
echo "========================\n\n";

// Find or create order for user 1
$order = Order::where('user_id', 1)->first();

if (!$order) {
    echo "No order found. Creating test order...\n";
    
    $order = Order::create([
        'user_id' => 1,
        'customer_name' => 'Test Customer',
        'customer_phone' => '03001234567',
        'delivery_address' => 'Test Address',
        'payment_method' => 'cash',
        'status' => 'pending',
        'payment_status' => 'pending',
        'total_amount' => 500,
        'notes' => 'Test order for notification'
    ]);
    
    echo "✅ Test order created: ID {$order->id}\n";
} else {
    echo "✅ Using existing order: ID {$order->id}\n";
}

echo "Current status: {$order->status}\n\n";

// Test notification directly
$firebase = new FirebaseNotificationService();
$messageService = new NotificationMessageService($firebase);

echo "Sending order ready notification...\n";
$result = $messageService->sendOrderReady(1, $order->id, 'pickup');

if ($result) {
    echo "✅ Notification sent!\n";
    
    // Update order status
    $order->update(['status' => 'ready']);
    echo "✅ Order status updated to 'ready'\n";
    
} else {
    echo "❌ Notification failed\n";
}

echo "\nNow go to admin panel and change order status to see notifications!\n";
echo "Admin URL: http://192.168.100.4:8080/orders/{$order->id}/edit\n";

?>
