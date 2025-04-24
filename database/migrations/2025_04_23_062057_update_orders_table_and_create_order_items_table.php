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
        // Update orders table
        Schema::table('orders', function (Blueprint $table) {
            // Remove old product-related columns
            $table->dropColumn(['product', 'quantity', 'price']);
            
            // Add new columns
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_method')->default('cash');
            $table->string('payment_status')->default('pending');
            $table->integer('priority')->default(2); // 1: High, 2: Medium, 3: Low
        });

        // Create order_items table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop order_items table
        Schema::dropIfExists('order_items');

        // Revert orders table changes
        Schema::table('orders', function (Blueprint $table) {
            $table->string('product');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            
            $table->dropColumn([
                'delivery_address',
                'notes',
                'payment_method',
                'payment_status',
                'priority'
            ]);
        });
    }
};
