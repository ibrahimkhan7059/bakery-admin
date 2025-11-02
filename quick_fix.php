<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FcmToken;

echo "Quick FCM Token Fix\n";
echo "===================\n\n";

// Real working FCM token format (example from Flutter)
$realToken = "eW1234567890abcdef:APA91bGH1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz1234567890";

// Clear old test tokens
FcmToken::truncate();

// Add real token for user 1
FcmToken::create([
    'user_id' => 1,
    'token' => $realToken,
    'platform' => 'android',
    'is_active' => true
]);

echo "✅ Real FCM token added for User ID 1\n";
echo "Token: " . substr($realToken, 0, 30) . "...\n";
echo "Length: " . strlen($realToken) . " chars\n\n";

// Test notification
use App\Services\FirebaseNotificationService;
$firebase = new FirebaseNotificationService();

echo "Testing notification...\n";
$result = $firebase->sendToTokens(
    [$realToken],
    "Order Ready!",
    "Your order is ready for pickup",
    ['type' => 'order', 'order_id' => '123']
);

if ($result) {
    echo "✅ Notification sent successfully!\n";
} else {
    echo "❌ Failed - check Firebase setup\n";
}

?>
