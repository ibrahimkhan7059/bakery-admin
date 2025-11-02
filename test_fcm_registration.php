<?php

echo "🔧 TESTING FCM TOKEN REGISTRATION\n";
echo "=================================\n\n";

$url = "http://192.168.100.4:8080/api/v1/register-fcm-token";
$data = [
    'token' => 'test_flutter_token_' . time(),
    'platform' => 'android'
];

echo "🎯 URL: $url\n";
echo "📤 Data: " . json_encode($data) . "\n\n";

$postData = json_encode($data);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                   "Accept: application/json\r\n" .
                   "Content-Length: " . strlen($postData) . "\r\n",
        'content' => $postData,
        'timeout' => 10
    ]
]);

echo "⏳ Sending request...\n";
$result = @file_get_contents($url, false, $context);
$httpResponseHeader = $http_response_header ?? [];

echo "📡 HTTP Response Headers:\n";
foreach ($httpResponseHeader as $header) {
    echo "   $header\n";
}

if ($result !== false) {
    echo "\n✅ Response received:\n";
    echo $result . "\n";
} else {
    echo "\n❌ Request failed\n";
    $error = error_get_last();
    if ($error) {
        echo "🔍 Error: " . $error['message'] . "\n";
    }
}

// Also test route:list to see if route exists
echo "\n🗺️  CHECKING ROUTES\n";
echo "==================\n";
exec('php artisan route:list --method=POST --path=fcm 2>&1', $output);
foreach ($output as $line) {
    if (strpos($line, 'fcm') !== false || strpos($line, 'token') !== false) {
        echo "$line\n";
    }
}

?>
