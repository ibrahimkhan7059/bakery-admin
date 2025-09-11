<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_cake_orders', function (Blueprint $table) {
            // Drop existing columns
            $table->dropColumn(['cake_flavor', 'cake_filling', 'cake_frosting']);
        });

        Schema::table('custom_cake_orders', function (Blueprint $table) {
            // Add new columns with updated options
            $table->string('cake_flavor')->default('Vanilla');
            $table->string('cake_filling')->default('Chocolate');
            $table->string('cake_frosting')->default('Buttercream');
        });
    }

    public function down()
    {
        Schema::table('custom_cake_orders', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn(['cake_flavor', 'cake_filling', 'cake_frosting']);
        });

        Schema::table('custom_cake_orders', function (Blueprint $table) {
            // Restore original columns
            $table->enum('cake_flavor', ['Vanilla', 'Chocolate', 'Red Velvet', 'Strawberry']);
            $table->enum('cake_filling', ['Chocolate', 'Vanilla Custard', 'Fruit', 'Nutella']);
            $table->enum('cake_frosting', ['Buttercream', 'Fondant', 'Whipped Cream', 'Cream Cheese']);
        });
    }
}; 