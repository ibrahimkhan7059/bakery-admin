<?php

namespace App\Http\Controllers;

use App\Models\BulkOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkOrderController extends Controller
{
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

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('delivery_date', [$request->start_date, $request->end_date]);
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

        $orders = $query->with(['items', 'user'])->latest()->paginate(10);
        
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
            'delivery_date' => 'required|date|after_or_equal:today',
            'delivery_time' => 'nullable|date_format:H:i',
            'order_type' => 'required|in:birthday,party,corporate,other',
            'event_details' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
            'advance_payment' => 'nullable|numeric|min:0',
            'special_instructions' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string|max:500',
        ], [
            'customer_phone.regex' => 'Please enter a valid Pakistani phone number (e.g., 03001234567 or +923001234567)',
            'delivery_date.after_or_equal' => 'Delivery date must be today or a future date',
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
            $bulkOrder->advance_payment = $validated['advance_payment'] ?? 0;
            $bulkOrder->special_instructions = $validated['special_instructions'];
            $bulkOrder->status = 'pending';
            $bulkOrder->payment_status = $validated['advance_payment'] > 0 ? 'partial' : 'pending';
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
        if ($bulkOrder->status === 'completed') {
            return redirect()->route('bulk-orders.show', $bulkOrder)
                ->with('error', 'Cannot edit a completed order.');
        }

        $products = Product::all();
        return view('admin.bulk-orders.edit', compact('bulkOrder', 'products'));
    }

    public function update(Request $request, BulkOrder $bulkOrder)
    {
        if ($bulkOrder->status === 'completed') {
            return redirect()->route('bulk-orders.show', $bulkOrder)
                ->with('error', 'Cannot update a completed order.');
        }

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'string', 'regex:/^(03[0-9]{9}|\+923[0-9]{9})$/'],
            'customer_email' => 'nullable|email|max:255',
            'delivery_address' => 'required|string|max:500',
            'delivery_date' => 'required|date',
            'delivery_time' => 'nullable|date_format:H:i',
            'order_type' => 'required|in:birthday,party,corporate,other',
            'event_details' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
            'advance_payment' => 'nullable|numeric|min:0',
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
        if ($request->method() !== 'POST') {
            return response()->json([
                'error' => 'Method not allowed. Please use POST method.'
            ], 405);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,completed,cancelled'
        ]);

        $bulkOrder->update(['status' => $request->status]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bulk order status updated successfully.',
                'status' => $bulkOrder->formatted_status
            ]);
        }

        return redirect()->back()->with('success', 'Bulk order status updated successfully.');
    }

    /**
     * Display the invoice for a bulk order.
     */
    public function invoice(BulkOrder $bulkOrder)
    {
        return view('admin.bulk-orders.invoice', compact('bulkOrder'));
    }
}