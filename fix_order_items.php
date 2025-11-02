<?php

/**
 * Fix existing order items - populate missing subtotal values
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel app
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;

echo "🔧 FIXING ORDER ITEMS - POPULATING SUBTOTAL VALUES\n";
echo "==================================================\n\n";

try {
    // Get all order items that have missing subtotal values
    $orderItems = DB::table('order_items')
        ->whereNull('subtotal')
        ->orWhere('subtotal', 0)
        ->get();

    echo "📋 Found " . $orderItems->count() . " order items to fix...\n\n";

    if ($orderItems->isEmpty()) {
        echo "✅ No order items need fixing. All subtotals are already populated.\n";
        exit;
    }

    $fixedCount = 0;
    
    foreach ($orderItems as $item) {
        // Calculate subtotal: (price * quantity) - discount
        $subtotal = ($item->price * $item->quantity) - ($item->discount ?? 0);
        
        // Update the record
        DB::table('order_items')
            ->where('id', $item->id)
            ->update(['subtotal' => $subtotal]);
        
        $fixedCount++;
        echo "✅ Fixed order item #{$item->id}: Subtotal = Rs. " . number_format($subtotal, 2) . "\n";
    }
    
    echo "\n📊 SUMMARY:\n";
    echo "===========\n";
    echo "Total items fixed: {$fixedCount}\n";
    echo "✅ All order items now have proper subtotal values!\n\n";
    
    echo "🎉 ORDER ITEMS TABLE FIXED SUCCESSFULLY!\n";
    echo "Now you can access order details without database errors.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
