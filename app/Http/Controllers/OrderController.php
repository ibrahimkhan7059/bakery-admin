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
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // Get monthly sales data for chart
        $monthlySales = Order::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('SUM(total_amount) as total')
        )
        ->where('status', 'completed')
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

        $orders = $query->with('products')->latest()->paginate(10);
        
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
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255|regex:/^[A-Za-z\s]+$/',
            'customer_phone' => ['required', 'string', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/'],
            'delivery_address' => 'required|string|max:255',
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
            'notes' => 'nullable|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1|max:100',
        ], [
            'customer_name.regex' => 'Customer name can only contain letters and spaces',
            'customer_phone.regex' => 'Please enter a valid phone number',
            'products.min' => 'Please add at least one product to the order',
            'products.*.quantity.max' => 'Maximum quantity per product is 100',
        ]);

        // Check stock availability
        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            if ($product->stock < $productData['quantity']) {
                return back()->withErrors([
                    'stock' => "Insufficient stock for {$product->name}. Available: {$product->stock}"
                ]);
            }
        }

        // Create order
        $order = new Order();
        $order->user_id = auth()->id();
        $order->customer_name = $validated['customer_name'];
        $order->customer_phone = $validated['customer_phone'];
        $order->delivery_address = $validated['delivery_address'];
        $order->status = 'pending';
        $order->payment_status = 'pending';
        $order->payment_method = $validated['payment_method'];
        $order->notes = $validated['notes'];
        $order->save();

        // Attach products with quantities, prices, and discounts
        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            $quantity = $productData['quantity'];
            
            // Calculate discount based on quantity
            $discount = 0;
            if ($quantity >= 10) {
                $discount = 0.10; // 10% discount for 10+ items
            } elseif ($quantity >= 5) {
                $discount = 0.05; // 5% discount for 5+ items
            }
            
            $order->products()->attach($product->id, [
                'quantity' => $quantity,
                'price' => $product->price,
                'discount' => $discount,
                'product_name' => $product->name,
            ]);

            // Update product stock
            $product->stock -= $quantity;
            $product->save();
        }

        // Calculate total and update order
        $order->total_amount = $order->calculateTotal();
        $order->save();
        $order->estimateDeliveryTime();

        // Generate receipt
        $receipt = $this->generateReceipt($order);
        $order->payment_receipt = json_encode($receipt);
        $order->save();

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!');
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
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255|regex:/^[A-Za-z\s]+$/',
            'customer_phone' => ['required', 'string', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/'],
            'delivery_address' => 'required|string|max:255',
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
            'status' => 'required|in:pending,processing,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,failed',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string|max:500',
        ], [
            'customer_name.regex' => 'Customer name can only contain letters and spaces',
            'customer_phone.regex' => 'Please enter a valid Pakistani phone number (e.g., 03001234567 or +923001234567)',
            'products.min' => 'Please add at least one product to the order',
            'products.*.quantity.max' => 'Maximum quantity per product is 100',
        ]);

        // Check stock availability for new quantities
        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            $currentQuantity = $order->products->where('id', $product->id)->first()?->pivot->quantity ?? 0;
            
            if ($product->stock + $currentQuantity < $productData['quantity']) {
                return back()->withErrors([
                    'stock' => "Insufficient stock for {$product->name}. Available: " . ($product->stock + $currentQuantity)
                ]);
            }
        }

        // Update order details
        $order->customer_name = $validated['customer_name'];
        $order->customer_phone = $validated['customer_phone'];
        $order->delivery_address = $validated['delivery_address'];
        $order->payment_method = $validated['payment_method'];
        $order->status = $validated['status'];
        $order->payment_status = $validated['payment_status'];
        $order->notes = $validated['notes'];

        // Return stock for existing products
        foreach ($order->products as $product) {
            $product->stock += $product->pivot->quantity;
            $product->save();
        }

        // Detach all existing products
        $order->products()->detach();

        // Attach updated products
        foreach ($validated['products'] as $productData) {
            $product = Product::find($productData['id']);
            $quantity = $productData['quantity'];
            
            // Calculate discount based on quantity
            $discount = 0;
            if ($quantity >= 10) {
                $discount = 0.10; // 10% discount for 10+ items
            } elseif ($quantity >= 5) {
                $discount = 0.05; // 5% discount for 5+ items
            }
            
            $order->products()->attach($product->id, [
                'quantity' => $quantity,
                'price' => $product->price,
                'discount' => $discount,
                'product_name' => $product->name,
            ]);

            // Update product stock
            $product->stock -= $quantity;
            $product->save();
        }

        // Calculate total and update order
        $order->total_amount = $order->calculateTotal();
        $order->save();

        // Generate new receipt if needed
        if ($order->wasChanged(['total_amount', 'payment_method'])) {
            $receipt = $this->generateReceipt($order);
            $order->payment_receipt = json_encode($receipt);
            $order->save();
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order updated successfully!');
    }

    // ✅ Delete Order
    public function destroy(Order $order)
    {
        // Return stock for all products in the order
        foreach ($order->products as $product) {
            $product->stock += $product->pivot->quantity;
            $product->save();
        }

        $order->delete();
        return redirect()->route('orders.index')
            ->with('success', 'Order deleted successfully!');
    }

    // ✅ Show Single Order Details
    public function show(Order $order)
    {
        $order->load('products');
        
        // Generate receipt if not exists
        if (!$order->payment_receipt) {
            $receipt = $this->generateReceipt($order);
            $order->payment_receipt = json_encode($receipt);
            $order->save();
        } else {
            $receipt = json_decode($order->payment_receipt, true);
        }
        
        return view('admin.orders.show', compact('order', 'receipt'));
    }

    public function printReceipt(Order $order)
    {
        $receipt = $this->generateReceipt($order);
        return view('admin.orders.receipt', compact('order', 'receipt'));
    }

    private function generateReceipt(Order $order)
    {
        return [
            'items' => $order->products->map(function ($product) {
                return [
                    'name' => $product->name,
                    'quantity' => $product->pivot->quantity,
                    'price' => $product->price,
                    'discount' => $product->pivot->discount ?? 0,
                    'total' => ($product->price * $product->pivot->quantity) - ($product->pivot->discount ?? 0),
                    'notes' => $product->pivot->notes ?? null
                ];
            })->toArray(),
            'subtotal' => $order->products->sum(function ($product) {
                return $product->price * $product->pivot->quantity;
            }),
            'discount' => $order->products->sum(function ($product) {
                return $product->pivot->discount ?? 0;
            })
        ];
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
