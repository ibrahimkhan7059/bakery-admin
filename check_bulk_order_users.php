<?php

// Check bulk order and user mapping
echo "🔍 CHECKING BULK ORDER USER MAPPING\n";
echo "===================================\n";

$host = '127.0.0.1';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check bulk orders and their users
    $stmt = $pdo->query("
        SELECT bo.id, bo.user_id, bo.customer_name, bo.customer_email, bo.status,
               u.name as user_name, u.email as user_email,
               ft.token as fcm_token
        FROM bulk_orders bo
        LEFT JOIN users u ON bo.user_id = u.id
        LEFT JOIN fcm_tokens ft ON u.id = ft.user_id
        ORDER BY bo.created_at DESC
        LIMIT 5
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($orders) {
        foreach ($orders as $order) {
            echo "📦 Bulk Order ID: {$order['id']}\n";
            echo "👤 User ID: {$order['user_id']}\n";
            echo "👤 User Name: " . ($order['user_name'] ?? 'N/A') . "\n";
            echo "📧 User Email: " . ($order['user_email'] ?? 'N/A') . "\n";
            echo "🛒 Customer Name: {$order['customer_name']}\n";
            echo "📧 Customer Email: {$order['customer_email']}\n";
            echo "📱 FCM Token: " . (substr($order['fcm_token'] ?? 'No token', 0, 30)) . "...\n";
            echo "📊 Status: {$order['status']}\n";
            echo "---\n";
        }
        
        // Check specifically for real FCM token users
        echo "\n🎯 USERS WITH REAL FCM TOKENS:\n";
        $stmt = $pdo->query("
            SELECT u.id, u.name, u.email, ft.token 
            FROM users u 
            JOIN fcm_tokens ft ON u.id = ft.user_id 
            WHERE ft.token LIKE 'ep17c33VSLiT4Rc6Fgr9Cr%'
        ");
        $realTokenUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($realTokenUsers as $user) {
            echo "👤 User ID: {$user['id']}\n";
            echo "👤 Name: {$user['name']}\n";
            echo "📧 Email: {$user['email']}\n";
            echo "📱 FCM Token: " . substr($user['token'], 0, 30) . "...\n";
            echo "---\n";
        }
        
    } else {
        echo "❌ No bulk orders found\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n✅ Check completed!\n";
?>
