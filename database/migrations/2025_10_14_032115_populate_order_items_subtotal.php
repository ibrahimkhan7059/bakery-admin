<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all order_items where subtotal is null or 0
        DB::statement("
            UPDATE order_items 
            SET subtotal = (price * quantity) - COALESCE(discount, 0)
            WHERE subtotal IS NULL OR subtotal = 0
        ");
        
        echo "✅ Updated order_items subtotal values successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset subtotal to null if needed to rollback
        DB::statement("UPDATE order_items SET subtotal = NULL");
    }
};
