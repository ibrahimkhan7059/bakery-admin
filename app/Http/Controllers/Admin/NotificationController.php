<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationMessageService;
use App\Models\Order;
use App\Models\CustomCakeOrder;
use App\Models\BulkOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    private $notificationService;

    public function __construct(NotificationMessageService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * ==========================================
     * ORDER MANAGEMENT OPERATIONS
     * ==========================================
     */

    // Admin updates order status
    public function updateOrderStatus(Request $request, $orderId)
    {
        $request->validate([
            'status' => 'required|in:received,processing,ready,delivered,cancelled',
            'delivery_type' => 'sometimes|in:pickup,delivery',
            'cancellation_reason' => 'required_if:status,cancelled'
        ]);

        $order = Order::findOrFail($orderId);
        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Update order status
        $order->update([
            'status' => $newStatus,
            'delivery_type' => $request->delivery_type,
            'cancellation_reason' => $request->cancellation_reason
        ]);

        // Send appropriate notification based on status
        switch ($newStatus) {
            case 'received':
                $this->notificationService->sendOrderReceived(
                    $order->user_id, 
                    $order->id, 
                    $order->total_amount
                );
                break;

            case 'processing':
                $this->notificationService->sendOrderProcessing(
                    $order->user_id, 
                    $order->id
                );
                break;

            case 'ready':
                $this->notificationService->sendOrderReady(
                    $order->user_id, 
                    $order->id, 
                    $request->delivery_type
                );
                break;

            case 'delivered':
                $this->notificationService->sendOrderCompleted(
                    $order->user_id, 
                    $order->id
                );
                break;

            case 'cancelled':
                $this->notificationService->sendOrderCancelled(
                    $order->user_id, 
                    $order->id, 
                    $request->cancellation_reason
                );
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated and notification sent',
            'order' => $order
        ]);
    }

    /**
     * ==========================================
     * CUSTOM CAKE OPERATIONS
     * ==========================================
     */

    // Admin updates custom cake design status
    public function updateCustomCakeStatus(Request $request, $customId)
    {
        $request->validate([
            'status' => 'required|in:received,approved,rejected,quote_ready',
            'message' => 'sometimes|string',
            'price' => 'required_if:status,quote_ready|numeric'
        ]);

        $customCake = CustomCakeOrder::findOrFail($customId);
        $customCake->update([
            'status' => $request->status,
            'admin_message' => $request->message,
            'quoted_price' => $request->price
        ]);

        // Send appropriate notification
        switch ($request->status) {
            case 'received':
                $this->notificationService->sendCustomCakeReceived(
                    $customCake->user_id, 
                    $customCake->id
                );
                break;

            case 'approved':
            case 'rejected':
                $this->notificationService->sendCustomCakeDesignUpdate(
                    $customCake->user_id, 
                    $customCake->id, 
                    $request->status, 
                    $request->message
                );
                break;

            case 'quote_ready':
                $this->notificationService->sendCustomCakeQuote(
                    $customCake->user_id, 
                    $customCake->id, 
                    $request->price
                );
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Custom cake status updated and notification sent',
            'custom_cake' => $customCake
        ]);
    }

    /**
     * ==========================================
     * BULK ORDER OPERATIONS
     * ==========================================
     */

    // Admin updates bulk order status
    public function updateBulkOrderStatus(Request $request, $bulkId)
    {
        $request->validate([
            'status' => 'required|in:received,quote_ready,approved,processing,completed',
            'total_price' => 'required_if:status,quote_ready|numeric',
            'delivery_date' => 'required_if:status,quote_ready|date'
        ]);

        $bulkOrder = BulkOrder::findOrFail($bulkId);
        $bulkOrder->update([
            'status' => $request->status,
            'total_price' => $request->total_price,
            'delivery_date' => $request->delivery_date
        ]);

        // Send appropriate notification
        switch ($request->status) {
            case 'received':
                $this->notificationService->sendBulkOrderReceived(
                    $bulkOrder->user_id, 
                    $bulkOrder->id
                );
                break;

            case 'quote_ready':
                $this->notificationService->sendBulkOrderQuote(
                    $bulkOrder->user_id, 
                    $bulkOrder->id, 
                    $request->total_price, 
                    $request->delivery_date
                );
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk order status updated and notification sent',
            'bulk_order' => $bulkOrder
        ]);
    }

    /**
     * ==========================================
     * PROMOTIONAL OPERATIONS
     * ==========================================
     */

    // Admin sends new product launch notification
    public function sendNewProductNotification(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $product = Product::findOrFail($request->product_id);
        
        $this->notificationService->sendNewProductLaunch(
            $product->name,
            $product->price,
            $product->image_url
        );

        return response()->json([
            'success' => true,
            'message' => 'New product launch notification sent to all users'
        ]);
    }

    // Admin sends discount offer notification
    public function sendDiscountNotification(Request $request)
    {
        $request->validate([
            'offer_title' => 'required|string',
            'discount_percent' => 'required|numeric|min:1|max:100',
            'valid_until' => 'required|date|after:today'
        ]);

        $this->notificationService->sendDiscountOffer(
            $request->offer_title,
            $request->discount_percent,
            $request->valid_until
        );

        return response()->json([
            'success' => true,
            'message' => 'Discount offer notification sent to all users'
        ]);
    }

    // Admin sends festival offer notification
    public function sendFestivalNotification(Request $request)
    {
        $request->validate([
            'festival_name' => 'required|string',
            'offer_details' => 'required|string'
        ]);

        $this->notificationService->sendFestivalOffer(
            $request->festival_name,
            $request->offer_details
        );

        return response()->json([
            'success' => true,
            'message' => 'Festival offer notification sent to all users'
        ]);
    }

    /**
     * ==========================================
     * INVENTORY MANAGEMENT OPERATIONS
     * ==========================================
     */

    // Admin updates product stock (triggers low stock alert)
    public function updateProductStock(Request $request, $productId)
    {
        $request->validate([
            'stock' => 'required|integer|min:0'
        ]);

        $product = Product::findOrFail($productId);
        $product->update(['stock' => $request->stock]);

        // Check if stock is low (less than 10 items)
        if ($request->stock < 10) {
            // Get admin users (assuming role_id = 1 for admin)
            $adminUsers = User::where('role', 'admin')->get();
            
            foreach ($adminUsers as $admin) {
                $this->notificationService->sendLowStockAlert(
                    $admin->id,
                    $product->name,
                    $request->stock
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product stock updated',
            'stock_alert_sent' => $request->stock < 10
        ]);
    }

    /**
     * ==========================================
     * AUTOMATED SYSTEM OPERATIONS
     * ==========================================
     */

    // Automatically triggered when new order is placed
    public function handleNewOrder($orderId)
    {
        $order = Order::with('user')->findOrFail($orderId);
        
        // Send confirmation to customer
        $this->notificationService->sendOrderReceived(
            $order->user_id,
            $order->id,
            $order->total_amount
        );

        // Alert admin users
        $adminUsers = User::where('role', 'admin')->get();
        foreach ($adminUsers as $admin) {
            $this->notificationService->sendNewOrderAlert(
                $admin->id,
                $order->id,
                $order->user->name,
                $order->total_amount
            );
        }
    }

    // Automatically triggered when new custom cake request is made
    public function handleNewCustomCakeRequest($customId)
    {
        $customCake = CustomCakeOrder::with('user')->findOrFail($customId);
        
        // Send confirmation to customer
        $this->notificationService->sendCustomCakeReceived(
            $customCake->user_id,
            $customCake->id
        );

        // Alert admin users
        $adminUsers = User::where('role', 'admin')->get();
        foreach ($adminUsers as $admin) {
            $this->notificationService->sendCustomCakeAlert(
                $admin->id,
                $customCake->id,
                $customCake->user->name
            );
        }
    }

    /**
     * ==========================================
     * USER ENGAGEMENT OPERATIONS
     * ==========================================
     */

    // Send cart abandonment reminder (run via cron job)
    public function sendCartReminders()
    {
        // Logic to find users with items in cart but no order in last 24 hours
        // This would typically be run as a scheduled job
        
        $usersWithAbandonedCarts = User::whereHas('cartItems')
            ->whereDoesntHave('orders', function($query) {
                $query->where('created_at', '>', now()->subDay());
            })
            ->with('cartItems')
            ->get();

        foreach ($usersWithAbandonedCarts as $user) {
            $itemsCount = $user->cartItems->count();
            $this->notificationService->sendCartReminder($user->id, $itemsCount);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart reminder notifications sent',
            'users_notified' => $usersWithAbandonedCarts->count()
        ]);
    }

    // Send re-engagement messages (run via cron job)
    public function sendReEngagementMessages()
    {
        // Find users who haven't ordered in last 30 days
        $inactiveUsers = User::whereDoesntHave('orders', function($query) {
                $query->where('created_at', '>', now()->subDays(30));
            })
            ->where('created_at', '<', now()->subDays(7)) // But registered at least 7 days ago
            ->get();

        foreach ($inactiveUsers as $user) {
            $this->notificationService->sendReEngagementMessage($user->id, $user->name);
        }

        return response()->json([
            'success' => true,
            'message' => 'Re-engagement notifications sent',
            'users_notified' => $inactiveUsers->count()
        ]);
    }

    /**
     * ==========================================
     * WELCOME MESSAGE FOR NEW USERS
     * ==========================================
     */

    // Send welcome message (triggered on user registration)
    public function sendWelcomeMessage($userId)
    {
        $user = User::findOrFail($userId);
        
        $this->notificationService->sendWelcomeMessage($userId, $user->name);

        return response()->json([
            'success' => true,
            'message' => 'Welcome notification sent'
        ]);
    }
}
