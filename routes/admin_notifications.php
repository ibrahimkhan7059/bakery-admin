<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\NotificationController;

/*
|--------------------------------------------------------------------------
| Admin Notification Routes
|--------------------------------------------------------------------------
| 
| These routes handle all admin operations that trigger notifications
| to users. All routes are protected with admin middleware.
|
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    
    /**
     * ==========================================
     * ORDER MANAGEMENT ROUTES
     * ==========================================
     */
    
    // Update order status - triggers customer notification
    Route::patch('/orders/{orderId}/status', [NotificationController::class, 'updateOrderStatus']);
    
    /**
     * Usage Examples:
     * 
     * 1. Mark Order as Processing:
     * PATCH /api/admin/orders/123/status
     * Body: { "status": "processing" }
     * 
     * 2. Mark Order Ready for Delivery:
     * PATCH /api/admin/orders/123/status  
     * Body: { "status": "ready", "delivery_type": "delivery" }
     * 
     * 3. Cancel Order:
     * PATCH /api/admin/orders/123/status
     * Body: { "status": "cancelled", "cancellation_reason": "Out of stock" }
     */
    
    /**
     * ==========================================
     * CUSTOM CAKE MANAGEMENT ROUTES
     * ==========================================
     */
    
    // Update custom cake status - triggers customer notification
    Route::patch('/custom-cakes/{customId}/status', [NotificationController::class, 'updateCustomCakeStatus']);
    
    /**
     * Usage Examples:
     * 
     * 1. Approve Custom Cake Design:
     * PATCH /api/admin/custom-cakes/456/status
     * Body: { "status": "approved", "message": "Beautiful design! We'll start baking." }
     * 
     * 2. Send Price Quote:
     * PATCH /api/admin/custom-cakes/456/status
     * Body: { "status": "quote_ready", "price": 2500, "message": "Quote based on your requirements" }
     * 
     * 3. Request Design Changes:
     * PATCH /api/admin/custom-cakes/456/status
     * Body: { "status": "rejected", "message": "Please adjust the size specifications" }
     */
    
    /**
     * ==========================================
     * BULK ORDER MANAGEMENT ROUTES
     * ==========================================
     */
    
    // Update bulk order status - triggers customer notification
    Route::patch('/bulk-orders/{bulkId}/status', [NotificationController::class, 'updateBulkOrderStatus']);
    
    /**
     * Usage Examples:
     * 
     * 1. Send Bulk Order Quote:
     * PATCH /api/admin/bulk-orders/789/status
     * Body: { 
     *   "status": "quote_ready", 
     *   "total_price": 15000, 
     *   "delivery_date": "2024-12-25" 
     * }
     */
    
    /**
     * ==========================================
     * PROMOTIONAL NOTIFICATION ROUTES
     * ==========================================
     */
    
    // Send new product launch notification to all users
    Route::post('/notifications/new-product', [NotificationController::class, 'sendNewProductNotification']);
    
    /**
     * Usage Example:
     * POST /api/admin/notifications/new-product
     * Body: { "product_id": 25 }
     */
    
    // Send discount offer notification to all users
    Route::post('/notifications/discount', [NotificationController::class, 'sendDiscountNotification']);
    
    /**
     * Usage Example:
     * POST /api/admin/notifications/discount
     * Body: { 
     *   "offer_title": "Weekend Special", 
     *   "discount_percent": 20, 
     *   "valid_until": "2024-12-31" 
     * }
     */
    
    // Send festival offer notification to all users
    Route::post('/notifications/festival', [NotificationController::class, 'sendFestivalNotification']);
    
    /**
     * Usage Example:
     * POST /api/admin/notifications/festival
     * Body: { 
     *   "festival_name": "Eid Mubarak", 
     *   "offer_details": "25% off on all cakes and sweets!" 
     * }
     */
    
    /**
     * ==========================================
     * INVENTORY MANAGEMENT ROUTES
     * ==========================================
     */
    
    // Update product stock - triggers low stock alert if needed
    Route::patch('/products/{productId}/stock', [NotificationController::class, 'updateProductStock']);
    
    /**
     * Usage Example:
     * PATCH /api/admin/products/15/stock
     * Body: { "stock": 5 }
     * 
     * If stock < 10, automatically sends low stock alert to all admins
     */
    
    /**
     * ==========================================
     * USER ENGAGEMENT ROUTES
     * ==========================================
     */
    
    // Send cart abandonment reminders (typically run via cron job)
    Route::post('/notifications/cart-reminders', [NotificationController::class, 'sendCartReminders']);
    
    // Send re-engagement messages (typically run via cron job)
    Route::post('/notifications/re-engagement', [NotificationController::class, 'sendReEngagementMessages']);
    
    // Send welcome message to new user
    Route::post('/notifications/welcome/{userId}', [NotificationController::class, 'sendWelcomeMessage']);
    
});

/*
|--------------------------------------------------------------------------
| Automated System Triggers (Internal Use)
|--------------------------------------------------------------------------
| 
| These methods are called automatically by the system when certain 
| events occur. They don't need routes as they're triggered internally.
|
*/

/**
 * AUTOMATIC TRIGGERS:
 * 
 * 1. When New Order is Placed:
 *    - Called from OrderController after order creation
 *    - $notificationController->handleNewOrder($orderId);
 *    - Sends confirmation to customer + alert to admin
 * 
 * 2. When Custom Cake Request is Made:
 *    - Called from CustomCakeController after request creation
 *    - $notificationController->handleNewCustomCakeRequest($customId);
 *    - Sends confirmation to customer + alert to admin
 * 
 * 3. When User Registers:
 *    - Called from AuthController after registration
 *    - $notificationController->sendWelcomeMessage($userId);
 *    - Sends welcome message to new user
 * 
 * 4. When Password is Changed:
 *    - Called from ProfileController after password update
 *    - $notificationService->sendPasswordChanged($userId);
 *    - Sends security notification
 * 
 * 5. When Profile is Updated:
 *    - Called from ProfileController after profile update
 *    - $notificationService->sendProfileUpdated($userId);
 *    - Sends confirmation notification
 */
