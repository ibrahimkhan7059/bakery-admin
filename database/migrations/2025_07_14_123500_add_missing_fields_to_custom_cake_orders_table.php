<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('custom_cake_orders', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('price');
            $table->text('delivery_address')->nullable()->after('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_cake_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_date', 'delivery_address']);
        });
    }
}; 