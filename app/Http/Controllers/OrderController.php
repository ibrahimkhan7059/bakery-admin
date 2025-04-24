<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ✅ Show All Orders with Search & Filtering
    public function index(Request $request)
    {
        $query = Order::query();

        // Apply filters
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status') && $request->payment_status != 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Get order statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::pending()->count(),
            'processing' => Order::processing()->count(),
            'completed' => Order::completed()->count(),
            'cancelled' => Order::cancelled()->count(),
        ];

        // Get monthly sales data for chart
        $monthlySales = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('SUM(total_price) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        $orders = $query->with('items')->latest()->paginate(10);
        
        return view('admin.orders.index', compact('orders', 'stats', 'monthlySales'));
    }

    // ✅ Show Create Order Form
    public function create()
    {
        $products = Product::all();
        return view('admin.orders.create', compact('products'));
    }

    // ✅ Store Order in Database
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:15',
            'delivery_address' => 'required|string',
            'payment_method' => 'required|in:cash,card,online',
            'priority' => 'required|in:1,2,3',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'delivery_address' => $request->delivery_address,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'priority' => $request->priority,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            $totalPrice = 0;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'notes' => $item['notes'] ?? null,
                ]);
                $totalPrice += $orderItem->total;
            }

            $order->update(['total_price' => $totalPrice]);

            DB::commit();
            return redirect()->route('orders.index')->with('success', 'Order created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    // ✅ Show Edit Order Form
    public function edit(Order $order)
    {
        $products = Product::all();
        return view('admin.orders.edit', compact('order', 'products'));
    }

    // ✅ Update Order Status
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'priority' => 'required|in:1,2,3',
            'notes' => 'nullable|string',
        ]);

        $order->update([
            'status' => $request->status,
            'payment_status' => $request->payment_status,
            'priority' => $request->priority,
            'notes' => $request->notes,
        ]);

        return redirect()->route('orders.index')->with('success', 'Order updated successfully!');
    }

    // ✅ Soft Delete Order (Move to Trash)
    public function destroy(Order $order)
    {
        $order->delete(); // Soft delete
        return redirect()->route('orders.index')->with('success', 'Order moved to trash!');
    }

    // ✅ Show Single Order Details
    public function show(Order $order)
    {
        return view('admin.orders.show', compact('order'));
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'action' => 'required|in:delete,status,payment_status,priority',
            'status' => 'required_if:action,status|in:pending,processing,completed,cancelled',
            'payment_status' => 'required_if:action,payment_status|in:pending,paid,failed,refunded',
            'priority' => 'required_if:action,priority|in:1,2,3'
        ]);

        if ($request->action === 'delete') {
            Order::whereIn('id', $request->order_ids)->delete();
            return redirect()->back()->with('success', 'Selected orders deleted successfully.');
        }

        if ($request->action === 'status') {
            Order::whereIn('id', $request->order_ids)->update(['status' => $request->status]);
            return redirect()->back()->with('success', 'Selected orders status updated successfully.');
        }

        if ($request->action === 'payment_status') {
            Order::whereIn('id', $request->order_ids)->update(['payment_status' => $request->payment_status]);
            return redirect()->back()->with('success', 'Selected orders payment status updated successfully.');
        }

        if ($request->action === 'priority') {
            Order::whereIn('id', $request->order_ids)->update(['priority' => $request->priority]);
            return redirect()->back()->with('success', 'Selected orders priority updated successfully.');
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order->update(['status' => $request->status]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully.',
                'status' => $order->formatted_status
            ]);
        }

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
}
