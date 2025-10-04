<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Create guest order (for checkout without authentication)
     */
    public function storeGuestOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'delivery_type' => 'required|in:home_delivery,self_pickup',
            'delivery_address' => 'required_if:delivery_type,home_delivery|string|max:500',
            'special_notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric|min:0',
            'delivery_charges' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Create the order
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'delivery_type' => $request->delivery_type,
                'delivery_address' => $request->delivery_address,
                'special_notes' => $request->special_notes,
                'total_amount' => $request->total_amount,
                'subtotal' => $request->subtotal,
                'delivery_charges' => $request->delivery_charges,
                'status' => 'pending',
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($request->items as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show guest order (public access)
     */
    public function showGuestOrder($id)
    {
        try {
            $order = Order::with('items')->findOrFail($id);

            return response()->json([
                'success' => true,
                'order' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        // Debug logging
        \Log::info('Order API Request Data:', $request->all());
        \Log::info('Payment Method Received:', ['payment_method' => $request->payment_method]);
        
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'delivery_type' => 'nullable|in:home_delivery,self_pickup',
            'delivery_address' => 'nullable|string|max:500',
            'special_notes' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|in:cash,online,cash_on_delivery,online_payment',
            'subtotal' => 'nullable|numeric|min:0',
            'delivery_charges' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Map payment method from Flutter format to database format
            $paymentMethodMap = [
                'cash_on_delivery' => 'cash',
                'online_payment' => 'online'
            ];
            $mappedPaymentMethod = $paymentMethodMap[$request->payment_method] ?? $request->payment_method;

            // Create order with all required database fields
            $order = Order::create([
                'user_id' => $request->user()->id ?? null,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'delivery_type' => $request->delivery_type ?? 'home_delivery',
                'delivery_address' => $request->delivery_address,
                'special_notes' => $request->special_notes,
                'subtotal' => $request->subtotal ?? 0,
                'delivery_charges' => $request->delivery_charges ?? 0,
                'total_amount' => $request->total_amount ?? 0,
                'payment_method' => $mappedPaymentMethod ?? 'cash',
                'payment_status' => 'pending',
                'status' => 'pending'
            ]);

            // Create order items
            foreach ($request->items as $item) {
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'] ?? 'Product',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity']
                ]);

                // Update product stock if needed
                $product = Product::find($item['id']);
                if ($product && $product->stock >= $item['quantity']) {
                    $product->decrement('stock', $item['quantity']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order creation failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load('items.product'));
    }
} 