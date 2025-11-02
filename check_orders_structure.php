<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Orders Table Structure ===\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('orders');
echo "Orders table columns: " . implode(', ', $columns) . "\n";

echo "\n=== Sample Order Data ===\n";
$sampleOrder = \App\Models\Order::first();
if ($sampleOrder) {
    echo "Sample Order ID: {$sampleOrder->id}\n";
    echo "User ID: {$sampleOrder->user_id}\n";
    
    if (property_exists($sampleOrder, 'customer_email')) {
        echo "Customer Email: {$sampleOrder->customer_email}\n";
    }
    
    if (property_exists($sampleOrder, 'email')) {
        echo "Email: {$sampleOrder->email}\n";
    }
    
    echo "Status: {$sampleOrder->status}\n";
    echo "Total: {$sampleOrder->total_amount}\n";
}

echo "\n=== Complete ===\n";
