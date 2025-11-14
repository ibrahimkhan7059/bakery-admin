<?php

namespace App\Http\Controllers;

use App\Models\CustomCakeOrder;
use App\Models\User;
use App\Http\Requests\CustomCakeOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationMessageService;

class CustomCakeOrderController extends Controller
{
    private $notificationService;

    public function __construct(NotificationMessageService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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

        // Get per_page from request, default to 10 if not provided
        $perPage = $request->get('per_page', 10);
        
        // Validate per_page to ensure it's a reasonable number
        if (!in_array($perPage, [5, 10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        // Get order statistics
        $stats = [
            'total' => CustomCakeOrder::count(),
            'pending' => CustomCakeOrder::where('status', 'pending')->count(),
            'completed' => CustomCakeOrder::where('status', 'completed')->count(),
            'today' => CustomCakeOrder::whereDate('created_at', today())->count(),
        ];

        $orders = $query->with('user')->latest()->paginate($perPage);
        
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
        try {
            $validated = $request->validated();
            
            // Handle reference image upload if provided
            if ($request->hasFile('reference_image')) {
                $image = $request->file('reference_image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('storage/custom-cake-images'), $imageName);
                $validated['reference_image'] = 'custom-cake-images/' . $imageName;
            }

            $customCakeOrder = CustomCakeOrder::create($validated);

            // Send notifications for new custom cake request
            try {
                // Send confirmation to customer
                $this->notificationService->sendCustomCakeReceived(
                    $customCakeOrder->user_id, 
                    $customCakeOrder->id
                );
                
                // Send alert to admin users
                $adminUsers = User::where('role', 'admin')->get();
                foreach ($adminUsers as $admin) {
                    $this->notificationService->sendCustomCakeAlert(
                        $admin->id,
                        $customCakeOrder->id,
                        $customCakeOrder->user->name ?? 'Customer'
                    );
                }
                
                $message = 'Custom cake order created successfully and notifications sent!';
            } catch (\Exception $e) {
                \Log::error("Failed to send custom cake notifications for order {$customCakeOrder->id}: " . $e->getMessage());
                $message = 'Custom cake order created successfully but notifications failed to send.';
            }

            return redirect()->route('custom-cake-orders.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            \Log::error("Failed to create custom cake order: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create custom cake order. Please try again.');
        }
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
            'admin_message' => 'sometimes|string|max:500',
            'quoted_price' => 'sometimes|numeric|min:0'
        ]);

        $oldStatus = $customCakeOrder->status;
        $newStatus = $validated['status'];
        
        // Prepare update data - only include fields that are provided  
        $updateData = ['status' => $newStatus];
        
        if ($request->filled('admin_message')) {
            $updateData['admin_message'] = $request->admin_message;
        }
        
        if ($request->filled('quoted_price')) {
            $updateData['quoted_price'] = $request->quoted_price;
        }
        
        // Update custom cake order
        $customCakeOrder->update($updateData);

        // Send appropriate notification based on status
        try {
            switch ($newStatus) {
                case 'pending':
                    // Custom cake request received
                    $this->notificationService->sendCustomCakeReceived(
                        $customCakeOrder->user_id, 
                        $customCakeOrder->id
                    );
                    break;

                case 'confirmed':
                    // Custom cake confirmed - use dedicated method
                    $this->notificationService->sendCustomCakeConfirmed(
                        $customCakeOrder->user_id, 
                        $customCakeOrder->id
                    );
                    break;

                case 'in_progress':
                    // Custom cake work started - always send notification
                    if ($request->quoted_price) {
                        // Quote ready with price
                        $this->notificationService->sendCustomCakeQuote(
                            $customCakeOrder->user_id, 
                            $customCakeOrder->id, 
                            $request->quoted_price
                        );
                    } else {
                        // Work started without quote - send progress update
                        $this->notificationService->sendCustomCakeInProgress(
                            $customCakeOrder->user_id, 
                            $customCakeOrder->id
                        );
                    }
                    break;

                case 'completed':
                    // Custom cake completed - use dedicated method
                    $this->notificationService->sendCustomCakeCompleted(
                        $customCakeOrder->user_id, 
                        $customCakeOrder->id
                    );
                    break;

                case 'cancelled':
                    // Custom cake cancelled - use dedicated method
                    $this->notificationService->sendCustomCakeCancelled(
                        $customCakeOrder->user_id, 
                        $customCakeOrder->id,
                        $request->admin_message
                    );
                    break;
            }

            $notificationMessage = 'Custom cake status updated and customer notified successfully!';
            
        } catch (\Exception $e) {
            \Log::error("Failed to send custom cake notification for order {$customCakeOrder->id}: " . $e->getMessage());
            $notificationMessage = 'Custom cake status updated but notification failed to send.';
        }

        return redirect()->back()
            ->with('success', $notificationMessage);
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