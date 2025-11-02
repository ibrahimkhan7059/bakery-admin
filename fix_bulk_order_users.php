<?php

// Fix bulk order user mapping
echo "🔧 FIXING BULK ORDER USER MAPPING\n";
echo "==================================\n";

$host = '127.0.0.1';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Update bulk orders for ibrahim khan to use correct user_id
    $stmt = $pdo->prepare("
        UPDATE bulk_orders 
        SET user_id = 15 
        WHERE customer_email = 'ibrahimkhan7059@gmail.com' 
        AND user_id = 1
    ");
    
    $result = $stmt->execute();
    $affectedRows = $stmt->rowCount();
    
    if ($result) {
        echo "✅ Updated $affectedRows bulk orders\n";
        echo "📧 Customer: ibrahimkhan7059@gmail.com\n";
        echo "👤 Changed from User ID: 1 → User ID: 15\n\n";
        
        // Verify the change
        $verifyStmt = $pdo->query("
            SELECT bo.id, bo.user_id, bo.customer_name, bo.customer_email,
                   u.name as user_name, u.email as user_email,
                   ft.token as fcm_token
            FROM bulk_orders bo
            LEFT JOIN users u ON bo.user_id = u.id
            LEFT JOIN fcm_tokens ft ON u.id = ft.user_id
            WHERE bo.customer_email = 'ibrahimkhan7059@gmail.com'
            ORDER BY bo.created_at DESC
            LIMIT 3
        ");
        
        $verifiedOrders = $verifyStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "📋 VERIFIED BULK ORDERS:\n";
        foreach ($verifiedOrders as $order) {
            echo "📦 Order ID: {$order['id']}\n";
            echo "👤 User ID: {$order['user_id']}\n";
            echo "👤 User Name: {$order['user_name']}\n";
            echo "📱 FCM Token: " . substr($order['fcm_token'], 0, 20) . "...\n";
            echo "---\n";
        }
        
    } else {
        echo "❌ No orders updated\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n✅ Fix completed!\n";
?>
