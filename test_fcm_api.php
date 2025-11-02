<?php

echo "Quick FCM Token Test\n";
echo "====================\n\n";

// Test data
$testData = [
    'token' => 'flutter_test_token_' . time(),
    'platform' => 'android'
];

echo "Testing FCM registration endpoint...\n";
echo "URL: http://192.168.100.4:8080/api/v1/register-fcm-token\n";
echo "Data: " . json_encode($testData) . "\n\n";

$postData = json_encode($testData);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $postData,
        'timeout' => 10
    ]
]);

$result = @file_get_contents('http://192.168.100.4:8080/api/v1/register-fcm-token', false, $context);

if ($result !== false) {
    echo "✅ API Response: $result\n";
    
    // Check database
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    $tokens = App\Models\FcmToken::count();
    echo "✅ Tokens in database: $tokens\n";
    
} else {
    echo "❌ API call failed!\n";
    $error = error_get_last();
    echo "Error: " . ($error['message'] ?? 'Unknown error') . "\n";
}

echo "\nIf this works, then Flutter app network issue hai.\n";
echo "If this fails, then Laravel API problem hai.\n";

?>
