<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bulk_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('delivery_address');
            $table->date('delivery_date');
            $table->time('delivery_time')->nullable();
            $table->enum('order_type', ['birthday', 'party', 'corporate', 'other']);
            $table->text('event_details')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->enum('payment_method', ['cash', 'online'])->default('cash');
            $table->decimal('advance_payment', 10, 2)->default(0);
            $table->text('special_instructions')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bulk_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('discount', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bulk_order_items');
        Schema::dropIfExists('bulk_orders');
    }
}; 