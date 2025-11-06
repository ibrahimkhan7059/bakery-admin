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
                $itemSubtotal = $item['price'] * $item['quantity'];
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $itemSubtotal,
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
                $itemSubtotal = $item['price'] * $item['quantity'];
                $order->items()->create([
                    'product_id' => $item['id'],
                    'product_name' => $item['name'] ?? 'Product',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $itemSubtotal,
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
        try {
            $user = $request->user();
            $orders = collect();
            
            if ($user) {
                Log::info('Fetching orders for user: ' . $user->id . ' (' . $user->email . ')');
                
                // Get orders for authenticated user
                $authenticatedOrders = Order::where('user_id', $user->id)
                    ->with('items.product')
                    ->latest()
                    ->get();
                
                Log::info('Authenticated orders found: ' . $authenticatedOrders->count());
                
                // Also get guest orders by matching email
                $guestOrders = Order::whereNull('user_id')
                    ->where('customer_email', $user->email)
                    ->with('items.product')
                    ->latest()
                    ->get();
                
                Log::info('Guest orders found: ' . $guestOrders->count());
                
                $orders = $authenticatedOrders->concat($guestOrders)->sortByDesc('created_at');
                Log::info('Total orders combined: ' . $orders->count());
            }
            
            // Transform the data to ensure consistent format
            $formattedOrders = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'customer_email' => $order->customer_email,
                    'customer_phone' => $order->customer_phone,
                    'delivery_type' => $order->delivery_type,
                    'delivery_address' => $order->delivery_address,
                    'special_notes' => $order->special_notes,
                    'total_amount' => $order->total_amount,
                    'subtotal' => $order->subtotal,
                    'delivery_charges' => $order->delivery_charges,
                    'status' => $order->status,
                    'user_id' => $order->user_id,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'items' => $order->items ? $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'subtotal' => $item->subtotal,
                            'product' => $item->product ? [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'price' => $item->product->price,
                            ] : null
                        ];
                    }) : []
                ];
            });

            return response()->json([
                'data' => $formattedOrders->values(),
                'total' => $formattedOrders->count(),
                'user_id' => $user ? $user->id : null,
                'user_email' => $user ? $user->email : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            return response()->json([
                'data' => [],
                'error' => 'Failed to fetch orders'
            ], 500);
        }
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order->load('items.product'));
    }

    public function markAsCompleted(Request $request, Order $order)
    {
        try {
            // Check if the order belongs to the authenticated user
            if ($order->user_id !== $request->user()->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            // Check if the order is in 'ready' status
            if ($order->status !== 'ready') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order must be in ready status to be marked as completed'
                ], 400);
            }

            // Update the order status to completed
            $order->update([
                'status' => 'completed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order marked as completed successfully',
                'order' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking order as completed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark order as completed'
            ], 500);
        }
    }
}