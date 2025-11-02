<?php

echo "🌐 TESTING LARAVEL API CONNECTIVITY\n";
echo "===================================\n\n";

// Test different API endpoints
$baseUrl = "http://10.110.11.230:8080/api";
$endpoints = [
    'categories' => '/v1/categories',
    'fcm-register' => '/v1/register-fcm-token',
    'routes-list' => '/v1/routes' // if exists
];

foreach ($endpoints as $name => $endpoint) {
    $url = $baseUrl . $endpoint;
    echo "🔍 Testing: $name\n";
    echo "URL: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\n",
            'timeout' => 10
        ]
    ]);
    
    $result = @file_get_contents($url, false, $context);
    
    if ($result !== false) {
        echo "✅ Status: Accessible\n";
        echo "📄 Response: " . substr($result, 0, 100) . "...\n";
    } else {
        echo "❌ Status: Not accessible\n";
        $error = error_get_last();
        if ($error) {
            echo "🔍 Error: " . $error['message'] . "\n";
        }
    }
    echo "\n";
}

// Test if Laravel is running
echo "🏃‍♂️ CHECKING LARAVEL SERVER\n";
echo "===========================\n";
$laravelUrl = "http://10.110.11.230:8080";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5
    ]
]);

$result = @file_get_contents($laravelUrl, false, $context);
if ($result !== false) {
    echo "✅ Laravel server is running on port 8080\n";
} else {
    echo "❌ Laravel server not accessible\n";
}

?>
