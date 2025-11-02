<?php

// Test FCM notification for the linked user
echo "🔔 TESTING FCM NOTIFICATION\n";
echo "===========================\n";

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
        WHERE ft.token LIKE 'ep17c33VSLiT4Rc6Fgr9Cr%'
        LIMIT 1
    ");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "👤 Target User: {$user['name']} ({$user['email']})\n";
        echo "📱 FCM Token: " . substr($user['token'], 0, 30) . "...\n";
        echo "🔗 User ID: {$user['id']}\n\n";
        
        echo "✅ FCM Token is properly linked!\n";
        echo "🎉 Ready to receive notifications!\n\n";
        
        echo "📝 To test notification, you can:\n";
        echo "1. Login to the Flutter app with: {$user['email']}\n";
        echo "2. Change order status in admin panel\n";
        echo "3. Notification should arrive on the device\n";
        
    } else {
        echo "❌ No user found with the real FCM token\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n✅ Test completed!\n";
?>
