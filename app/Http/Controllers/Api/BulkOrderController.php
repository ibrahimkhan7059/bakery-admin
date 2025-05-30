<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BulkOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkOrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/'],
            'delivery_date' => ['required', 'date', 'after:today'],
            'delivery_address' => ['required', 'string'],
            'payment_method' => ['required', 'in:cash,card'],
            'advance_payment' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            DB::beginTransaction();

            // Check stock for all items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
            }

            // Create bulk order
            $bulkOrder = BulkOrder::create([
                'user_id' => $request->user()->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'advance_payment' => $request->advance_payment,
                'status' => 'pending'
            ]);

            // Create bulk order items and update stock
            foreach ($request->items as $item) {
                $bulkOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0
                ]);

                Product::find($item['product_id'])->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Bulk order created successfully',
                'order' => $bulkOrder->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function index(Request $request)
    {
        $orders = BulkOrder::where('user_id', $request->user()->id)
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, BulkOrder $bulkOrder)
    {
        if ($bulkOrder->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($bulkOrder->load('items.product'));
    }

    public function updateStatus(Request $request, BulkOrder $bulkOrder)
    {
        if ($bulkOrder->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled']
        ]);

        $bulkOrder->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $bulkOrder->load('items.product')
        ]);
    }
} 