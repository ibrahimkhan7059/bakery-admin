<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Different Devices Scenario ===\n";

// Simulate different devices with different FCM tokens
$device1Token = 'device1_token_' . time();
$device2Token = 'device2_token_' . (time() + 1);

echo "Device 1 Token: " . substr($device1Token, 0, 30) . "...\n";
echo "Device 2 Token: " . substr($device2Token, 0, 30) . "...\n\n";

// Test 1: User 15 (Ibrahim) on Device 1
echo "=== Test 1: User 15 (Ibrahim) on Device 1 ===\n";

$request1 = new \Illuminate\Http\Request();
$request1->merge([
    'token' => $device1Token,
    'platform' => 'android',
    'user_email' => 'ibrahimkhan7059@gmail.com'
]);

$controller = new \App\Http\Controllers\Api\FcmTokenController();
$response1 = $controller->register($request1);
$responseData1 = json_decode($response1->getContent(), true);

echo "Response: " . json_encode($responseData1) . "\n";

// Test 2: User 14 (Javeria) on Device 2 (different device)
echo "\n=== Test 2: User 14 (Javeria) on Device 2 ===\n";

$request2 = new \Illuminate\Http\Request();
$request2->merge([
    'token' => $device2Token, // Different token (different device)
    'platform' => 'android',
    'user_email' => 'jkayani001@gmail.com'
]);

$response2 = $controller->register($request2);
$responseData2 = json_decode($response2->getContent(), true);

echo "Response: " . json_encode($responseData2) . "\n";

// Check database entries
echo "\n=== Database Analysis ===\n";

$user15Tokens = \App\Models\FcmToken::where('user_id', 15)->get();
$user14Tokens = \App\Models\FcmToken::where('user_id', 14)->get();

echo "User 15 (Ibrahim) tokens: " . $user15Tokens->count() . "\n";
foreach ($user15Tokens as $token) {
    echo "  - " . substr($token->token, 0, 30) . "... (Active: " . ($token->is_active ? 'Yes' : 'No') . ")\n";
}

echo "User 14 (Javeria) tokens: " . $user14Tokens->count() . "\n";
foreach ($user14Tokens as $token) {
    echo "  - " . substr($token->token, 0, 30) . "... (Active: " . ($token->is_active ? 'Yes' : 'No') . ")\n";
}

// Test 3: Send notifications to both users
echo "\n=== Testing Notifications to Different Devices ===\n";

try {
    $notificationService = app(\App\Services\NotificationMessageService::class);
    
    // Send to User 15 (Device 1)
    echo "Sending notification to User 15 (Device 1)...\n";
    $result15 = $notificationService->sendOrderProcessing(15, 100);
    echo "User 15 result: " . ($result15 ? 'Success ✅' : 'Failed ❌') . "\n";
    
    // Send to User 14 (Device 2)
    echo "Sending notification to User 14 (Device 2)...\n";
    $result14 = $notificationService->sendOrderProcessing(14, 101);
    echo "User 14 result: " . ($result14 ? 'Success ✅' : 'Failed ❌') . "\n";
    
} catch (Exception $e) {
    echo "Error sending notifications: " . $e->getMessage() . "\n";
}

// Test 4: User switches device (User 15 now uses Device 2)
echo "\n=== Test 4: User 15 switches to Device 2 ===\n";

$request3 = new \Illuminate\Http\Request();
$request3->merge([
    'token' => $device2Token, // Same as Device 2 (now shared)
    'platform' => 'android',
    'user_email' => 'ibrahimkhan7059@gmail.com'
]);

$response3 = $controller->register($request3);
$responseData3 = json_decode($response3->getContent(), true);

echo "Response: " . json_encode($responseData3) . "\n";

// Check final state
echo "\n=== Final State Analysis ===\n";

$device2Tokens = \App\Models\FcmToken::where('token', $device2Token)->with('user')->get();
echo "Device 2 now has " . $device2Tokens->count() . " user(s):\n";

foreach ($device2Tokens as $token) {
    $userName = $token->user ? $token->user->name : 'Unknown';
    echo "  - User {$token->user_id} ({$userName})\n";
}

// Cleanup test tokens
echo "\n=== Cleanup ===\n";
$deleted1 = \App\Models\FcmToken::where('token', $device1Token)->delete();
$deleted2 = \App\Models\FcmToken::where('token', $device2Token)->delete();
echo "Deleted $deleted1 Device 1 tokens and $deleted2 Device 2 tokens\n";

echo "\n=== Benefits of Different Devices ===\n";
echo "✅ Each user gets unique FCM token\n";
echo "✅ No token conflicts or replacements\n";
echo "✅ Notifications deliver to correct device\n";
echo "✅ Better user experience\n";
echo "✅ No shared device complications\n";

echo "\n=== Test Complete ===\n";
