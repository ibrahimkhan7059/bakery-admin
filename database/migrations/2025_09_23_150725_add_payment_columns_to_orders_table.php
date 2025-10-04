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
            $table->string('basket_id')->nullable();
            $table->string('payment_status_payfast')->default('pending'); // pending, paid, failed, cancelled
            $table->string('payment_method_type')->nullable(); // payfast, cash_on_delivery, etc.
            $table->string('transaction_id')->nullable();
            $table->text('payment_error')->nullable();
            $table->timestamp('payment_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'basket_id',
                'payment_status_payfast',
                'payment_method_type',
                'transaction_id',
                'payment_error',
                'payment_date'
            ]);
        });
    }
};
