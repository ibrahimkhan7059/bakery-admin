<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Shared Device FCM Token Registration ===\n";

// Simulate same FCM token being used by different users
$testToken = 'test_shared_device_token_' . time();

echo "Using test token: " . substr($testToken, 0, 30) . "...\n\n";

// Test 1: Register token for User 15 (Ibrahim)
echo "=== Test 1: Register token for User 15 (Ibrahim) ===\n";

$request1 = new \Illuminate\Http\Request();
$request1->merge([
    'token' => $testToken,
    'platform' => 'android',
    'user_email' => 'ibrahimkhan7059@gmail.com'
]);

$controller = new \App\Http\Controllers\Api\FcmTokenController();
$response1 = $controller->register($request1);
$responseData1 = json_decode($response1->getContent(), true);

echo "Response: " . json_encode($responseData1) . "\n";

// Check database
$tokensUser15 = \App\Models\FcmToken::where('user_id', 15)->where('token', $testToken)->count();
echo "Tokens for User 15: $tokensUser15\n\n";

// Test 2: Register SAME token for User 14 (Javeria) - shared device
echo "=== Test 2: Register SAME token for User 14 (Javeria) ===\n";

$request2 = new \Illuminate\Http\Request();
$request2->merge([
    'token' => $testToken, // Same token!
    'platform' => 'android',
    'user_email' => 'jkayani001@gmail.com'
]);

$response2 = $controller->register($request2);
$responseData2 = json_decode($response2->getContent(), true);

echo "Response: " . json_encode($responseData2) . "\n";

// Check database
$tokensUser14 = \App\Models\FcmToken::where('user_id', 14)->where('token', $testToken)->count();
$tokensUser15After = \App\Models\FcmToken::where('user_id', 15)->where('token', $testToken)->count();

echo "Tokens for User 14: $tokensUser14\n";
echo "Tokens for User 15: $tokensUser15After\n";

// Test 3: Check all entries for this token
echo "\n=== All entries for this token ===\n";
$allTokens = \App\Models\FcmToken::where('token', $testToken)->with('user')->get();

foreach ($allTokens as $token) {
    $userName = $token->user ? $token->user->name : 'Unknown';
    echo "User ID: {$token->user_id} ({$userName}), Active: " . ($token->is_active ? 'Yes' : 'No') . "\n";
}

// Test 4: Send notification to both users with same token
echo "\n=== Testing Notifications to Both Users ===\n";

try {
    $notificationService = app(\App\Services\NotificationMessageService::class);
    
    // Send to User 15
    echo "Sending notification to User 15...\n";
    $result15 = $notificationService->sendOrderProcessing(15, 999);
    echo "User 15 result: " . ($result15 ? 'Success' : 'Failed') . "\n";
    
    // Send to User 14
    echo "Sending notification to User 14...\n";
    $result14 = $notificationService->sendOrderProcessing(14, 999);
    echo "User 14 result: " . ($result14 ? 'Success' : 'Failed') . "\n";
    
} catch (Exception $e) {
    echo "Error sending notifications: " . $e->getMessage() . "\n";
}

// Cleanup test token
echo "\n=== Cleanup ===\n";
$deleted = \App\Models\FcmToken::where('token', $testToken)->delete();
echo "Deleted $deleted test token entries\n";

echo "\n=== Test Complete ===\n";
