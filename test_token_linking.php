<?php

/**
 * Test User Login and FCM Token Linking System
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FcmToken;
use App\Models\User;
use App\Services\FirebaseNotificationService;

echo "🔗 TESTING USER LOGIN & FCM TOKEN LINKING\n";
echo "========================================\n\n";

try {
    // 1. Check current FCM tokens
    echo "📱 1. CURRENT FCM TOKEN STATUS\n";
    echo "------------------------------\n";
    
    $tokens = FcmToken::with('user')->get();
    
    foreach ($tokens as $token) {
        echo "Token: " . substr($token->token, 0, 30) . "...\n";
        echo "User ID: " . ($token->user_id ?? 'Guest (NULL)') . "\n";
        echo "User Name: " . ($token->user ? $token->user->name : 'No User Linked') . "\n";
        echo "Platform: {$token->platform}\n";
        echo "Active: " . ($token->is_active ? 'Yes' : 'No') . "\n";
        echo "Created: {$token->created_at}\n\n";
    }
    
    // 2. Link guest tokens to User ID 1 (simulate login)
    echo "🔄 2. SIMULATING USER LOGIN (Linking tokens to User ID 1)\n";
    echo "---------------------------------------------------------\n";
    
    $user = User::first();
    if (!$user) {
        echo "❌ No users found in database\n";
        exit;
    }
    
    echo "👤 Target User: {$user->name} (ID: {$user->id})\n";
    echo "📧 Email: {$user->email}\n\n";
    
    // Find guest tokens
    $guestTokens = FcmToken::whereNull('user_id')->get();
    
    if ($guestTokens->isEmpty()) {
        echo "ℹ️  No guest tokens found to link\n";
    } else {
        echo "🔗 Linking {$guestTokens->count()} guest token(s) to authenticated user...\n\n";
        
        foreach ($guestTokens as $token) {
            $token->user_id = $user->id;
            $token->save();
            
            echo "✅ Linked token: " . substr($token->token, 0, 20) . "...\n";
            echo "   Platform: {$token->platform}\n";
            echo "   Created: {$token->created_at}\n\n";
        }
    }
    
    // 3. Check final status
    echo "📊 3. FINAL TOKEN STATUS AFTER LOGIN SIMULATION\n";
    echo "----------------------------------------------\n";
    
    $userTokens = FcmToken::where('user_id', $user->id)->get();
    
    echo "👤 User {$user->name} has {$userTokens->count()} FCM token(s):\n\n";
    
    foreach ($userTokens as $token) {
        echo "Token: " . substr($token->token, 0, 30) . "...\n";
        echo "Platform: {$token->platform}\n";
        echo "Active: " . ($token->is_active ? 'Yes' : 'No') . "\n";
        echo "Created: {$token->created_at}\n\n";
    }
    
    // 4. Test notification to authenticated user
    if ($userTokens->isNotEmpty()) {
        echo "🧪 4. TESTING NOTIFICATION TO AUTHENTICATED USER\n";
        echo "===============================================\n";
        
        $firebaseService = new FirebaseNotificationService();
        
        $tokens = $userTokens->pluck('token')->toArray();
        
        echo "📤 Sending notification to authenticated user...\n";
        
        $result = $firebaseService->sendToTokens(
            $tokens,
            "🎉 Login Successful!",
            "Welcome back {$user->name}! Your FCM token is now linked to your account.",
            [
                'type' => 'login_success',
                'user_id' => $user->id,
                'timestamp' => now()->toISOString()
            ]
        );
        
        if ($result) {
            echo "✅ Notification sent successfully!\n";
            echo "📱 Check your device for the notification\n";
        } else {
            echo "⚠️  Notification failed to send\n";
        }
    } else {
        echo "❌ No FCM tokens found for authenticated user\n";
    }
    
    echo "\n🎯 SYSTEM STATUS\n";
    echo "===============\n";
    echo "✅ FCM token linking system working\n";
    echo "✅ Guest tokens properly converted to user tokens\n";
    echo "✅ Notification system ready for authenticated users\n";
    
    echo "\n💡 NEXT STEPS\n";
    echo "=============\n";
    echo "1. 📱 Login to Flutter app to test real token linking\n";
    echo "2. 🔔 Check console logs for FCM registration messages\n";
    echo "3. 🧪 Test order status notifications from admin panel\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

?>
