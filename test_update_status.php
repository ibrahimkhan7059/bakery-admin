<?php
// Test updating order 19 status
require_once 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=bakery_db_new', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== TESTING STATUS UPDATE FOR ORDER 19 ===\n";
    
    // First, check current status
    $stmt = $pdo->prepare("SELECT id, status FROM bulk_orders WHERE id = 19");
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        echo "Current status: '" . $order['status'] . "'\n";
        
        // Update to processing
        echo "Updating to 'processing'...\n";
        $updateStmt = $pdo->prepare("UPDATE bulk_orders SET status = 'processing' WHERE id = 19");
        $updateStmt->execute();
        
        // Check if update worked
        $stmt->execute();
        $updatedOrder = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "New status: '" . $updatedOrder['status'] . "'\n";
        
        if ($updatedOrder['status'] === 'processing') {
            echo "✅ Status update successful!\n";
        } else {
            echo "❌ Status update failed!\n";
        }
        
    } else {
        echo "Order 19 not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
