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
        Schema::table('orders', function (Blueprint $table) {
            // Customer Information
            $table->string('customer_email')->nullable();
            $table->enum('delivery_type', ['home_delivery', 'self_pickup'])->default('home_delivery');
            $table->string('city')->nullable();
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('delivery_charges', 8, 2)->default(0);
            $table->text('special_notes')->nullable();
            
            // Update existing columns to allow null for guest orders
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_email',
                'delivery_type', 
                'city',
                'subtotal',
                'delivery_charges',
                'special_notes'
            ]);
        });
    }
};
