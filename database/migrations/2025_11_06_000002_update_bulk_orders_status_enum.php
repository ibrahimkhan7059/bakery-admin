<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First convert any 'confirmed' status to 'processing'
        DB::table('bulk_orders')
            ->where('status', 'confirmed')
            ->update(['status' => 'processing']);
            
        // Modify the enum values
        DB::statement("ALTER TABLE bulk_orders MODIFY COLUMN status ENUM('pending', 'processing', 'ready', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down()
    {
        // First convert any 'ready' status to 'processing'
        DB::table('bulk_orders')
            ->where('status', 'ready')
            ->update(['status' => 'processing']);
            
        // Restore the original enum values
        DB::statement("ALTER TABLE bulk_orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'processing', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
