<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('custom_cake_orders', function (Blueprint $table) {
            // Update enum columns with new options
            $table->enum('cake_flavor', [
                'Vanilla',
                'Chocolate',
                'Red Velvet',
                'Strawberry',
                'Butterscotch',
                'Coffee',
                'Lemon',
                'Blueberry',
                'Pineapple',
                'Mango'
            ])->change();

            $table->enum('cake_filling', [
                'Chocolate',
                'Vanilla Custard',
                'Fresh Cream',
                'Nutella',
                'Strawberry Jam',
                'Caramel',
                'Lemon Curd',
                'Cream Cheese',
                'Fruit Compote',
                'Chocolate Ganache'
            ])->change();

            $table->enum('cake_frosting', [
                'Buttercream',
                'Fondant',
                'Whipped Cream',
                'Cream Cheese',
                'Chocolate',
                'Vanilla',
                'Berry',
                'Caramel',
                'Coffee',
                'Lemon'
            ])->change();
        });
    }

    public function down()
    {
        Schema::table('custom_cake_orders', function (Blueprint $table) {
            // Revert to original enum options
            $table->enum('cake_flavor', ['Vanilla', 'Chocolate', 'Red Velvet', 'Strawberry'])->change();
            $table->enum('cake_filling', ['Chocolate', 'Vanilla Custard', 'Fruit', 'Nutella'])->change();
            $table->enum('cake_frosting', ['Buttercream', 'Fondant', 'Whipped Cream', 'Cream Cheese'])->change();
        });
    }
}; 