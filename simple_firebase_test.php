<?php

echo "🔥 SIMPLE FIREBASE TEST\n";
echo "=====================\n";

try {
    // Bootstrap Laravel
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "✅ Laravel bootstrapped successfully\n";
    
    // Check if Firebase service exists
    if (class_exists('App\\Services\\FirebaseNotificationService')) {
        echo "✅ FirebaseNotificationService class found\n";
        
        // Try to instantiate
        $firebase = new App\Services\FirebaseNotificationService();
        echo "✅ Firebase service instantiated successfully\n";
        
    } else {
        echo "❌ FirebaseNotificationService class not found\n";
    }
    
    // Check FCM tokens
    $tokens = App\Models\FcmToken::count();
    echo "📱 FCM tokens in database: {$tokens}\n";
    
    // Check service account file
    $serviceAccountPath = storage_path('app/firebase/service-account.json');
    if (file_exists($serviceAccountPath)) {
        echo "✅ Service account file exists\n";
        $size = filesize($serviceAccountPath);
        echo "📋 File size: {$size} bytes\n";
    } else {
        echo "❌ Service account file missing at: {$serviceAccountPath}\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
}

echo "\n✨ Test completed!\n";
?>
