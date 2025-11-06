<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First update any 'confirmed' status to 'processing'
        DB::table('bulk_orders')
            ->where('status', 'confirmed')
            ->update(['status' => 'processing']);

        // Then modify the enum values
        DB::statement("ALTER TABLE bulk_orders MODIFY COLUMN status ENUM('pending', 'processing', 'ready', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    public function down()
    {
        // In case of rollback, we'll convert 'ready' status to 'processing'
        DB::table('bulk_orders')
            ->where('status', 'ready')
            ->update(['status' => 'processing']);

        DB::statement("ALTER TABLE bulk_orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'processing', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
