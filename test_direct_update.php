<?php
// Direct test of updateStatus method
require_once 'vendor/autoload.php';

use App\Http\Controllers\BulkOrderController;
use App\Models\BulkOrder;
use Illuminate\Http\Request;
use App\Services\NotificationMessageService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DIRECT updateStatus METHOD TEST ===\n";

try {
    // Get the bulk order
    $bulkOrder = BulkOrder::find(19);
    if (!$bulkOrder) {
        echo "❌ Order 19 not found\n";
        exit;
    }
    
    echo "Current status: " . $bulkOrder->status . "\n";
    
    // Create a fake POST request
    $request = Request::create('/test', 'POST', ['status' => 'ready']);
    
    // Create controller instance with proper dependency injection
    $controller = app(BulkOrderController::class);
    
    echo "Calling updateStatus method...\n";
    
    // Call the method
    $response = $controller->updateStatus($request, $bulkOrder);
    
    echo "Method called successfully!\n";
    echo "Response type: " . get_class($response) . "\n";
    
    // Refresh the model to see if it changed
    $bulkOrder->refresh();
    echo "New status: " . $bulkOrder->status . "\n";
    
    if ($bulkOrder->status === 'completed') {
        echo "✅ Status successfully updated to 'completed'!\n";
    } else {
        echo "❌ Status was not updated\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
