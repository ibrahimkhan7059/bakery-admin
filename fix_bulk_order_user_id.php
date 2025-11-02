<?php

// Fix bulk order user_id based on customer email
echo "🔧 FIXING BULK ORDER USER_ID\n";
echo "==============================\n";

$host = '127.0.0.1';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all bulk orders with customer emails
    $stmt = $pdo->query("
        SELECT id, user_id, customer_email, customer_name 
        FROM bulk_orders 
        WHERE customer_email IS NOT NULL AND customer_email != ''
    ");
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📦 Found " . count($orders) . " bulk orders with emails\n\n";
    
    $fixedCount = 0;
    
    foreach ($orders as $order) {
        // Find user by email
        $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$order['customer_email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['id'] != $order['user_id']) {
            echo "🔄 Fixing Order #{$order['id']}:\n";
            echo "  Email: {$order['customer_email']}\n";
            echo "  Old User ID: {$order['user_id']}\n";
            echo "  New User ID: {$user['id']} ({$user['name']})\n";
            
            // Update bulk order with correct user_id
            $updateStmt = $pdo->prepare("UPDATE bulk_orders SET user_id = ? WHERE id = ?");
            $result = $updateStmt->execute([$user['id'], $order['id']]);
            
            if ($result) {
                echo "  ✅ Updated successfully!\n\n";
                $fixedCount++;
            } else {
                echo "  ❌ Update failed!\n\n";
            }
        } else if ($user) {
            echo "✅ Order #{$order['id']} already has correct user_id\n";
        } else {
            echo "⚠️  Order #{$order['id']} - No user found for email: {$order['customer_email']}\n";
        }
    }
    
    echo "🎉 Fixed $fixedCount bulk orders!\n";
    
    // Show updated status
    echo "\n📊 Updated Bulk Orders Status:\n";
    echo "--------------------------------\n";
    $stmt = $pdo->query("
        SELECT bo.id, bo.user_id, bo.customer_email, bo.customer_name, u.name as user_name
        FROM bulk_orders bo
        LEFT JOIN users u ON bo.user_id = u.id
        WHERE bo.customer_email IS NOT NULL AND bo.customer_email != ''
        ORDER BY bo.id DESC
        LIMIT 5
    ");
    $updatedOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($updatedOrders as $order) {
        echo "📦 Order #{$order['id']}: {$order['customer_name']} → User ID: {$order['user_id']} ({$order['user_name']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Fix completed!\n";
?>
