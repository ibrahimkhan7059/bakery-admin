<?php
// Check database tokens
$host = 'localhost';
$dbname = 'bakery_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // Check if personal_access_tokens table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'personal_access_tokens'");
    if ($stmt->rowCount() > 0) {
        echo "personal_access_tokens table exists\n";
        
        // Check tokens
        $stmt = $pdo->query("SELECT * FROM personal_access_tokens ORDER BY created_at DESC LIMIT 5");
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Found " . count($tokens) . " tokens:\n";
        foreach ($tokens as $token) {
            echo "ID: {$token['id']}, Name: {$token['name']}, Tokenable ID: {$token['tokenable_id']}, Created: {$token['created_at']}\n";
        }
        
        // Check specific token
        $tokenId = '36';
        $stmt = $pdo->prepare("SELECT * FROM personal_access_tokens WHERE id = ?");
        $stmt->execute([$tokenId]);
        $specificToken = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($specificToken) {
            echo "\nSpecific token (ID: $tokenId) found:\n";
            echo "Tokenable ID: {$specificToken['tokenable_id']}\n";
            echo "Name: {$specificToken['name']}\n";
            echo "Created: {$specificToken['created_at']}\n";
            echo "Expires: {$specificToken['expires_at']}\n";
        } else {
            echo "\nToken with ID $tokenId not found\n";
        }
        
    } else {
        echo "personal_access_tokens table does not exist\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>
