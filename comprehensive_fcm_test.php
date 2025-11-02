<?php

/**
 * Comprehensive FCM Notification Test for Authenticated User
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FcmToken;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use App\Services\NotificationMessageService;

echo "🎉 COMPREHENSIVE FCM TEST FOR AUTHENTICATED USER\n";
echo "===============================================\n\n";

try {
    // 1. Get authenticated user with FCM tokens
    $user = User::find(1);
    if (!$user) {
        echo "❌ User not found\n";
        exit;
    }
    
    echo "👤 Testing notifications for: {$user->name}\n";
    echo "📧 Email: {$user->email}\n";
    echo "🆔 User ID: {$user->id}\n\n";
    
    // 2. Get user's FCM tokens
    $userTokens = FcmToken::where('user_id', $user->id)->where('is_active', true)->get();
    
    echo "📱 User's FCM Tokens: {$userTokens->count()}\n";
    echo "==============================\n";
    
    foreach ($userTokens as $index => $token) {
        echo "Token " . ($index + 1) . ":\n";
        echo "  Preview: " . substr($token->token, 0, 30) . "...\n";
        echo "  Platform: {$token->platform}\n";
        echo "  Active: " . ($token->is_active ? 'Yes' : 'No') . "\n";
        echo "  Created: {$token->created_at}\n\n";
    }
    
    if ($userTokens->isEmpty()) {
        echo "❌ No FCM tokens found for this user\n";
        echo "💡 Please login to Flutter app to register FCM token\n";
        exit;
    }
    
    // 3. Initialize notification services
    $firebaseService = new FirebaseNotificationService();
    $messageService = new NotificationMessageService($firebaseService);
    
    $tokens = $userTokens->pluck('token')->toArray();
    
    // 4. Test different notification types
    echo "🧪 TESTING DIFFERENT NOTIFICATION TYPES\n";
    echo "======================================\n\n";
    
    // Test 1: Welcome notification
    echo "📤 Test 1: Welcome Notification\n";
    echo "------------------------------\n";
    $result1 = $firebaseService->sendToTokens(
        $tokens,
        "🎉 Welcome {$user->name}!",
        "Your account is now connected to receive notifications. You'll get updates about your orders automatically!",
        [
            'type' => 'welcome',
            'user_id' => $user->id,
            'timestamp' => now()->toISOString()
        ]
    );
    echo $result1 ? "✅ Welcome notification sent\n" : "❌ Welcome notification failed\n";
    echo "\n";
    
    // Test 2: Order status notification
    echo "📤 Test 2: Order Status Notification\n";
    echo "-----------------------------------\n";
    $result2 = $messageService->sendOrderReady($user->id, 12345, 'pickup');
    echo $result2 ? "✅ Order ready notification sent\n" : "❌ Order ready notification failed\n";
    echo "\n";
    
    // Test 3: Order update notification
    echo "📤 Test 3: Order Update Notification\n";
    echo "-----------------------------------\n";
    $result3 = $firebaseService->sendToTokens(
        $tokens,
        "🛍️ Order Update - #67890",
        "Your bakery order is now being prepared by our expert bakers. Estimated completion: 30 minutes.",
        [
            'type' => 'order_status_update',
            'order_id' => 67890,
            'status' => 'in_preparation',
            'user_id' => $user->id,
            'estimated_completion' => '30 minutes',
            'timestamp' => now()->toISOString()
        ]
    );
    echo $result3 ? "✅ Order update notification sent\n" : "❌ Order update notification failed\n";
    echo "\n";
    
    // Test 4: Custom cake notification
    echo "📤 Test 4: Custom Cake Notification\n";
    echo "----------------------------------\n";
    $result4 = $firebaseService->sendToTokens(
        $tokens,
        "🍰 Custom Cake Update",
        "Your custom chocolate birthday cake design has been approved! We'll start baking it tomorrow.",
        [
            'type' => 'custom_cake_update',
            'cake_id' => 555,
            'status' => 'design_approved',
            'user_id' => $user->id,
            'next_step' => 'baking_tomorrow',
            'timestamp' => now()->toISOString()
        ]
    );
    echo $result4 ? "✅ Custom cake notification sent\n" : "❌ Custom cake notification failed\n";
    echo "\n";
    
    // Test 5: Promotional notification
    echo "📤 Test 5: Promotional Notification\n";
    echo "----------------------------------\n";
    $result5 = $firebaseService->sendToTokens(
        $tokens,
        "🎊 Special Offer - 20% Off!",
        "Get 20% off on all birthday cakes this weekend! Use code BIRTHDAY20. Valid until Sunday.",
        [
            'type' => 'promotion',
            'discount_code' => 'BIRTHDAY20',
            'discount_percentage' => 20,
            'valid_until' => 'Sunday',
            'category' => 'birthday_cakes',
            'user_id' => $user->id,
            'timestamp' => now()->toISOString()
        ]
    );
    echo $result5 ? "✅ Promotional notification sent\n" : "❌ Promotional notification failed\n";
    echo "\n";
    
    // 5. Summary
    echo "📊 TEST SUMMARY\n";
    echo "===============\n";
    $totalTests = 5;
    $successCount = array_sum([$result1, $result2, $result3, $result4, $result5]);
    
    echo "Total Tests: {$totalTests}\n";
    echo "Successful: {$successCount}\n";
    echo "Failed: " . ($totalTests - $successCount) . "\n";
    echo "Success Rate: " . round(($successCount / $totalTests) * 100, 1) . "%\n\n";
    
    if ($successCount == $totalTests) {
        echo "🎉 ALL TESTS PASSED!\n";
        echo "✅ FCM notification system is working perfectly\n";
        echo "✅ User authentication and token linking successful\n";
        echo "✅ Ready for production use\n";
    } elseif ($successCount > 0) {
        echo "⚠️  PARTIAL SUCCESS\n";
        echo "Some notifications sent successfully, check Laravel logs for failed ones\n";
    } else {
        echo "❌ ALL TESTS FAILED\n";
        echo "Check Firebase configuration and Laravel logs\n";
    }
    
    echo "\n💡 NEXT STEPS\n";
    echo "=============\n";
    echo "1. 📱 Check your Flutter app/device for received notifications\n";
    echo "2. 🛍️ Test real order flow from admin panel\n";
    echo "3. 🔔 Verify notifications appear when order status changes\n";
    echo "4. 👥 Test with multiple users and devices\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "🔍 Stack trace: " . $e->getTraceAsString() . "\n";
}

?>
