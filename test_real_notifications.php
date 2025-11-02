<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FirebaseNotificationService;
use App\Services\NotificationMessageService;
use App\Models\FcmToken;

echo "🔔 TESTING REAL NOTIFICATION SENDING\n";
echo "=====================================\n\n";

try {
    $firebaseService = new FirebaseNotificationService();
    $messageService = new NotificationMessageService($firebaseService);
    
    // Get real FCM tokens from database
    $fcmTokens = FcmToken::where('is_active', true)->get();
    
    echo "📱 Found " . $fcmTokens->count() . " active FCM tokens in database\n\n";
    
    foreach ($fcmTokens as $index => $fcmToken) {
        echo "Token " . ($index + 1) . ":\n";
        echo "  User ID: " . ($fcmToken->user_id ?? 'Guest') . "\n";
        echo "  Platform: {$fcmToken->platform}\n";
        echo "  Token: " . substr($fcmToken->token, 0, 30) . "...\n";
        echo "  Created: {$fcmToken->created_at}\n\n";
    }
    
    if ($fcmTokens->count() > 0) {
        // Test with real tokens from database
        $tokens = $fcmTokens->pluck('token')->toArray();
        
        echo "🧪 TESTING DIRECT FIREBASE NOTIFICATION\n";
        echo "---------------------------------------\n";
        
        $result = $firebaseService->sendToTokens(
            $tokens,
            "🧪 Test from Laravel Backend",
            "Your order status has been updated to: Ready for pickup!",
            [
                'type' => 'order',
                'order_id' => '123',
                'status' => 'ready',
                'timestamp' => now()->toISOString()
            ]
        );
        
        if ($result) {
            echo "✅ Direct Firebase notification sent successfully!\n";
        } else {
            echo "❌ Direct Firebase notification failed\n";
        }
        
        echo "\n🧪 TESTING ORDER READY MESSAGE SERVICE\n";
        echo "-------------------------------------\n";
        
        // Test with user ID 1 (if exists)
        $userWithToken = FcmToken::whereNotNull('user_id')->first();
        if ($userWithToken) {
            $result2 = $messageService->sendOrderReady($userWithToken->user_id, 'ORD-123', 'pickup');
            
            if ($result2) {
                echo "✅ Order ready message service sent successfully!\n";
            } else {
                echo "❌ Order ready message service failed\n";
            }
        } else {
            echo "⚠️  No authenticated user tokens found for testing\n";
        }
        
        echo "\n📊 DETAILED TOKEN ANALYSIS\n";
        echo "-------------------------\n";
        
        foreach ($tokens as $token) {
            if (strlen($token) < 140) {
                echo "⚠️  Token too short (likely test): " . substr($token, 0, 20) . "... (Length: " . strlen($token) . ")\n";
            } else {
                echo "✅ Valid FCM token format: " . substr($token, 0, 20) . "... (Length: " . strlen($token) . ")\n";
            }
        }
        
    } else {
        echo "❌ No FCM tokens found in database\n";
        echo "💡 Make sure Flutter app has registered tokens\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    
    if ($e->getPrevious()) {
        echo "🔗 Previous: " . $e->getPrevious()->getMessage() . "\n";
    }
}

echo "\n💡 DEBUGGING TIPS:\n";
echo "==================\n";
echo "1. Check if Flutter app is running and has FCM token\n";
echo "2. Ensure device/emulator has Google Play Services\n";
echo "3. Check Firebase project settings\n";
echo "4. Verify Laravel logs for detailed errors\n";
echo "5. Test with actual device instead of emulator if possible\n";

?>
