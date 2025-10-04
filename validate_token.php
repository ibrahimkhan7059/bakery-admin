<?php
require_once 'vendor/autoload.php';

// Database configuration
$host = 'localhost';
$dbname = 'bakery_db';
$username = 'bakery_user';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database successfully\n";
    
    // Get all tokens with their hashed values
    $stmt = $pdo->prepare("SELECT id, name, tokenable_id, token, created_at, expires_at FROM personal_access_tokens ORDER BY id DESC");
    $stmt->execute();
    $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($tokens) . " tokens:\n";
    foreach ($tokens as $token) {
        $hashedToken = substr($token['token'], 0, 20) . '...';
        $expires = $token['expires_at'] ? $token['expires_at'] : 'Never';
        echo "ID: {$token['id']}, Name: {$token['name']}, Tokenable ID: {$token['tokenable_id']}, Created: {$token['created_at']}, Expires: {$expires}, Token: {$hashedToken}\n";
    }
    
    // Try to find token by checking if the plain text token matches any hashed token
    echo "\nChecking if the plain token '36|Y4n3fn6YgzRKnpEFJJ6Twix7D8YMBKHRC7wDCCwA9827fb98' matches any stored hash...\n";
    $plainToken = 'Y4n3fn6YgzRKnpEFJJ6Twix7D8YMBKHRC7wDCCwA9827fb98';
    
    $foundMatch = false;
    foreach ($tokens as $token) {
        if (hash_equals(hash('sha256', $plainToken), $token['token'])) {
            echo "✅ Token matches! ID: {$token['id']}, User ID: {$token['tokenable_id']}\n";
            $foundMatch = true;
            break;
        }
    }
    
    if (!$foundMatch) {
        echo "❌ No matching token found in database\n";
        echo "This means the token '36|Y4n3fn6YgzRKnpEFJJ6Twix7D8YMBKHRC7wDCCwA9827fb98' is invalid or expired\n";
    }
    
    // Check users table for user ID 15 (from latest token)
    echo "\nChecking user details for tokenable_id 15:\n";
    $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE id = 15");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "User found: ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Created: {$user['created_at']}\n";
    } else {
        echo "User with ID 15 not found\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
