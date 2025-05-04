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
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'delivery_address' => 'required|string|max:255',
            'delivery_date' => 'required|date|after_or_equal:today',
            'delivery_time' => 'nullable|date_format:H:i',
            'order_type' => 'required|in:birthday,party,corporate,other',
            'event_details' => 'nullable|string',
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
            'advance_payment' => 'nullable|numeric|min:0',
            'special_instructions' => 'nullable|string',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Check stock availability
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
                if ($product->stock < $productData['quantity']) {
                    return back()->withErrors([
                        'stock' => "Insufficient stock for {$product->name}. Available: {$product->stock}"
                    ]);
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
            $bulkOrder->save();

            // Attach products and update stock
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
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

            // Calculate and update total amount
            $bulkOrder->total_amount = $bulkOrder->calculateTotal();
            $bulkOrder->save();

            DB::commit();

            return redirect()->route('bulk-orders.show', $bulkOrder)
                ->with('success', 'Bulk order created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error creating bulk order: ' . $e->getMessage()]);
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
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'delivery_address' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'delivery_time' => 'nullable|date_format:H:i',
            'order_type' => 'required|in:birthday,party,corporate,other',
            'event_details' => 'nullable|string',
            'payment_method' => 'required|in:cash,gcash,bank_transfer',
            'advance_payment' => 'nullable|numeric|min:0',
            'special_instructions' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,processing,completed,cancelled',
            'payment_status' => 'required|in:pending,partial,paid',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Return stock for existing items
            foreach ($bulkOrder->items as $item) {
                $product = $item->product;
                $product->stock += $item->quantity;
                $product->save();
            }

            // Check stock availability for new quantities
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
                if ($product->stock < $productData['quantity']) {
                    return back()->withErrors([
                        'stock' => "Insufficient stock for {$product->name}. Available: {$product->stock}"
                    ]);
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
            $bulkOrder->special_instructions = $validated['special_instructions'];
            $bulkOrder->status = $validated['status'];
            $bulkOrder->payment_status = $validated['payment_status'];

            // Delete existing items
            $bulkOrder->items()->delete();

            // Create new items and update stock
            foreach ($validated['products'] as $productData) {
                $product = Product::find($productData['id']);
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

            // Calculate and update total amount
            $bulkOrder->total_amount = $bulkOrder->calculateTotal();
            $bulkOrder->save();

            DB::commit();

            return redirect()->route('bulk-orders.show', $bulkOrder)
                ->with('success', 'Bulk order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error updating bulk order: ' . $e->getMessage()]);
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
} 