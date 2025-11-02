<?php
require_once __DIR__.'/vendor/autoload.php';

// Initialize Laravel app
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Working Firebase function from simple_fcm_test.php
function sendFirebaseNotification($projectId, $fcmToken, $title, $body, $data = []) {
    $serviceAccountPath = storage_path('app/firebase/service-account.json');
    
    if (!file_exists($serviceAccountPath)) {
        echo "❌ Service account file not found\n";
        return false;
    }

    // Get access token
    $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
    
    // Create JWT
    $header = json_encode(['typ' => 'JWT', 'alg' => 'RS256']);
    $now = time();
    $payload = json_encode([
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + 3600
    ]);
    
    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = '';
    openssl_sign(
        $base64Header . "." . $base64Payload,
        $signature,
        $serviceAccount['private_key'],
        'SHA256'
    );
    
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    $jwt = $base64Header . "." . $base64Payload . "." . $base64Signature;
    
    // Exchange JWT for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'] ?? null;
    
    if (!$accessToken) {
        echo "❌ Failed to get access token\n";
        return false;
    }
    
    // Send notification
    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
    
    $message = [
        'message' => [
            'token' => $fcmToken,
            'notification' => [
                'title' => $title,
                'body' => $body
            ],
            'data' => $data
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode == 200;
}

// Test Laravel notifications with working function
echo "🔥 Testing Laravel Notifications (Working Version)\n";
echo "=================================================\n\n";

$projectId = 'bakehub-474807';
$testToken = "ecMyxOz3QZ62ITcngkYUNU:APA91bHLwgURaB3a0B96ivCG6KMHlyiPNt25RuXAz3rBZgU7O-fA_zBNe6Wahk4fJ3vmHztfL5TtM6RK969PysFIiPuTItfyOP-QSKHKgMoZGqFsS8sOczI";

// Order notification
echo "📦 Sending order notification...\n";
$result1 = sendFirebaseNotification(
    $projectId,
    $testToken,
    "Order Confirmed! 🛍️",
    "Your cake order #12345 has been confirmed and is being prepared.",
    [
        'type' => 'order',
        'order_id' => '12345',
        'status' => 'confirmed',
        'module' => 'order_management'
    ]
);

if ($result1) {
    echo "✅ Order notification sent successfully!\n\n";
} else {
    echo "❌ Order notification failed!\n\n";
}

// Custom cake notification
echo "🍰 Sending custom cake notification...\n";
$result2 = sendFirebaseNotification(
    $projectId,
    $testToken,
    "Custom Cake Update! 🎨",
    "Your custom cake design has been approved and production started.",
    [
        'type' => 'custom_cake',
        'custom_id' => '567',
        'status' => 'approved',
        'module' => 'custom_cake'
    ]
);

if ($result2) {
    echo "✅ Custom cake notification sent successfully!\n\n";
} else {
    echo "❌ Custom cake notification failed!\n\n";
}

// Bulk order notification
echo "📋 Sending bulk order notification...\n";
$result3 = sendFirebaseNotification(
    $projectId,
    $testToken,
    "Bulk Order Update! 📦",
    "Your bulk order for 50 cupcakes is ready for pickup.",
    [
        'type' => 'bulk_order',
        'bulk_id' => '789',
        'status' => 'ready',
        'module' => 'bulk_order'
    ]
);

if ($result3) {
    echo "✅ Bulk order notification sent successfully!\n\n";
} else {
    echo "❌ Bulk order notification failed!\n\n";
}

// Promotional notification
echo "🎉 Sending promotional notification...\n";
$result4 = sendFirebaseNotification(
    $projectId,
    $testToken,
    "Weekend Special! 🎁",
    "Get 20% off on all birthday cakes this weekend only!",
    [
        'type' => 'promotion',
        'discount' => '20%',
        'category' => 'birthday_cakes',
        'module' => 'promotional'
    ]
);

if ($result4) {
    echo "✅ Promotional notification sent successfully!\n\n";
} else {
    echo "❌ Promotional notification failed!\n\n";
}

// Summary
$totalTests = 4;
$passedTests = ($result1 ? 1 : 0) + ($result2 ? 1 : 0) + ($result3 ? 1 : 0) + ($result4 ? 1 : 0);

echo "=================================================\n";
echo "📊 LARAVEL NOTIFICATION TEST SUMMARY:\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: " . ($totalTests - $passedTests) . "\n";

if ($passedTests == $totalTests) {
    echo "🎉 ALL LARAVEL TESTS PASSED!\n";
    echo "Laravel Firebase notification system is working perfectly!\n";
} else {
    echo "⚠️  Some Laravel tests failed.\n";
}
?>
