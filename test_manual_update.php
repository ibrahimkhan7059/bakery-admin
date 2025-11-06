<?php
// Manual test - update status directly and see if Flutter gets it
require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bakery_db_new', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== MANUAL STATUS UPDATE TEST ===\n";
    
    // Check current status
    $stmt = $pdo->prepare("SELECT id, status FROM bulk_orders WHERE id = 19");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current status: " . $order['status'] . "\n";
    
    // Update to 'ready'
    echo "Updating to 'ready'...\n";
    $updateStmt = $pdo->prepare("UPDATE bulk_orders SET status = 'ready' WHERE id = 19");
    $updateStmt->execute();
    
    // Verify update
    $stmt->execute();
    $updatedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "New status: " . $updatedOrder['status'] . "\n";
    
    // Test API response
    echo "\n=== TESTING API RESPONSE ===\n";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://192.168.100.4:8080/api/v1/bulk-orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        foreach ($data as $order) {
            if ($order['id'] == 19) {
                echo "API shows Order 19 status: '" . $order['status'] . "'\n";
                if ($order['status'] === 'ready') {
                    echo "✅ API correctly shows updated status!\n";
                    echo "Flutter app should now show 'ready' status for Order 19\n";
                } else {
                    echo "❌ API still shows old status\n";
                }
                break;
            }
        }
    } else {
        echo "API Error: HTTP $httpCode\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
