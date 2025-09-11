<?php
echo "Testing Laravel API endpoints...\n\n";

// Test 1: Basic Laravel response
$url1 = "http://localhost:8000";
echo "Test 1: Basic Laravel response\n";
echo "URL: $url1\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response1 = curl_exec($ch);
$httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode1\n";
echo "Response: " . substr($response1, 0, 200) . "...\n\n";

// Test 2: API health endpoint
$url2 = "http://localhost:8000/api/v1/ai-cake/health";
echo "Test 2: API health endpoint\n";
echo "URL: $url2\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode2\n";
echo "Response: " . $response2 . "\n\n";

// Test 3: Network IP test
$url3 = "http://192.168.100.81:8000/api/v1/ai-cake/health";
echo "Test 3: Network IP test\n";
echo "URL: $url3\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url3);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response3 = curl_exec($ch);
$httpCode3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode3\n";
echo "Response: " . $response3 . "\n\n";

echo "Testing complete!\n";
?> 