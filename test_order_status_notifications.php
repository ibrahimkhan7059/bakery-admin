<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;
use App\Models\User;
use App\Models\FcmToken;
use App\Services\NotificationMessageService;
use App\Services\FirebaseNotificationService;

echo "🎯 TESTING ORDER STATUS NOTIFICATION FLOW\n";
echo "==========================================\n\n";

try {
    // Find a user with FCM token
    $userWithToken = FcmToken::whereNotNull('user_id')->with('user')->first();
    
    if (!$userWithToken) {
        echo "❌ No authenticated user with FCM token found\n";
        echo "💡 Please login to Flutter app first to register FCM token\n\n";
        
        // Show available tokens
        $tokens = FcmToken::all();
        echo "📱 Available FCM tokens:\n";
        foreach ($tokens as $token) {
            echo "  - User ID: " . ($token->user_id ?? 'Guest') . " | Token: " . substr($token->token, 0, 30) . "...\n";
        }
        exit;
    }
    
    $user = $userWithToken->user;
    echo "✅ Found user with FCM token:\n";
    echo "   User: {$user->name} (ID: {$user->id})\n";
    echo "   Email: {$user->email}\n";
    echo "   FCM Token: " . substr($userWithToken->token, 0, 30) . "...\n\n";
    
    // Find an order for this user or create a test one
    $order = Order::where('user_id', $user->id)->first();
    
    if (!$order) {
        echo "⚠️  No existing order found for this user\n";
        echo "💡 Create an order through Flutter app first or use manual test\n\n";
    } else {
        echo "✅ Found existing order:\n";
        echo "   Order ID: {$order->id}\n";
        echo "   Status: {$order->status}\n";
        echo "   Total: Rs. {$order->total_amount}\n\n";
    }
    
    // Test notification services
    $firebaseService = new FirebaseNotificationService();
    $messageService = new NotificationMessageService($firebaseService);
    
    echo "🧪 TESTING NOTIFICATION SERVICES\n";
    echo "--------------------------------\n";
    
    // Test 1: Direct Firebase notification
    echo "1. Testing direct Firebase notification...\n";
    $result1 = $firebaseService->sendToTokens(
        [$userWithToken->token],
        "🧪 Direct Firebase Test",
        "This is a direct Firebase notification test from Laravel backend.",
        [
            'type' => 'test',
            'user_id' => $user->id,
            'timestamp' => now()->toISOString()
        ]
    );
    
    if ($result1) {
        echo "   ✅ Direct Firebase notification sent!\n";
    } else {
        echo "   ❌ Direct Firebase notification failed\n";
    }
    
    // Test 2: Order ready notification
    echo "\n2. Testing order ready notification...\n";
    $result2 = $messageService->sendOrderReady($user->id, $order ? $order->id : 999, 'pickup');
    
    if ($result2) {
        echo "   ✅ Order ready notification sent!\n";
    } else {
        echo "   ❌ Order ready notification failed\n";
    }
    
    // Test 3: Order processing notification  
    echo "\n3. Testing order processing notification...\n";
    $result3 = $messageService->sendOrderProcessing($user->id, $order ? $order->id : 999);
    
    if ($result3) {
        echo "   ✅ Order processing notification sent!\n";
    } else {
        echo "   ❌ Order processing notification failed\n";
    }
    
    echo "\n🎯 SUMMARY\n";
    echo "----------\n";
    echo "User: {$user->name}\n";
    echo "FCM Token: Available ✅\n";
    echo "Direct Firebase: " . ($result1 ? "✅ Success" : "❌ Failed") . "\n";
    echo "Order Ready: " . ($result2 ? "✅ Success" : "❌ Failed") . "\n";
    echo "Order Processing: " . ($result3 ? "✅ Success" : "❌ Failed") . "\n";
    
    echo "\n💡 NEXT STEPS:\n";
    echo "==============\n";
    echo "1. Check Flutter app for notifications\n";
    echo "2. Update order status in admin panel\n";
    echo "3. Verify notification appears on device\n";
    echo "4. Check Laravel logs: tail -f storage/logs/laravel.log\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

?>
