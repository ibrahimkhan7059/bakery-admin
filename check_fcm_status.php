<?php

// Quick FCM token test
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔔 FCM TOKEN STATUS CHECK\n";
echo "=========================\n\n";

// Check FCM tokens
$tokens = \App\Models\FcmToken::all();
echo "📱 Total FCM Tokens: " . $tokens->count() . "\n\n";

if ($tokens->count() > 0) {
    echo "📋 FCM Token Details:\n";
    echo "----------------------\n";
    foreach ($tokens as $token) {
        echo "User ID: {$token->user_id}\n";
        echo "Platform: {$token->platform}\n";
        echo "Active: " . ($token->is_active ? 'Yes' : 'No') . "\n";
        echo "Token: " . substr($token->token, 0, 30) . "...\n";
        echo "Created: {$token->created_at}\n\n";
    }
} else {
    echo "❌ No FCM tokens found!\n";
    echo "💡 This means Flutter app hasn't registered any tokens yet.\n\n";
    
    echo "🔍 TROUBLESHOOTING:\n";
    echo "1. Check Flutter console for FCM token registration logs\n";
    echo "2. Ensure user is logged in before token registration\n";
    echo "3. Check API endpoint accessibility\n";
}

// Check users for auth
$users = \App\Models\User::count();
echo "👤 Total Users: {$users}\n";

// Check if FCM endpoint exists
echo "\n🎯 API ENDPOINT CHECK:\n";
echo "----------------------\n";
echo "✅ FcmTokenController exists: " . (class_exists('App\Http\Controllers\Api\FcmTokenController') ? 'YES' : 'NO') . "\n";
echo "✅ Route registration: Check /v1/register-fcm-token endpoint\n";

?>
