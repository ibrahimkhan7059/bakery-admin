<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    // ✅ Show All Orders with Search & Filtering
    public function index(Request $request)
    {
        $query = Order::query();

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('product', 'like', '%' . $request->search . '%');
            });
        }

        $orders = $query->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    // ✅ Show Create Order Form
    public function create()
    {
        return view('admin.orders.create');
    }

    // ✅ Store Order in Database
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:15',
            'product' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
        ]);

        Order::create([
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'product' => $request->product,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'total_price' => $request->quantity * $request->price,
            'status' => 'pending', // Default status
        ]);

        return redirect()->route('orders.index')->with('success', 'Order added successfully!');
    }

    // ✅ Show Edit Order Form
    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }

    // ✅ Update Order Status
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order->update([
            'status' => $request->status,
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
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    
    
}
