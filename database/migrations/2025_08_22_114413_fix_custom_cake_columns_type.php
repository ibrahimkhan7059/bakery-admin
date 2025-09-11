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
            // Change enum columns to string to allow any values
            $table->string('cake_flavor')->change();
            $table->string('cake_filling')->change();
            $table->string('cake_frosting')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_cake_orders', function (Blueprint $table) {
            // Revert back to enum if needed
            $table->enum('cake_flavor', ['Vanilla', 'Chocolate', 'Red Velvet', 'Strawberry'])->change();
            $table->enum('cake_filling', ['Chocolate', 'Vanilla Custard', 'Fruit', 'Nutella'])->change();
            $table->enum('cake_frosting', ['Buttercream', 'Fondant', 'Whipped Cream', 'Cream Cheese'])->change();
        });
    }
};
