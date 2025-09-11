<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CakeSize;
use App\Models\CakeOptionGroup;
use App\Models\CakeOption;

class CakeConfigSeeder extends Seeder
{
	public function run(): void
	{
		// Sizes with base prices
		$sizes = [
			['name' => '1 Pound', 'base_price' => 1000],
			['name' => '2 Pounds', 'base_price' => 1800],
			['name' => '3 Pounds', 'base_price' => 2500],
			['name' => '5 Pounds', 'base_price' => 4000],
		];
		foreach ($sizes as $size) {
			CakeSize::updateOrCreate(
				['name' => $size['name']],
				['base_price' => $size['base_price']]
			);
		}

		// Option groups
		$groups = [
			'flavor' => [
				'Vanilla','Chocolate','Red Velvet','Strawberry',
				'Butterscotch','Coffee','Lemon','Blueberry','Pineapple','Mango'
			],
			'filling' => [
				'Chocolate','Vanilla Custard','Fresh Cream','Nutella',
				'Strawberry Jam','Caramel','Lemon Curd','Cream Cheese',
				'Fruit Compote','Chocolate Ganache'
			],
			'frosting' => [
				'Buttercream','Fondant','Whipped Cream','Cream Cheese',
				'Chocolate','Vanilla','Berry','Caramel','Coffee','Lemon'
			],
		];

		foreach ($groups as $key => $options) {
			$group = CakeOptionGroup::firstOrCreate(
				['key' => $key],
				['label' => ucfirst($key)]
			);
			foreach ($options as $name) {
				// Random, but stable-ish price buckets
				$price = match ($key) {
					'flavor' => rand(50, 200),
					'filling' => rand(80, 300),
					'frosting' => rand(60, 250),
					default => rand(50, 200),
				};
				CakeOption::updateOrCreate(
					['cake_option_group_id' => $group->id, 'name' => $name],
					['price' => $price]
				);
			}
		}
	}
} 