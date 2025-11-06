<?php
// Test artisan command to debug the update method
// Put this in app/Console/Commands/TestBulkOrderUpdate.php

require_once 'vendor/autoload.php';

// Load Laravel environment
require_once 'bootstrap/app.php';

use App\Models\BulkOrder;
use Illuminate\Http\Request;
use App\Http\Controllers\BulkOrderController;

try {
    echo "=== TESTING LARAVEL UPDATE METHOD ===\n";
    
    // Get the bulk order
    $bulkOrder = BulkOrder::find(19);
    if (!$bulkOrder) {
        echo "Order 19 not found!\n";
        exit;
    }
    
    echo "Current status: " . $bulkOrder->status . "\n";
    
    // Create a fake request data (similar to form submission)
    $requestData = [
        'customer_name' => $bulkOrder->customer_name,
        'customer_phone' => $bulkOrder->customer_phone,
        'customer_email' => $bulkOrder->customer_email,
        'delivery_address' => $bulkOrder->delivery_address,
        'delivery_date' => $bulkOrder->delivery_date->format('Y-m-d'),
        'delivery_time' => $bulkOrder->delivery_time ? $bulkOrder->delivery_time->format('H:i') : null,
        'order_type' => $bulkOrder->order_type,
        'event_details' => $bulkOrder->event_details,
        'payment_method' => $bulkOrder->payment_method,
        'advance_payment' => $bulkOrder->advance_payment,
        'status' => 'completed', // Change to completed
        'payment_status' => $bulkOrder->payment_status,
        'special_instructions' => $bulkOrder->special_instructions,
        'products' => []
    ];
    
    // Add products data
    foreach ($bulkOrder->items as $item) {
        $requestData['products'][] = [
            'id' => $item->product_id,
            'quantity' => $item->quantity,
            'notes' => $item->notes
        ];
    }
    
    // Create request object
    $request = new Request($requestData);
    $request->setMethod('PUT');
    
    // Test the update method
    $controller = new BulkOrderController(new App\Services\NotificationMessageService());
    
    echo "Testing update method...\n";
    $response = $controller->update($request, $bulkOrder);
    
    // Check if status updated
    $bulkOrder->refresh();
    echo "Status after update: " . $bulkOrder->status . "\n";
    
    if ($bulkOrder->status === 'completed') {
        echo "✅ Laravel update method working!\n";
    } else {
        echo "❌ Laravel update method failed!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
