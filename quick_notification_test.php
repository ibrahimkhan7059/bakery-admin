<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FcmToken;
use App\Services\NotificationMessageService;
use App\Services\FirebaseNotificationService;

echo "Quick User Link & Notification Test\n";
echo "===================================\n\n";

// Link latest FCM token to User ID 1
$latestToken = FcmToken::latest()->first();
if ($latestToken) {
    $latestToken->update(['user_id' => 1]);
    echo "✅ Latest FCM token linked to User ID 1\n";
    echo "📱 Token: " . substr($latestToken->token, 0, 30) . "...\n\n";
    
    // Test notification
    $firebase = new FirebaseNotificationService();
    $messageService = new NotificationMessageService($firebase);
    
    echo "🧪 Testing order ready notification...\n";
    $result = $messageService->sendOrderReady(1, 123, 'pickup');
    
    if ($result) {
        echo "🎉 SUCCESS! Notification sent!\n";
        echo "📱 Check your device for notification!\n\n";
        
        echo "✅ SYSTEM READY FOR ADMIN PANEL!\n";
        echo "Now you can:\n";
        echo "1. Go to admin panel: http://192.168.100.4:8080/orders\n";
        echo "2. Change any order status\n";
        echo "3. Notification will be sent automatically!\n";
        
    } else {
        echo "❌ Notification failed. Check Firebase logs.\n";
    }
    
} else {
    echo "❌ No FCM tokens found\n";
}

?>
