<?php
// Laravel Bootstrap
require_once __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FirebaseNotificationService;

echo "🔥 Testing Laravel FirebaseNotificationService...\n";
echo "===============================================\n\n";

try {
    // Initialize the service
    $notificationService = new FirebaseNotificationService();
    echo "✅ FirebaseNotificationService initialized successfully\n";

    // Your FCM token
    $testToken = "ecMyxOz3QZ62ITcngkYUNU:APA91bHLwgURaB3a0B96ivCG6KMHlyiPNt25RuXAz3rBZgU7O-fA_zBNe6Wahk4fJ3vmHztfL5TtM6RK969PysFIiPuTItfyOP-QSKHKgMoZGqFsS8sOczI";
    
    echo "Token: " . substr($testToken, 0, 30) . "...\n\n";

    // Test 1: Simple notification
    echo "📱 Test 1: Sending simple notification...\n";
    $result1 = $notificationService->sendToTokens(
        [$testToken],
        "BakeHub Laravel Test 🎂",
        "Your Laravel notification system is working perfectly!",
        [
            'type' => 'test',
            'timestamp' => time(),
            'module' => 'laravel_test'
        ]
    );

    if ($result1) {
        echo "✅ Test 1 PASSED: Simple notification sent successfully!\n\n";
    } else {
        echo "❌ Test 1 FAILED: Simple notification failed\n\n";
    }

    // Test 2: Order update notification  
    echo "📦 Test 2: Sending order update notification...\n";
    $result2 = $notificationService->sendOrderUpdate(
        1, // userId
        12345, // orderId
        'confirmed',
        "Your cake order #12345 has been confirmed! 🛍️"
    );

    if ($result2) {
        echo "✅ Test 2 PASSED: Order update notification sent successfully!\n\n";
    } else {
        echo "❌ Test 2 FAILED: Order update notification failed\n\n";
    }

    // Test 3: Custom cake update notification
    echo "🍰 Test 3: Sending custom cake update notification...\n";
    $result3 = $notificationService->sendCustomCakeUpdate(
        1, // userId
        567, // customId
        'approved',
        "Your custom cake design has been approved! 🎨"
    );

    if ($result3) {
        echo "✅ Test 3 PASSED: Custom cake update notification sent successfully!\n\n";
    } else {
        echo "❌ Test 3 FAILED: Custom cake update notification failed\n\n";
    }

    // Test 4: Promotional notification to all
    echo "🎉 Test 4: Sending promotional notification...\n";
    $result4 = $notificationService->sendPromotionalNotification(
        "Weekend Special! 🎁",
        "Get 20% off on all birthday cakes this weekend!",
        null, // imageUrl
        "https://bakehub.com/offers" // actionUrl
    );

    if ($result4) {
        echo "✅ Test 4 PASSED: Promotional notification sent successfully!\n\n";
    } else {
        echo "❌ Test 4 FAILED: Promotional notification failed\n\n";
    }

    // Summary
    $totalTests = 4;
    $passedTests = ($result1 ? 1 : 0) + ($result2 ? 1 : 0) + ($result3 ? 1 : 0) + ($result4 ? 1 : 0);
    
    echo "===============================================\n";
    echo "📊 TEST SUMMARY:\n";
    echo "Total Tests: $totalTests\n";
    echo "Passed: $passedTests\n";
    echo "Failed: " . ($totalTests - $passedTests) . "\n";
    
    if ($passedTests == $totalTests) {
        echo "🎉 ALL TESTS PASSED! Laravel notification system is working perfectly!\n";
    } else {
        echo "⚠️  Some tests failed. Check the output above for details.\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
