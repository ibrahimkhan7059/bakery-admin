<?php

// Check bulk order data
echo "🔍 CHECKING BULK ORDER DATA\n";
echo "==========================\n";

$host = '127.0.0.1';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check bulk order 23
    $stmt = $pdo->query("SELECT id, user_id, customer_email, customer_name FROM bulk_orders WHERE id = 23");
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "📦 Bulk Order 23:\n";
        echo "User ID: " . ($order['user_id'] ?? 'NULL') . "\n";
        echo "Customer Email: " . $order['customer_email'] . "\n";
        echo "Customer Name: " . $order['customer_name'] . "\n\n";
        
        // Check if this email exists in users table
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ?");
        $stmt->execute([$order['customer_email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "👤 Found matching user:\n";
            echo "User ID: {$user['id']}\n";
            echo "Name: {$user['name']}\n";
            echo "Email: {$user['email']}\n\n";
            
            // Check FCM tokens for this user
            $stmt = $pdo->prepare("SELECT token FROM fcm_tokens WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "📱 FCM Tokens for this user: " . count($tokens) . "\n";
            foreach ($tokens as $token) {
                echo "  " . substr($token, 0, 30) . "...\n";
            }
            
        } else {
            echo "❌ No user found with email: {$order['customer_email']}\n";
        }
        
    } else {
        echo "❌ Bulk order 23 not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Check completed!\n";
?>
