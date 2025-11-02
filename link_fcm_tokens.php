<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

echo "🔧 FCM TOKEN LINKING SCRIPT\n";
echo "===========================\n";

// Find FCM tokens with null user_id
$guestTokens = \App\Models\FcmToken::where('user_id', null)->get();

echo "📱 Found {$guestTokens->count()} guest FCM tokens\n\n";

if ($guestTokens->count() > 0) {
    // Get the most recent user (assuming it's the current user)
    $recentUser = \App\Models\User::latest()->first();
    
    if ($recentUser) {
        echo "👤 Most recent user: {$recentUser->name} ({$recentUser->email})\n";
        echo "🔗 Linking guest tokens to this user...\n\n";
        
        foreach ($guestTokens as $token) {
            $token->update(['user_id' => $recentUser->id]);
            echo "✅ Linked token: " . substr($token->token, 0, 30) . "...\n";
        }
        
        echo "\n🎉 Successfully linked {$guestTokens->count()} tokens to user: {$recentUser->name}\n";
    } else {
        echo "❌ No users found in database!\n";
    }
} else {
    echo "✅ No guest tokens found - all tokens are properly linked!\n";
}

echo "\n📊 Current FCM Token Status:\n";
echo "-----------------------------\n";

$allTokens = \App\Models\FcmToken::with('user')->get();
foreach ($allTokens as $token) {
    $userName = $token->user ? $token->user->name : 'Guest';
    $userEmail = $token->user ? $token->user->email : 'No email';
    echo "👤 User: {$userName} ({$userEmail})\n";
    echo "📱 Token: " . substr($token->token, 0, 30) . "...\n";
    echo "📅 Created: {$token->created_at}\n\n";
}

echo "✅ Done!\n";
