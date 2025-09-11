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
        // Update the enum to include new status values
        DB::statement("ALTER TABLE custom_cake_orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
        
        // Update any existing 'processing' records to 'in_progress'
        DB::table('custom_cake_orders')->where('status', 'processing')->update(['status' => 'in_progress']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update any 'confirmed' or 'in_progress' back to 'processing' before reverting
        DB::table('custom_cake_orders')->where('status', 'confirmed')->update(['status' => 'pending']);
        DB::table('custom_cake_orders')->where('status', 'in_progress')->update(['status' => 'processing']);
        
        // Revert the enum back to original values
        DB::statement("ALTER TABLE custom_cake_orders MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
