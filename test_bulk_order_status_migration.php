<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Bulk Order Status Update After Migration ===\n";

// Get the first bulk order to test with
$bulkOrder = \App\Models\BulkOrder::first();

if (!$bulkOrder) {
    echo "❌ No bulk orders found in database\n";
    exit(1);
}

echo "📦 Testing with Bulk Order:\n";
echo "ID: {$bulkOrder->id}\n";
echo "Order Number: {$bulkOrder->order_number}\n";
echo "Current Status: {$bulkOrder->status}\n";
echo "Customer: {$bulkOrder->customer_name}\n\n";

// Test updating status to 'ready'
echo "🔄 Testing status update to 'ready'...\n";

try {
    $bulkOrder->update(['status' => 'ready']);
    $bulkOrder->refresh();
    
    echo "✅ Status updated successfully!\n";
    echo "New Status: {$bulkOrder->status}\n";
    
    // Test back to pending
    echo "\n🔄 Testing status update back to 'pending'...\n";
    $bulkOrder->update(['status' => 'pending']);
    $bulkOrder->refresh();
    
    echo "✅ Status updated successfully!\n";
    echo "New Status: {$bulkOrder->status}\n";
    
    // Test all valid statuses
    $validStatuses = ['pending', 'processing', 'ready', 'completed', 'cancelled'];
    
    echo "\n🧪 Testing all valid statuses...\n";
    foreach ($validStatuses as $status) {
        try {
            $bulkOrder->update(['status' => $status]);
            $bulkOrder->refresh();
            echo "✅ Status '$status': Success\n";
        } catch (Exception $e) {
            echo "❌ Status '$status': Failed - {$e->getMessage()}\n";
        }
    }
    
    // Reset to pending
    $bulkOrder->update(['status' => 'pending']);
    echo "\n🔄 Reset status to 'pending'\n";
    
} catch (Exception $e) {
    echo "❌ Error updating status: {$e->getMessage()}\n";
}

echo "\n=== Test Complete ===\n";
