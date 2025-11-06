<?php
// Test status update directly
require_once 'vendor/autoload.php';

// Test with CURL to the updateStatus endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://192.168.100.4:8080/admin/bulk-orders/19/status');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Get CSRF token first
$tokenCurl = curl_init();
curl_setopt($tokenCurl, CURLOPT_URL, 'http://192.168.100.4:8080/admin/bulk-orders/19');
curl_setopt($tokenCurl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($tokenCurl, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($tokenCurl, CURLOPT_COOKIEFILE, 'cookies.txt');

$response = curl_exec($tokenCurl);
curl_close($tokenCurl);

// Extract CSRF token
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';

echo "=== TESTING STATUS UPDATE ===\n";
echo "CSRF Token: " . substr($csrfToken, 0, 20) . "...\n";

// Now test status update
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $csrfToken,
    'status' => 'ready'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');

$updateResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: $httpCode\n";
echo "Response Length: " . strlen($updateResponse) . "\n";

if ($httpCode === 200 || $httpCode === 302) {
    echo "✅ Status update request successful!\n";
    
    // Check if status actually changed in database
    $pdo = new PDO('mysql:host=localhost;dbname=bakery_db_new', 'root', '');
    $stmt = $pdo->prepare("SELECT status FROM bulk_orders WHERE id = 19");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Current status in DB: '" . $order['status'] . "'\n";
    
    if ($order['status'] === 'ready') {
        echo "✅ Status successfully changed to 'ready'!\n";
    } else {
        echo "❌ Status NOT changed in database\n";
    }
} else {
    echo "❌ Status update failed\n";
    echo "Response: " . substr($updateResponse, 0, 500) . "\n";
}

// Cleanup
@unlink('cookies.txt');
?>
