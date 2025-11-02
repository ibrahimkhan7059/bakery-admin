<?php
require_once 'vendor/autoload.php';

use App\Services\FirebaseNotificationService;

// Test notification with your FCM token
$notificationService = new FirebaseNotificationService();

// Replace with your actual FCM token from the app
// Get this token from Profile screen in your app
$testToken = "ecMyxOz3QZ62ITcngkYUNU:APA91bHLwgURaB3a0B96ivCG6KMHlyiPNt25RuXAz3rBZgU7O-fA_zBNe6Wahk4fJ3vmHztfL5TtM6RK969PysFIiPuTItfyOP-QSKHKgMoZGqFsS8sOczI";

$result = $notificationService->sendToTokens(
    [$testToken],
    "BakeHub Test from Laravel",
    "Your notification system is working perfectly! 🎂🚀",
    [
        'type' => 'test',
        'timestamp' => time(),
        'module' => 'testing'
    ]
);

if ($result) {
    echo "✅ Notification sent successfully!\n";
} else {
    echo "❌ Failed to send notification\n";
}
?>
