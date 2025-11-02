<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationMessageService;

class OrderController extends Controller
{
    private $notificationService;

    public function __construct(NotificationMessageService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    //  Show All Orders with Search & Filtering
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

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Get per_page from request, default to 10 if not provided
        $perPage = $request->get('per_page', 10);
        
        // Validate per_page value (only allow specific values)
        $allowedPerPage = [5, 10, 15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get order statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'ready' => Order::where('status', 'ready')->count(),
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

        $orders = $query->with('products')->latest()->paginate($perPage);
        
        // Append query parameters to pagination links
        $orders->appends($request->query());
        
        return view('admin.orders.index', compact('orders', 'stats', 'monthlySales'));
    }

    //  Show Create Order Form
    public function create()
    {
        $products = Product::all();
        return view('admin.orders.create', compact('products'));
    }

    //  Store Order in Database
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255|regex:/^[A-Za-z\s]+$/',
            'customer_phone' => ['required', 'string', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/'],
            'delivery_address' => 'required|string|max:255',
            'payment_method' => 'required|in:cash,online',
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
            
            $itemSubtotal = $product->price * $quantity;
            $order->products()->attach($product->id, [
                'quantity' => $quantity,
                'price' => $product->price,
                'discount' => $discount,
                'product_name' => $product->name,
                'subtotal' => $itemSubtotal,
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

        // Send order confirmation notification to customer
        try {
            $this->notificationService->sendOrderReceived(
                $order->user_id, 
                $order->id, 
                $order->total_amount
            );
            
            // Send new order alert to admin
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                $this->notificationService->sendNewOrderAlert(
                    $admin->id,
                    $order->id,
                    $order->customer_name,
                    $order->total_amount
                );
            }
            
            $message = 'Order placed successfully and notifications sent!';
        } catch (\Exception $e) {
            \Log::error("Failed to send order notifications for order {$order->id}: " . $e->getMessage());
            $message = 'Order placed successfully but notifications failed to send.';
        }

        return redirect()->route('orders.show', $order)
            ->with('success', $message);
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
            'payment_method' => 'required|in:cash,online',
            'status' => 'required|in:pending,processing,ready,completed,cancelled',
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

        // Store original status for notification comparison
        $originalStatus = $order->status;
        
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
            
            $itemSubtotal = $product->price * $quantity;
            $order->products()->attach($product->id, [
                'quantity' => $quantity,
                'price' => $product->price,
                'discount' => $discount,
                'product_name' => $product->name,
                'subtotal' => $itemSubtotal,
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

        // Send notification if status changed and user exists
        try {
            if ($originalStatus !== $validated['status'] && $order->user_id) {
                \Log::info("Order status changed from {$originalStatus} to {$validated['status']} for order {$order->id}");
                
                switch ($validated['status']) {
                    case 'processing':
                        $this->notificationService->sendOrderProcessing($order->user_id, $order->id);
                        break;
                    case 'ready':
                        $deliveryType = $order->payment_method === 'cash' ? 'pickup' : 'delivery';
                        $this->notificationService->sendOrderReady($order->user_id, $order->id, $deliveryType);
                        break;
                    case 'completed':
                        $this->notificationService->sendOrderCompleted($order->user_id, $order->id);
                        break;
                    case 'cancelled':
                        $this->notificationService->sendOrderCancelled($order->user_id, $order->id, 'Updated by admin');
                        break;
                }
                
                \Log::info("Notification sent for order {$order->id} status change to {$validated['status']}");
            }
        } catch (\Exception $e) {
            \Log::error("Failed to send notification for order {$order->id}: " . $e->getMessage());
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
            'status' => 'required_if:action,status|in:pending,processing,ready,completed,cancelled',
            'payment_status' => 'required_if:action,payment_status|in:pending,paid,failed,refunded',
            'priority' => 'required_if:action,priority|in:1,2,3'
        ]);

        if ($request->action === 'delete') {
            Order::whereIn('id', $request->order_ids)->delete();
            return redirect()->back()->with('success', 'Selected orders deleted successfully.');
        }

        if ($request->action === 'status') {
            $orders = Order::whereIn('id', $request->order_ids)->get();
            $notificationCount = 0;
            
            foreach ($orders as $order) {
                $order->update(['status' => $request->status]);
                
                // Send notification for each order
                try {
                    switch ($request->status) {
                        case 'pending':
                            $this->notificationService->sendOrderReceived($order->user_id, $order->id, $order->total_amount);
                            break;
                        case 'processing':
                            $this->notificationService->sendOrderProcessing($order->user_id, $order->id);
                            break;
                        case 'ready':
                            $this->notificationService->sendOrderReady($order->user_id, $order->id, 'delivery');
                            break;
                        case 'completed':
                            $this->notificationService->sendOrderCompleted($order->user_id, $order->id);
                            break;
                        case 'cancelled':
                            $this->notificationService->sendOrderCancelled($order->user_id, $order->id, 'Bulk status update');
                            break;
                    }
                    $notificationCount++;
                } catch (\Exception $e) {
                    \Log::error("Failed to send notification for order {$order->id}: " . $e->getMessage());
                }
            }
            
            return redirect()->back()->with('success', "Selected orders status updated successfully. {$notificationCount} notifications sent.");
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
            'status' => 'required|in:pending,processing,ready,completed,cancelled',
            'delivery_type' => 'sometimes|in:pickup,delivery',
            'cancellation_reason' => 'required_if:status,cancelled'
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Prepare update data - only include fields that are provided
        $updateData = ['status' => $newStatus];
        
        if ($request->filled('delivery_type')) {
            $updateData['delivery_type'] = $request->delivery_type;
        }
        
        if ($request->filled('cancellation_reason')) {
            $updateData['cancellation_reason'] = $request->cancellation_reason;
        }
        
        // Update order status
        $order->update($updateData);

        // Send appropriate notification based on status change
        try {
            switch ($newStatus) {
                case 'pending':
                    // Order received notification
                    $this->notificationService->sendOrderReceived(
                        $order->user_id, 
                        $order->id, 
                        $order->total_amount
                    );
                    break;

                case 'processing':
                    // Order is being prepared
                    $this->notificationService->sendOrderProcessing(
                        $order->user_id, 
                        $order->id
                    );
                    break;

                case 'ready':
                    // Order ready for pickup/delivery
                    $deliveryType = $request->delivery_type ?? 'pickup';
                    $this->notificationService->sendOrderReady(
                        $order->user_id, 
                        $order->id, 
                        $deliveryType
                    );
                    break;

                case 'completed':
                    // Order delivered/completed
                    $this->notificationService->sendOrderCompleted(
                        $order->user_id, 
                        $order->id
                    );
                    break;

                case 'cancelled':
                    // Order cancelled
                    $reason = $request->cancellation_reason ?? 'Order cancelled by admin';
                    $this->notificationService->sendOrderCancelled(
                        $order->user_id, 
                        $order->id, 
                        $reason
                    );
                    break;
            }

            $notificationSent = true;
            $notificationMessage = "Order status updated and customer notified successfully.";
            
        } catch (\Exception $e) {
            // Log notification error but don't fail the status update
            \Log::error("Failed to send notification for order {$order->id}: " . $e->getMessage());
            $notificationSent = false;
            $notificationMessage = "Order status updated but notification failed to send.";
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $notificationMessage,
                'status' => $order->formatted_status,
                'notification_sent' => $notificationSent
            ]);
        }

        return redirect()->back()->with('success', $notificationMessage);
    }
}
