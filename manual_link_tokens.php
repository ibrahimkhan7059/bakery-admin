<?php

// Simple test to link FCM token manually
echo "🔧 MANUAL FCM TOKEN LINKING\n";
echo "==========================\n";

// Database connection
$host = '127.0.0.1';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected\n\n";
    
    // Get all users
    $stmt = $pdo->query("SELECT id, name, email FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👥 Available Users:\n";
    foreach ($users as $user) {
        echo "  {$user['id']}. {$user['name']} ({$user['email']})\n";
    }
    
    // Get FCM tokens with null user_id
    $stmt = $pdo->query("SELECT * FROM fcm_tokens WHERE user_id IS NULL");
    $nullTokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n📱 FCM Tokens with NULL user_id:\n";
    if (count($nullTokens) > 0) {
        foreach ($nullTokens as $token) {
            echo "  Token: " . substr($token['token'], 0, 30) . "...\n";
            echo "  Platform: {$token['platform']}\n";
            echo "  Created: {$token['created_at']}\n\n";
        }
        
        if (count($users) > 0) {
            // Link to the most recent user (usually the active user)
            $targetUser = $users[0];
            
            echo "🔗 Linking tokens to: {$targetUser['name']} ({$targetUser['email']})\n";
            
            $updateStmt = $pdo->prepare("UPDATE fcm_tokens SET user_id = ? WHERE user_id IS NULL");
            $result = $updateStmt->execute([$targetUser['id']]);
            
            if ($result) {
                echo "✅ Successfully linked " . count($nullTokens) . " tokens!\n";
            } else {
                echo "❌ Failed to link tokens\n";
            }
        } else {
            echo "❌ No users found to link tokens to\n";
        }
    } else {
        echo "✅ No tokens with NULL user_id found!\n";
    }
    
    // Show final status
    echo "\n📊 Final FCM Token Status:\n";
    echo "----------------------------\n";
    $stmt = $pdo->query("
        SELECT ft.*, u.name, u.email 
        FROM fcm_tokens ft 
        LEFT JOIN users u ON ft.user_id = u.id 
        ORDER BY ft.created_at DESC
    ");
    $allTokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($allTokens as $token) {
        $userName = $token['name'] ?? 'Guest';
        $userEmail = $token['email'] ?? 'No email';
        echo "👤 User: $userName ($userEmail)\n";
        echo "📱 Token: " . substr($token['token'], 0, 30) . "...\n";
        echo "📅 Created: {$token['created_at']}\n\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "✅ Done!\n";
?>
