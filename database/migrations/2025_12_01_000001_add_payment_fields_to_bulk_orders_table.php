<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bulk_orders', function (Blueprint $table) {
            $table->string('basket_id')->nullable()->after('payment_method');
            $table->string('transaction_id')->nullable()->after('basket_id');
            $table->timestamp('payment_date')->nullable()->after('transaction_id');
            $table->text('payment_error')->nullable()->after('payment_date');
        });
    }

    public function down()
    {
        Schema::table('bulk_orders', function (Blueprint $table) {
            $table->dropColumn(['basket_id', 'transaction_id', 'payment_date', 'payment_error']);
        });
    }
};
