<?php

namespace App\Http\Controllers;

use App\Models\BulkOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationMessageService;

class BulkOrderController extends Controller
{
    private $notificationService;

    public function __construct(NotificationMessageService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = BulkOrder::query();

        // Apply filters
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('order_type') && $request->order_type != 'all') {
            $query->where('order_type', $request->order_type);
        }

        if ($request->filled('payment_status') && $request->payment_status != 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
            });
        }

        // Get per_page from request, default to 10 if not provided
        $perPage = $request->get('per_page', 10);
        
        // Validate per_page value (only allow specific values)
        $allowedPerPage = [5, 10, 15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        // Get statistics
        $stats = [
            'total' => BulkOrder::count(),
            'pending' => BulkOrder::where('status', 'pending')->count(),
            'confirmed' => BulkOrder::where('status', 'confirmed')->count(),
            'processing' => BulkOrder::where('status', 'processing')->count(),
            'completed' => BulkOrder::where('status', 'completed')->count(),
            'cancelled' => BulkOrder::where('status', 'cancelled')->count(),
        ];

        $orders = $query->with(['items', 'user'])->latest()->paginate($perPage);
        
        // Append query parameters to pagination links
        $orders->appends($request->query());
        
        return view('admin.bulk-orders.index', compact('orders', 'stats'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('admin.bulk-orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'string', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/'],
            'customer_email' => 'nullable|email|max:255',
            'delivery_address' => 'required|string|max:500',
            'delivery_date' => 'required|date|after:' . now()->addDays(4)->format('Y-m-d'),
            'delivery_time' => 'nullable|date_format:H:i',
            'order_type' => 'required|in:birthday,party,corporate,other',
            'event_details' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash,online',
            'payment_status' => 'required|in:pending,partial,paid',
            'advance_payment' => 'nullable|numeric|min:0',
            'special_instructions' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string|max:500',
        ], [
            'customer_phone.regex' => 'Please enter a valid Pakistani phone number (e.g., 03001234567 or +923001234567)',
            'delivery_date.after' => 'Delivery date must be at least 5 days from today. Orders require advance notice for proper preparation.',
            'products.min' => 'Please add at least one product to the order',
        ]);

        try {
            DB::beginTransaction();

            // Check stock availability
            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                if ($product->stock < $productData['quantity']) {
                    return back()->withErrors([
                        'stock' => "Insufficient stock for {$product->name}. Available: {$product->stock}"
                    ])->withInput();
                }
            }

            // Create bulk order
            $bulkOrder = new BulkOrder();
            $bulkOrder->user_id = auth()->id();
            $bulkOrder->customer_name = $validated['customer_name'];
            $bulkOrder->customer_phone = $validated['customer_phone'];
            $bulkOrder->customer_email = $validated['customer_email'];
            $bulkOrder->delivery_address = $validated['delivery_address'];
            $bulkOrder->delivery_date = $validated['delivery_date'];
            $bulkOrder->delivery_time = $validated['delivery_time'];
            $bulkOrder->order_type = $validated['order_type'];
            $bulkOrder->event_details = $validated['event_details'];
            $bulkOrder->payment_method = $validated['payment_method'];
            $bulkOrder->payment_status = $validated['payment_status'];
            $bulkOrder->advance_payment = $validated['advance_payment'] ?? 0;
            $bulkOrder->special_instructions = $validated['special_instructions'];
            $bulkOrder->status = 'pending';
            $bulkOrder->total_amount = 0; // Initialize with 0, will be updated after items are added
            $bulkOrder->save();

            $totalAmount = 0;

            // Attach products and update stock
            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                $quantity = $productData['quantity'];
                
                // Calculate discount based on quantity
                $discount = 0;
                if ($quantity >= 50) {
                    $discount = 0.15; // 15% discount for 50+ items
                } elseif ($quantity >= 25) {
                    $discount = 0.10; // 10% discount for 25+ items
                } elseif ($quantity >= 10) {
                    $discount = 0.05; // 5% discount for 10+ items
                }

                $itemTotal = ($product->price * $quantity) * (1 - $discount);
                $totalAmount += $itemTotal;

                $bulkOrder->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'discount' => $discount,
                    'notes' => $productData['notes'] ?? null,
                ]);

                // Update product stock
                $product->stock -= $quantity;
                $product->save();
            }

            // Update total amount
            $bulkOrder->total_amount = $totalAmount;
            $bulkOrder->save();

            DB::commit();

            return redirect()->route('bulk-orders.show', $bulkOrder)
                ->with('success', 'Bulk order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk Order Creation Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error creating bulk order. Please try again.'])->withInput();
        }
    }

    public function show(BulkOrder $bulkOrder)
    {
        $bulkOrder->load(['items.product', 'user']);
        return view('admin.bulk-orders.show', compact('bulkOrder'));
    }

    public function edit(BulkOrder $bulkOrder)
    {
        $products = Product::all();
        return view('admin.bulk-orders.edit', compact('bulkOrder', 'products'));
    }

    public function update(Request $request, BulkOrder $bulkOrder)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'string', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/'],
            'customer_email' => 'nullable|email|max:255',
            'delivery_address' => 'required|string|max:500',
            'delivery_date' => 'required|date',
            'delivery_time' => 'nullable|date_format:H:i',
            'order_type' => 'required|in:birthday,party,corporate,other',
            'event_details' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash,online',
            'advance_payment' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,processing,ready,completed,cancelled',
            'payment_status' => 'required|in:pending,partial,paid',
            'special_instructions' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string|max:500',
        ], [
            'customer_phone.regex' => 'Please enter a valid Pakistani phone number (e.g., 03001234567 or +923001234567)',
            'products.min' => 'Please add at least one product to the order',
        ]);

        try {
            DB::beginTransaction();

            // Check stock availability for new quantities
            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                $existingItem = $bulkOrder->items()->where('product_id', $product->id)->first();
                $quantityDifference = $productData['quantity'] - ($existingItem ? $existingItem->quantity : 0);
                
                if ($product->stock < $quantityDifference) {
                    return back()->withErrors([
                        'stock' => "Insufficient stock for {$product->name}. Available: {$product->stock}"
                    ])->withInput();
                }
            }

            // Update bulk order details
            // Store old status for notification comparison
            $oldStatus = $bulkOrder->status;
            
            $bulkOrder->customer_name = $validated['customer_name'];
            $bulkOrder->customer_phone = $validated['customer_phone'];
            $bulkOrder->customer_email = $validated['customer_email'];
            $bulkOrder->delivery_address = $validated['delivery_address'];
            $bulkOrder->delivery_date = $validated['delivery_date'];
            $bulkOrder->delivery_time = $validated['delivery_time'];
            $bulkOrder->order_type = $validated['order_type'];
            $bulkOrder->event_details = $validated['event_details'];
            $bulkOrder->payment_method = $validated['payment_method'];
            $bulkOrder->advance_payment = $validated['advance_payment'] ?? 0;
            $bulkOrder->status = $validated['status'];
            $bulkOrder->payment_status = $validated['payment_status'];
            $bulkOrder->special_instructions = $validated['special_instructions'];
            // Save direct amounts from app if provided
            if (isset($validated['total_amount'])) {
                $bulkOrder->total_amount = $validated['total_amount'];
            }
            if (isset($validated['discount_amount'])) {
                $bulkOrder->discount_amount = $validated['discount_amount'];
            }
            if (isset($validated['final_amount'])) {
                $bulkOrder->final_amount = $validated['final_amount'];
            }
            $bulkOrder->save();

            // Handle products
            $totalAmount = 0;
            $existingProductIds = [];

            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                $quantity = $productData['quantity'];
                $existingItem = $bulkOrder->items()->where('product_id', $product->id)->first();
                
                // Calculate discount based on quantity
                $discount = 0;
                if ($quantity >= 50) {
                    $discount = 0.15; // 15% discount for 50+ items
                } elseif ($quantity >= 25) {
                    $discount = 0.10; // 10% discount for 25+ items
                } elseif ($quantity >= 10) {
                    $discount = 0.05; // 5% discount for 10+ items
                }

                $itemTotal = ($product->price * $quantity) * (1 - $discount);
                $totalAmount += $itemTotal;

                if ($existingItem) {
                    // Update existing item
                    $quantityDifference = $quantity - $existingItem->quantity;
                    $existingItem->update([
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'discount' => $discount,
                        'notes' => $productData['notes'] ?? null,
                    ]);

                    // Update product stock
                    $product->stock -= $quantityDifference;
                    $product->save();
                } else {
                    // Create new item
                    $bulkOrder->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'discount' => $discount,
                        'notes' => $productData['notes'] ?? null,
                    ]);

                    // Update product stock
                    $product->stock -= $quantity;
                    $product->save();
                }

                $existingProductIds[] = $product->id;
            }

            // Remove items that are no longer in the order
            $removedItems = $bulkOrder->items()->whereNotIn('product_id', $existingProductIds)->get();
            foreach ($removedItems as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->stock += $item->quantity;
                    $product->save();
                }
                $item->delete();
            }

            // Update total amount
            $bulkOrder->total_amount = $totalAmount;
            $bulkOrder->save();

            // Send notification if status changed
            if ($oldStatus !== $validated['status']) {
                try {
                    $userId = $bulkOrder->user_id;
                    
                    // If user_id is null or admin (1), try to find user by email
                    if (!$userId || $userId == 1) {
                        if ($bulkOrder->customer_email) {
                            $user = \App\Models\User::where('email', $bulkOrder->customer_email)->first();
                            if ($user) {
                                $userId = $user->id;
                                // DON'T update user_id as it causes orders to disappear from user's list
                                // $bulkOrder->update(['user_id' => $userId]);
                                \Log::info("Found user {$userId} for notifications, but keeping original user_id {$bulkOrder->user_id}");
                            }
                        }
                    }
                    
                    if ($userId) {
                        switch ($validated['status']) {
                            case 'processing':
                                $this->notificationService->sendBulkOrderUpdate(
                                    $userId, $bulkOrder->id, 'processing',
                                    'Great news! Your bulk order is now being prepared by our team.'
                                );
                                break;
                            case 'ready':
                                $this->notificationService->sendBulkOrderUpdate(
                                    $userId, $bulkOrder->id, 'ready',
                                    'Your bulk order is ready! Please arrange for pickup or delivery.'
                                );
                                break;
                            case 'completed':
                                $this->notificationService->sendBulkOrderUpdate(
                                    $userId, $bulkOrder->id, 'completed',
                                    'Your bulk order has been completed successfully! Thank you for your business.'
                                );
                                break;
                            case 'cancelled':
                                $this->notificationService->sendBulkOrderUpdate(
                                    $userId, $bulkOrder->id, 'cancelled',
                                    'We\'re sorry! Your bulk order has been cancelled. Please contact us for details.'
                                );
                                break;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Notification Error: ' . $e->getMessage());
                    // Don't fail the update if notification fails
                }
            }

            DB::commit();

            return redirect()->route('bulk-orders.show', $bulkOrder)
                ->with('success', 'Bulk order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk Order Update Error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error updating bulk order. Please try again.'])->withInput();
        }
    }

    public function destroy(BulkOrder $bulkOrder)
    {
        if ($bulkOrder->status === 'completed') {
            return redirect()->route('bulk-orders.show', $bulkOrder)
                ->with('error', 'Cannot delete a completed order.');
        }

        try {
            DB::beginTransaction();

            // Return stock for all items
            foreach ($bulkOrder->items as $item) {
                $product = $item->product;
                $product->stock += $item->quantity;
                $product->save();
            }

            $bulkOrder->delete();

            DB::commit();

            return redirect()->route('bulk-orders.index')
                ->with('success', 'Bulk order deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error deleting bulk order: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, BulkOrder $bulkOrder)
    {
        \Log::info('updateStatus called for order ' . $bulkOrder->id . ' with status: ' . ($request->status ?? 'null'));
        
        // Allow both POST and PUT methods
        if (!in_array($request->method(), ['POST', 'PUT'])) {
            \Log::error('Wrong method: ' . $request->method());
            return response()->json([
                'error' => 'Method not allowed. Please use POST or PUT method.'
            ], 405);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,ready,completed,cancelled',
            'total_price' => 'sometimes|numeric|min:0',
            'delivery_date' => 'sometimes|date|after:today'
        ]);

        $newStatus = $request->status;
        
        // Prepare update data - only include fields that are provided
        $updateData = ['status' => $newStatus];
        
        if ($request->filled('total_price')) {
            $updateData['total_amount'] = $request->total_price;
        }
        
        if ($request->filled('delivery_date')) {
            $updateData['delivery_date'] = $request->delivery_date;
        }
        
        // Update bulk order
        $bulkOrder->update($updateData);

        // Send appropriate notification based on status
        try {
            // Get the correct user_id (either from order or find by email)
            $userId = $bulkOrder->user_id;
            
            // If user_id is null or admin (1), try to find user by email
            if (!$userId || $userId == 1) {
                if ($bulkOrder->customer_email) {
                    $user = \App\Models\User::where('email', $bulkOrder->customer_email)->first();
                    if ($user) {
                        $userId = $user->id;
                        // DON'T update user_id as it causes orders to disappear from user's list
                        // $bulkOrder->update(['user_id' => $userId]);
                        \Log::info("Found user {$userId} for bulk order {$bulkOrder->id} via email, but keeping original user_id {$bulkOrder->user_id}");
                    }
                }
            }
            
            if (!$userId) {
                \Log::warning("No valid user_id found for bulk order {$bulkOrder->id}, cannot send notification");
                $notificationMessage = 'Bulk order status updated but no valid user found for notification.';
            } else {
                switch ($newStatus) {
                    case 'pending':
                        // Bulk order received
                        $this->notificationService->sendBulkOrderReceived(
                            $userId, 
                            $bulkOrder->id
                        );
                        break;

                    case 'processing':
                        // Bulk order is being prepared - use simple method call
                        $this->notificationService->sendBulkOrderUpdate(
                            $userId, 
                            $bulkOrder->id, 
                            'processing',
                            'Great news! Your bulk order is now being prepared by our team.'
                        );
                        break;

                    case 'ready':
                        // Bulk order is ready for pickup/delivery
                        $this->notificationService->sendBulkOrderUpdate(
                            $userId, 
                            $bulkOrder->id, 
                            'ready',
                            'Your bulk order is ready! Please arrange for pickup or delivery.'
                        );
                        break;

                    case 'completed':
                        // Bulk order completed
                        $this->notificationService->sendBulkOrderUpdate(
                            $userId, 
                            $bulkOrder->id, 
                            'completed',
                            'Your bulk order has been completed successfully! Thank you for your business.'
                        );
                        break;

                    case 'cancelled':
                        // Bulk order cancelled
                        $this->notificationService->sendBulkOrderUpdate(
                            $userId, 
                            $bulkOrder->id, 
                            'cancelled',
                            'We\'re sorry! Your bulk order has been cancelled. Please contact us for details.'
                        );
                        break;
                }
                
                $notificationMessage = 'Bulk order status updated and customer notified successfully.';
            }

            $notificationMessage = 'Bulk order status updated and customer notified successfully.';
            
        } catch (\Exception $e) {
            \Log::error("Failed to send bulk order notification for order {$bulkOrder->id}: " . $e->getMessage());
            $notificationMessage = 'Bulk order status updated but notification failed to send.';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $notificationMessage,
                'status' => $bulkOrder->formatted_status
            ]);
        }

        return redirect()->back()->with('success', $notificationMessage);
    }

    /**
     * Display the invoice for a bulk order.
     */
    public function invoice(BulkOrder $bulkOrder)
    {
        return view('admin.bulk-orders.invoice', compact('bulkOrder'));
    }
}