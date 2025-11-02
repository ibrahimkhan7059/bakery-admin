<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FcmToken;
use App\Models\User;

echo "🔧 MANUAL FCM TOKEN REGISTRATION\n";
echo "================================\n\n";

echo "Flutter app se real FCM token copy kar ke yahan paste karo:\n";
echo "(Real tokens usually start with something like: c1234abcd:APA91b...)\n";
echo "Token: ";

$token = trim(fgets(STDIN));

if (empty($token) || strlen($token) < 50) {
    echo "❌ Invalid token. Real FCM tokens are usually 150+ characters long.\n";
    exit;
}

echo "\nAvailable Users:\n";
$users = User::all();
foreach ($users as $user) {
    echo "{$user->id}. {$user->name} ({$user->email})\n";
}

echo "\nEnter User ID: ";
$userId = (int) trim(fgets(STDIN));

$user = User::find($userId);
if (!$user) {
    echo "❌ Invalid user ID.\n";
    exit;
}

try {
    // Remove any existing tokens for this user
    FcmToken::where('user_id', $userId)->delete();
    
    // Add new token
    FcmToken::create([
        'user_id' => $userId,
        'token' => $token,
        'platform' => 'android',
        'is_active' => true
    ]);
    
    echo "\n✅ FCM token registered successfully!\n";
    echo "User: {$user->name}\n";
    echo "Token: " . substr($token, 0, 30) . "...\n";
    echo "Length: " . strlen($token) . " characters\n";
    
    // Test notification immediately
    echo "\nTesting notification...\n";
    
    $firebaseService = new App\Services\FirebaseNotificationService();
    $result = $firebaseService->sendToTokens(
        [$token],
        "🎉 BakeHub Test",
        "Your FCM token has been registered successfully!",
        [
            'type' => 'test',
            'user_id' => $userId,
            'timestamp' => now()->toISOString()
        ]
    );
    
    if ($result) {
        echo "✅ Test notification sent! Check your device.\n";
    } else {
        echo "❌ Test notification failed. Check Laravel logs.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>
