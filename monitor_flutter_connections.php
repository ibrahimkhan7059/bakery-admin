<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🕵️ FLUTTER APP CONNECTION DETECTIVE\n";
echo "=====================================\n\n";

echo "This script will monitor Laravel logs in real-time.\n";
echo "Run your Flutter app now and watch for API calls...\n\n";

$logFile = storage_path('logs/laravel.log');

if (!file_exists($logFile)) {
    echo "❌ Log file not found: $logFile\n";
    exit;
}

echo "📡 Monitoring: $logFile\n";
echo "🔍 Looking for: FCM, token, register, login, API calls\n";
echo "Press Ctrl+C to stop monitoring...\n\n";

// Get initial file size
$lastSize = filesize($logFile);

while (true) {
    clearstatcache();
    $currentSize = filesize($logFile);
    
    if ($currentSize > $lastSize) {
        // New content added
        $handle = fopen($logFile, 'r');
        fseek($handle, $lastSize);
        
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            
            // Filter for relevant lines
            if (preg_match('/(fcm|token|register|login|api|notification|error)/i', $line)) {
                $timestamp = date('H:i:s');
                echo "[$timestamp] $line\n";
            }
        }
        
        fclose($handle);
        $lastSize = $currentSize;
    }
    
    usleep(500000); // Sleep for 0.5 seconds
}

?>
