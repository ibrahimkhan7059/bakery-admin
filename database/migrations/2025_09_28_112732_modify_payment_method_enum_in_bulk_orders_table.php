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
        // First update existing 'gcash' and 'bank_transfer' values to 'online'
        DB::statement("UPDATE bulk_orders SET payment_method = 'online' WHERE payment_method IN ('gcash', 'bank_transfer')");
        
        // Now modify the enum to only have 'cash' and 'online'
        DB::statement("ALTER TABLE bulk_orders MODIFY payment_method ENUM('cash', 'online') DEFAULT 'cash'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE bulk_orders MODIFY payment_method ENUM('cash', 'gcash', 'bank_transfer') DEFAULT 'cash'");
        
        // Convert 'online' back to 'gcash'
        DB::statement("UPDATE bulk_orders SET payment_method = 'gcash' WHERE payment_method = 'online'");
    }
};
