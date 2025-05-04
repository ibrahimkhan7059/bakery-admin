<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Get all products
        $products = Product::all();
        
        // Create 20 test orders
        for ($i = 1; $i <= 20; $i++) {
            // Random user
            $user = User::inRandomOrder()->first();
            
            // Random number of products (1-5)
            $numProducts = rand(1, 5);
            $selectedProducts = $products->random($numProducts);
            
            // Random status and payment status
            $statuses = ['pending', 'processing', 'completed', 'cancelled'];
            $paymentStatuses = ['pending', 'paid', 'failed'];
            $priorities = [1, 2, 3]; // 1 = High, 2 = Medium, 3 = Low
            $paymentMethods = ['cash', 'gcash', 'bank_transfer'];
            
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
            $priority = $priorities[array_rand($priorities)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'customer_name' => "Customer #$i",
                'customer_phone' => '09' . rand(100000000, 999999999),
                'delivery_address' => "Test Address #$i, City",
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'priority' => $priority,
                'notes' => "Test order #$i",
                'created_at' => Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
            ]);
            
            // Attach products with random quantities
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 15);
                $discount = 0;
                
                // Calculate discount based on quantity
                if ($quantity >= 10) {
                    $discount = 0.10; // 10% discount
                } elseif ($quantity >= 5) {
                    $discount = 0.05; // 5% discount
                }
                
                $order->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'discount' => $discount,
                    'product_name' => $product->name,
                ]);
            }
            
            // Calculate total and update order
            $order->total_amount = $order->calculateTotal();
            $order->save();
            
            // Generate receipt
            $receipt = [
                'items' => $order->products->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'quantity' => $product->pivot->quantity,
                        'price' => $product->price,
                        'discount' => $product->pivot->discount ?? 0,
                        'total' => ($product->price * $product->pivot->quantity) - ($product->pivot->discount ?? 0),
                    ];
                })->toArray(),
                'subtotal' => $order->products->sum(function ($product) {
                    return $product->price * $product->pivot->quantity;
                }),
                'discount' => $order->products->sum(function ($product) {
                    return $product->pivot->discount ?? 0;
                })
            ];
            
            $order->payment_receipt = json_encode($receipt);
            $order->save();
            
            // Estimate delivery time for completed orders
            if ($status === 'completed') {
                $order->estimated_delivery_time = Carbon::now()->addHours(rand(1, 24));
                $order->save();
            }
        }
    }
} 