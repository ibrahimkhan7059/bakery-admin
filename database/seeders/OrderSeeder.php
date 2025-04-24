<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing orders
        Order::truncate();

        // Create sample orders
        $orders = [
            [
                'customer_name' => 'John Doe',
                'customer_phone' => '1234567890',
                'product' => 'Chocolate Cake',
                'quantity' => 2,
                'price' => 25.00,
                'total_price' => 50.00,
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'customer_name' => 'Jane Smith',
                'customer_phone' => '9876543210',
                'product' => 'Red Velvet Cake',
                'quantity' => 1,
                'price' => 30.00,
                'total_price' => 30.00,
                'status' => 'processing',
                'created_at' => Carbon::now()->subDays(3),
            ],
            [
                'customer_name' => 'Mike Johnson',
                'customer_phone' => '5551234567',
                'product' => 'Cheesecake',
                'quantity' => 3,
                'price' => 20.00,
                'total_price' => 60.00,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(1),
            ],
            [
                'customer_name' => 'Sarah Williams',
                'customer_phone' => '4445556666',
                'product' => 'Black Forest Cake',
                'quantity' => 1,
                'price' => 35.00,
                'total_price' => 35.00,
                'status' => 'cancelled',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'customer_name' => 'David Brown',
                'customer_phone' => '7778889999',
                'product' => 'Carrot Cake',
                'quantity' => 2,
                'price' => 22.50,
                'total_price' => 45.00,
                'status' => 'pending',
                'created_at' => Carbon::now(),
            ],
        ];

        foreach ($orders as $order) {
            Order::create($order);
        }

        $this->command->info('Sample orders created successfully!');
    }
}
