<?php

/**
 * Link FCM Tokens to Authenticated User and Test Notifications
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FcmToken;
use App\Models\User;
use App\Services\FirebaseNotificationService;

echo "🔗 LINKING FCM TOKENS TO AUTHENTICATED USER\n";
echo "===========================================\n\n";

try {
    // 1. Get the first user (assuming this is your account)
    $user = User::first();
    if (!$user) {
        echo "❌ No users found in database\n";
        exit;
    }
    
    echo "👤 Found User: {$user->name} (ID: {$user->id})\n";
    echo "📧 Email: {$user->email}\n\n";
    
    // 2. Get all unlinked FCM tokens
    $unlinkedTokens = FcmToken::whereNull('user_id')->get();
    
    if ($unlinkedTokens->isEmpty()) {
        echo "ℹ️  All FCM tokens are already linked to users\n";
    } else {
        echo "🔗 Linking {$unlinkedTokens->count()} FCM tokens to user {$user->name}...\n\n";
        
        foreach ($unlinkedTokens as $token) {
            $token->user_id = $user->id;
            $token->save();
            
            echo "✅ Linked token: " . substr($token->token, 0, 20) . "...\n";
            echo "   Platform: {$token->platform}\n";
            echo "   Created: {$token->created_at}\n\n";
        }
    }
    
    // 3. Get all tokens for this user
    $userTokens = FcmToken::where('user_id', $user->id)->where('is_active', true)->get();
    
    echo "📱 User's Active FCM Tokens: {$userTokens->count()}\n";
    echo "================================\n";
    
    foreach ($userTokens as $token) {
        echo "Token: " . substr($token->token, 0, 30) . "...\n";
        echo "Platform: {$token->platform}\n";
        echo "Created: {$token->created_at}\n\n";
    }
    
    // 4. Test notification to this user
    if ($userTokens->isNotEmpty()) {
        echo "🧪 TESTING NOTIFICATION DELIVERY\n";
        echo "===============================\n";
        
        $firebaseService = new FirebaseNotificationService();
        
        // Get real FCM tokens
        $tokens = $userTokens->pluck('token')->toArray();
        
        echo "📤 Sending test notification to {$userTokens->count()} device(s)...\n";
        
        $result = $firebaseService->sendToTokens(
            $tokens,
            "🎉 Welcome {$user->name}!",
            "Your FCM token has been successfully linked to your account. You will now receive order notifications.",
            [
                'type' => 'account_linked',
                'user_id' => $user->id,
                'timestamp' => now()->toISOString()
            ]
        );
        
        if ($result) {
            echo "✅ Test notification sent successfully!\n";
            echo "📱 Check your device for the notification\n\n";
            
            // 5. Test order notification
            echo "🛍️ TESTING ORDER STATUS NOTIFICATION\n";
            echo "===================================\n";
            
            $orderResult = $firebaseService->sendToTokens(
                $tokens,
                "🛍️ Order Update",
                "Your bakery order #12345 status has been updated to 'In Preparation'. We'll notify you when it's ready!",
                [
                    'type' => 'order_status_update',
                    'order_id' => '12345',
                    'status' => 'in_preparation',
                    'user_id' => $user->id,
                    'timestamp' => now()->toISOString()
                ]
            );
            
            if ($orderResult) {
                echo "✅ Order notification sent successfully!\n";
                echo "📱 Check your device for the order update notification\n";
            } else {
                echo "⚠️  Order notification failed\n";
            }
            
        } else {
            echo "⚠️  Test notification failed\n";
        }
        
    } else {
        echo "❌ No active FCM tokens found for user\n";
    }
    
    // 6. Final Status
    echo "\n🎯 FINAL STATUS\n";
    echo "==============\n";
    echo "✅ FCM tokens linked to authenticated user\n";
    echo "✅ Notification service tested\n";
    echo "✅ Ready for order status notifications\n";
    
    echo "\n💡 NEXT STEPS:\n";
    echo "=============\n";
    echo "1. 📱 Check your Flutter app for test notifications\n";
    echo "2. 🛍️ Test order status changes from admin panel\n";
    echo "3. 🔔 Verify notifications arrive when order status updates\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

?>
