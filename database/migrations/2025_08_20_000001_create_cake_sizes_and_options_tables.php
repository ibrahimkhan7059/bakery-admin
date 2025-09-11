<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('cake_sizes', function (Blueprint $table) {
			$table->id();
			$table->string('name')->unique();
			$table->decimal('base_price', 10, 2)->default(0);
			$table->timestamps();
		});

		Schema::create('cake_option_groups', function (Blueprint $table) {
			$table->id();
			$table->string('key')->unique(); // flavor, filling, frosting
			$table->string('label');
			$table->timestamps();
		});

		Schema::create('cake_options', function (Blueprint $table) {
			$table->id();
			$table->foreignId('cake_option_group_id')->constrained('cake_option_groups')->onDelete('cascade');
			$table->string('name');
			$table->decimal('price', 10, 2)->default(0);
			$table->timestamps();
			$table->unique(['cake_option_group_id', 'name']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('cake_options');
		Schema::dropIfExists('cake_option_groups');
		Schema::dropIfExists('cake_sizes');
	}
}; 