<?php

namespace App\Http\Controllers;

use App\Models\CustomCakeOrder;
use App\Models\User;
use App\Http\Requests\CustomCakeOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomCakeOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CustomCakeOrder::query();

        // Apply filters
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cake_flavor', 'like', '%' . $request->search . '%')
                  ->orWhere('cake_size', 'like', '%' . $request->search . '%')
                  ->orWhere('special_instructions', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($request) {
                      $userQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Get order statistics
        $stats = [
            'total' => CustomCakeOrder::count(),
            'pending' => CustomCakeOrder::where('status', 'pending')->count(),
            'confirmed' => CustomCakeOrder::where('status', 'confirmed')->count(),
            'in_progress' => CustomCakeOrder::where('status', 'in_progress')->count(),
            'completed' => CustomCakeOrder::where('status', 'completed')->count(),
            'cancelled' => CustomCakeOrder::where('status', 'cancelled')->count(),
        ];

        $orders = $query->with('user')->latest()->paginate(10);
        
        return view('admin.custom-cake-orders.index', compact('orders', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role', 'customer')->orderBy('name')->get();
        
        // Fetch cake configuration data
        $cakeSizes = \App\Models\CakeSize::orderBy('id')->get();
        $cakeFlavors = \App\Models\CakeOption::whereHas('group', function($query) {
            $query->where('key', 'flavor');
        })->orderBy('name')->get();
        $cakeFillings = \App\Models\CakeOption::whereHas('group', function($query) {
            $query->where('key', 'filling');
        })->orderBy('name')->get();
        $cakeFrostings = \App\Models\CakeOption::whereHas('group', function($query) {
            $query->where('key', 'frosting');
        })->orderBy('name')->get();
        
        return view('admin.custom-cake-orders.create', compact('users', 'cakeSizes', 'cakeFlavors', 'cakeFillings', 'cakeFrostings'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomCakeOrderRequest $request)
    {
        $validated = $request->validated();

        CustomCakeOrder::create($validated);

        return redirect()->route('custom-cake-orders.index')
            ->with('success', 'Custom cake order created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomCakeOrder $customCakeOrder)
    {
        $customCakeOrder->load('user');
        return view('admin.custom-cake-orders.show', compact('customCakeOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomCakeOrder $customCakeOrder)
    {
        $users = User::where('role', 'customer')->orderBy('name')->get();
        
        // Fetch cake configuration data
        $cakeSizes = \App\Models\CakeSize::orderBy('id')->get();
        $cakeFlavors = \App\Models\CakeOption::whereHas('group', function($query) {
            $query->where('key', 'flavor');
        })->orderBy('name')->get();
        $cakeFillings = \App\Models\CakeOption::whereHas('group', function($query) {
            $query->where('key', 'filling');
        })->orderBy('name')->get();
        $cakeFrostings = \App\Models\CakeOption::whereHas('group', function($query) {
            $query->where('key', 'frosting');
        })->orderBy('name')->get();
        
        return view('admin.custom-cake-orders.edit', compact('customCakeOrder', 'users', 'cakeSizes', 'cakeFlavors', 'cakeFillings', 'cakeFrostings'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomCakeOrderRequest $request, CustomCakeOrder $customCakeOrder)
    {
        $validated = $request->validated();

        $customCakeOrder->update($validated);

        return redirect()->route('custom-cake-orders.show', $customCakeOrder)
            ->with('success', 'Custom cake order updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomCakeOrder $customCakeOrder)
    {
        $customCakeOrder->delete();
        return redirect()->route('custom-cake-orders.index')
            ->with('success', 'Custom cake order deleted successfully!');
    }

    /**
     * Update status of the order
     */
    public function updateStatus(Request $request, CustomCakeOrder $customCakeOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        $customCakeOrder->update(['status' => $validated['status']]);

        return redirect()->back()
            ->with('success', 'Order status updated successfully!');
    }

    /**
     * Bulk update operations
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:custom_cake_orders,id',
            'action' => 'required|in:delete,status',
            'status' => 'required_if:action,status|in:pending,confirmed,in_progress,completed,cancelled',
        ]);

        if ($request->action === 'delete') {
            CustomCakeOrder::whereIn('id', $request->order_ids)->delete();
            return redirect()->back()->with('success', 'Selected orders deleted successfully.');
        }

        if ($request->action === 'status') {
            CustomCakeOrder::whereIn('id', $request->order_ids)->update(['status' => $request->status]);
            return redirect()->back()->with('success', 'Selected orders status updated successfully.');
        }
    }
} 