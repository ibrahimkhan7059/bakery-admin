<?php

// Test FCM notification sending
echo "🔔 TESTING FCM NOTIFICATION SENDING\n";
echo "===================================\n";

// Database connection
$host = '127.0.0.1';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get user with FCM token
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, ft.token 
        FROM users u 
        JOIN fcm_tokens ft ON u.id = ft.user_id 
        WHERE u.id = 15
        LIMIT 1
    ");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "👤 Target User: {$user['name']} ({$user['email']})\n";
        echo "🔑 User ID: {$user['id']}\n";
        echo "📱 FCM Token: " . substr($user['token'], 0, 30) . "...\n\n";
        
        // Test notification sending using Laravel artisan
        echo "🧪 Testing notification via Laravel command...\n";
        
        // Create a temporary test command
        $testUrl = "http://10.110.10.28:8080/api/test-fcm-notification";
        
        $data = json_encode([
            'user_id' => $user['id'],
            'title' => 'Test Notification',
            'message' => 'This is a test notification from admin panel!'
        ]);
        
        echo "📤 Sending test notification...\n";
        echo "URL: $testUrl\n";
        echo "Data: $data\n\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "📬 Response Code: $httpCode\n";
        echo "📬 Response: $response\n\n";
        
        if ($httpCode == 200) {
            echo "✅ Test notification sent successfully!\n";
        } else {
            echo "❌ Failed to send test notification\n";
            echo "Check Laravel logs: tail -f storage/logs/laravel.log\n";
        }
        
    } else {
        echo "❌ User with ID 15 not found or no FCM token\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Test completed!\n";
?>
