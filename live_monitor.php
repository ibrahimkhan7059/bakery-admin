<?php

echo "Live API Request Monitor\n";
echo "========================\n\n";

echo "Laravel server running on: http://192.168.100.4:8080\n";
echo "Monitoring incoming requests...\n";
echo "Ab Flutter app run karo aur login karo!\n\n";

// Start monitoring Laravel logs in real-time
$logFile = storage_path('logs/laravel.log');
$lastSize = file_exists($logFile) ? filesize($logFile) : 0;

$startTime = time();

while (true) {
    clearstatcache();
    $currentSize = file_exists($logFile) ? filesize($logFile) : 0;
    
    if ($currentSize > $lastSize) {
        $handle = fopen($logFile, 'r');
        fseek($handle, $lastSize);
        
        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            
            // Show all relevant log entries
            if (preg_match('/(register|token|fcm|login|api|error)/i', $line)) {
                echo "[" . date('H:i:s') . "] $line\n";
            }
        }
        
        fclose($handle);
        $lastSize = $currentSize;
    }
    
    // Show status every 10 seconds
    if ((time() - $startTime) % 10 == 0) {
        echo "--- Monitoring... (Press Ctrl+C to stop) ---\n";
    }
    
    usleep(500000); // Sleep 0.5 seconds
}

?>
