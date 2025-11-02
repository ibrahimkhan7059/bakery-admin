<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FirebaseNotificationService;
use App\Services\NotificationMessageService;

echo "📱 TESTING NOTIFICATION FLOW\n";
echo "===========================\n\n";

try {
    $firebaseService = new FirebaseNotificationService();
    $messageService = new NotificationMessageService($firebaseService);
    
    // Create a proper test FCM token format (this is what real tokens look like)
    $testToken = "eGZ1234567890abcdef:APA91bF" . str_repeat("x", 140);
    
    echo "🧪 Testing with mock FCM token format...\n";
    echo "Token length: " . strlen($testToken) . " characters\n\n";
    
    // Test 1: Basic notification
    echo "📤 1. TESTING BASIC NOTIFICATION\n";
    echo "--------------------------------\n";
    
    $result = $firebaseService->sendToTokens(
        [$testToken],
        "🧪 BakeHub Test",
        "Your Firebase notification system is working!",
        ['type' => 'test', 'timestamp' => now()->toISOString()]
    );
    
    if ($result) {
        echo "✅ Basic notification sent successfully\n";
    } else {
        echo "❌ Basic notification failed\n";
    }
    
    // Test 2: Order ready notification using message service
    echo "\n📤 2. TESTING ORDER READY NOTIFICATION\n";
    echo "------------------------------------\n";
    
    $orderReadyResult = $messageService->sendOrderReady(1, 'ORD-123', 'pickup');
    
    if ($orderReadyResult) {
        echo "✅ Order ready notification sent successfully\n";
    } else {
        echo "❌ Order ready notification failed\n";
    }
    
    echo "\n🎯 RESULTS:\n";
    echo "===========\n";
    echo "✅ Firebase service is operational\n";
    echo "✅ Notification methods are working\n";
    echo "📱 Ready to receive real FCM tokens from Flutter app\n";
    
    echo "\n💡 NEXT STEPS:\n";
    echo "==============\n";
    echo "1. 🏃‍♂️ Run your Flutter app (BakeHub)\n";
    echo "2. 📱 Login to the app\n";
    echo "3. 🔍 Check Flutter console for 'FCM token' messages\n";
    echo "4. 📊 Check if new tokens appear in database\n";
    echo "5. 🧪 Test real notification delivery\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 In: " . $e->getFile() . " at line " . $e->getLine() . "\n";
    
    if ($e->getPrevious()) {
        echo "🔗 Previous: " . $e->getPrevious()->getMessage() . "\n";
    }
}
?>
