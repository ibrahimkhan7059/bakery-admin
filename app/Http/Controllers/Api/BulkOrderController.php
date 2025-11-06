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
        // Debug logging
        \Log::info('Bulk Order API Request Data:', $request->all());
        \Log::info('Payment Method Received:', ['payment_method' => $request->payment_method]);
        
        $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'delivery_date' => ['required', 'date'],
            'delivery_address' => ['required', 'string'],
            'payment_method' => ['required', 'in:cash,online'],
            'advance_payment' => ['required', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            // Check stock for all items (temporarily disabled for testing)
            /* 
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }
            }
            */

            // Generate order number
            $orderNumber = 'BULK-' . strtoupper(uniqid());

            // Create bulk order
            $bulkOrder = BulkOrder::create([
                'order_number' => $orderNumber,
                'user_id' => $request->user() ? $request->user()->id : 1, // Default to user ID 1 if no auth
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'delivery_address' => $request->delivery_address,
                'delivery_date' => $request->delivery_date,
                'delivery_time' => $request->delivery_time,
                'order_type' => $request->order_type ?? 'other',
                'event_details' => $request->event_details,
                'total_amount' => $request->total_amount,
                'payment_method' => $request->payment_method,
                'advance_payment' => $request->advance_payment,
                'status' => $request->status ?? 'pending',
                'payment_status' => $request->payment_status ?? 'pending',
                'special_instructions' => $request->special_instructions,
            ]);

            // Create bulk order items (stock decrement temporarily disabled)
            foreach ($request->items as $item) {
                $bulkOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'] ?? '',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount' => $item['discount'] ?? 0
                ]);

                // Product::find($item['product_id'])->decrement('stock', $item['quantity']);
            }

            DB::commit();

            // Refresh the bulk order to get the latest data with items
            $bulkOrder->refresh();
            $bulkOrder->load('items');

            return response()->json([
                'message' => 'Bulk order created successfully',
                'order' => $bulkOrder
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function index(Request $request)
    {
        $userId = $request->user() ? $request->user()->id : 1; // Default to user ID 1 if no auth
        
        $orders = BulkOrder::where('user_id', $userId)
            ->with('items')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    public function show(Request $request, BulkOrder $bulkOrder)
    {
        $userId = $request->user() ? $request->user()->id : 1; // Default to user ID 1 if no auth
        
        if ($bulkOrder->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($bulkOrder->load('items'));
    }

    public function updateStatus(Request $request, BulkOrder $bulkOrder)
    {
        $userId = $request->user() ? $request->user()->id : 1; // Default to user ID 1 if no auth
        
        if ($bulkOrder->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => ['required', 'in:pending,processing,ready,completed,cancelled']
        ]);

        $bulkOrder->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $bulkOrder->load('items.product')
        ]);
    }

    public function destroy(Request $request, BulkOrder $bulkOrder)
    {
        $userId = $request->user() ? $request->user()->id : 1; // Default to user ID 1 if no auth
        
        if ($bulkOrder->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Debug: Log the order details
        \Log::info('Delete attempt for order: ', [
            'order_id' => $bulkOrder->id,
            'created_at' => $bulkOrder->created_at,
            'status' => $bulkOrder->status,
            'user_id' => $bulkOrder->user_id,
            'request_user_id' => $userId
        ]);

        // Check if order can be deleted (within 2 days of creation)
        $orderCreatedAt = $bulkOrder->created_at;
        $twoDaysFromCreation = $orderCreatedAt->copy()->addDays(2); // Use copy() to avoid modifying original
        $now = now();

        \Log::info('Time check: ', [
            'created_at' => $orderCreatedAt,
            'two_days_from_creation' => $twoDaysFromCreation,
            'now' => $now,
            'can_delete_by_time' => $now->lt($twoDaysFromCreation)
        ]);

        if ($now->gt($twoDaysFromCreation)) {
            return response()->json([
                'message' => 'Order cannot be deleted. You can only delete orders within 2 days of creation.',
                'error' => 'deletion_not_allowed',
                'debug' => [
                    'created_at' => $orderCreatedAt,
                    'deadline' => $twoDaysFromCreation,
                    'now' => $now
                ]
            ], 403);
        }

        // Check if order status allows deletion
        if (in_array($bulkOrder->status, ['processing', 'completed'])) {
            return response()->json([
                'message' => 'Order cannot be deleted. Order is already being processed or completed.',
                'error' => 'status_not_allowed',
                'debug' => [
                    'current_status' => $bulkOrder->status
                ]
            ], 403);
        }

        try {
            DB::beginTransaction();

            // Restore product stock (if stock tracking is enabled)
            foreach ($bulkOrder->items as $item) {
                // Product::find($item->product_id)->increment('stock', $item->quantity);
            }

            // Delete the order
            $bulkOrder->delete();

            DB::commit();

            \Log::info('Order deleted successfully: ' . $bulkOrder->id);

            return response()->json([
                'message' => 'Order deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}