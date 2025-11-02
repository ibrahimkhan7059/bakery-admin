<?php

// Quick notification test
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚨 NOTIFICATION QUICK CHECK\n";
echo "===========================\n\n";

// Check 1: FCM Tokens
$fcmTokens = \App\Models\FcmToken::count();
echo "📱 FCM Tokens: $fcmTokens\n";

// Check 2: Service Account
$serviceFile = storage_path('app/firebase/service-account.json');
echo "📄 Service Account: " . (file_exists($serviceFile) ? "✅ EXISTS" : "❌ MISSING") . "\n";

// Check 3: Ready Orders
$readyOrders = \App\Models\Order::where('status', 'ready')->count();
echo "🟣 Ready Orders: $readyOrders\n";

// Check 4: Test Firebase Service
try {
    $firebase = new \App\Services\FirebaseNotificationService();
    echo "🔥 Firebase Service: ✅ LOADED\n";
} catch (Exception $e) {
    echo "🔥 Firebase Service: ❌ ERROR - " . $e->getMessage() . "\n";
}

echo "\n🎯 MAIN ISSUES:\n";
if ($fcmTokens == 0) {
    echo "• ❌ NO FCM TOKENS - Flutter app must register first\n";
}
if (!file_exists($serviceFile)) {
    echo "• ❌ NO SERVICE ACCOUNT - Firebase not configured\n";
}

echo "\n💡 SOLUTION:\n";
echo "1. Run Flutter app\n";
echo "2. Register FCM token from Flutter\n";
echo "3. Then test notifications\n";

?>
