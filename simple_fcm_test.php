<?php
// Simple Firebase FCM V1 API test without Laravel

$projectId = 'bakehub-474807';
$fcmToken = 'ecMyxOz3QZ62ITcngkYUNU:APA91bHLwgURaB3a0B96ivCG6KMHlyiPNt25RuXAz3rBZgU7O-fA_zBNe6Wahk4fJ3vmHztfL5TtM6RK969PysFIiPuTItfyOP-QSKHKgMoZGqFsS8sOczI';

// Service account key path
$serviceAccountPath = 'storage/app/firebase/service-account.json';

if (!file_exists($serviceAccountPath)) {
    echo "❌ Service account file not found: $serviceAccountPath\n";
    exit(1);
}

// Get access token using service account
function getAccessToken($serviceAccountPath) {
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
    return $tokenData['access_token'] ?? null;
}

// Send notification
function sendNotification($projectId, $accessToken, $fcmToken) {
    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
    
    $message = [
        'message' => [
            'token' => $fcmToken,
            'notification' => [
                'title' => 'BakeHub Test 🎂',
                'body' => 'Your notification system is working perfectly!'
            ],
            'data' => [
                'type' => 'test',
                'timestamp' => (string)time(),
                'module' => 'testing'
            ]
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
    
    return ['code' => $httpCode, 'response' => $response];
}

echo "🔥 Testing Firebase FCM Notification...\n";
echo "Token: " . substr($fcmToken, 0, 20) . "...\n\n";

// Get access token
echo "Getting access token...\n";
$accessToken = getAccessToken($serviceAccountPath);

if (!$accessToken) {
    echo "❌ Failed to get access token\n";
    exit(1);
}

echo "✅ Access token obtained\n";

// Send notification
echo "Sending notification...\n";
$result = sendNotification($projectId, $accessToken, $fcmToken);

if ($result['code'] == 200) {
    echo "✅ Notification sent successfully!\n";
    echo "Response: " . $result['response'] . "\n";
} else {
    echo "❌ Failed to send notification\n";
    echo "HTTP Code: " . $result['code'] . "\n";
    echo "Response: " . $result['response'] . "\n";
}
?>
