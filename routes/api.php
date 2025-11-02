<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BulkOrderController;
use App\Http\Controllers\Api\CustomCakeOrderController;
use App\Http\Controllers\Api\CakeConfigController;
use App\Http\Controllers\Api\AICakeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ Public Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/verify-login', [AuthController::class, 'verifyLogin']);

// Public Routes
Route::get('/v1/categories', [CategoryController::class, 'index']);
Route::get('/v1/categories/{category}', [CategoryController::class, 'show']);

// Public Product Routes
Route::get('/v1/products', [ProductController::class, 'index']);
Route::get('/v1/products/search', [ProductController::class, 'search']);
Route::get('/v1/products/{product}', [ProductController::class, 'show']);
Route::get('/v1/products/category/{category}', [ProductController::class, 'byCategory']);

// Public Cake Config Routes
Route::get('/v1/cake-config', [CakeConfigController::class, 'index']);

// FCM Token Registration (public for initial app setup)
Route::post('/v1/register-fcm-token', [FcmTokenController::class, 'register']);
Route::post('/v1/link-fcm-by-email', [FcmTokenController::class, 'linkByEmail']);

// Temporary: Bulk Orders (will be moved back to protected after auth is fixed)
Route::post('/v1/bulk-orders', [BulkOrderController::class, 'store']);
Route::get('/v1/bulk-orders', [BulkOrderController::class, 'index']);
Route::get('/v1/bulk-orders/{bulkOrder}', [BulkOrderController::class, 'show']);
Route::put('/v1/bulk-orders/{bulkOrder}/status', [BulkOrderController::class, 'updateStatus']);
Route::delete('/v1/bulk-orders/{bulkOrder}', [BulkOrderController::class, 'destroy']);

// Guest Order Routes (for checkout)
Route::post('/orders', [OrderController::class, 'storeGuestOrder']);
Route::get('/orders/{order}', [OrderController::class, 'showGuestOrder']);

// AI Cake Matching (Temporary - outside auth for debugging)
Route::post('/ai-match-cakes', [AICakeController::class, 'predictCake']);
Route::post('/v1/ai-cake', [AICakeController::class, 'predictCake']);
Route::get('/ai-health', [AICakeController::class, 'healthCheck']);

// Debug route to test if routes are working
Route::any('/debug-ai', function() {
    \Illuminate\Support\Facades\Log::info('🐛 Debug AI route hit', [
        'method' => request()->method(),
        'url' => request()->fullUrl(),
        'headers' => request()->headers->all()
    ]);
    return response()->json(['debug' => 'AI route is working', 'timestamp' => now()]);
});

// Protected routes (Sanctum middleware)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/v1/profile', [ProfileController::class, 'show']);
    Route::put('/v1/profile', [ProfileController::class, 'update']);
    Route::put('/v1/profile/change-password', [ProfileController::class, 'changePassword']);

    // Cart
    Route::get('/v1/cart', [CartController::class, 'show']);
    Route::post('/v1/cart', [CartController::class, 'add']);
    Route::put('/v1/cart/{item}', [CartController::class, 'update']);
    Route::delete('/v1/cart/{item}', [CartController::class, 'remove']);

    // Orders
    Route::post('/v1/orders', [OrderController::class, 'store']);
    Route::get('/v1/orders', [OrderController::class, 'index']);
    Route::get('/v1/orders/{order}', [OrderController::class, 'show']);

    // Custom Cake Orders
    Route::get('/v1/custom-cake-orders', [CustomCakeOrderController::class, 'index']);
    Route::post('/v1/custom-cake-orders', [CustomCakeOrderController::class, 'store']);
    Route::get('/v1/custom-cake-orders/{customCakeOrder}', [CustomCakeOrderController::class, 'show']);
    Route::put('/v1/custom-cake-orders/{customCakeOrder}', [CustomCakeOrderController::class, 'update']);
    Route::delete('/v1/custom-cake-orders/{customCakeOrder}', [CustomCakeOrderController::class, 'destroy']);

    // AI Cake Matching
    Route::post('/ai-match-cakes', [AICakeController::class, 'matchCakes']);
    
    // FCM Token Management (authenticated routes)
    Route::delete('/v1/delete-fcm-token', [FcmTokenController::class, 'delete']);
});

// PayFast Payment Routes
Route::prefix('payment')->group(function () {
    Route::post('/initiate', [PaymentController::class, 'initiatePayment']);
    Route::post('/success', [PaymentController::class, 'paymentSuccess']);
    Route::post('/failure', [PaymentController::class, 'paymentFailure']);
    Route::get('/status', [PaymentController::class, 'checkPaymentStatus']);
});

// FCM Notification Routes
Route::prefix('v1/notifications')->group(function () {
    Route::post('/fcm-token', [NotificationController::class, 'registerToken']);
    Route::delete('/fcm-token', [NotificationController::class, 'deleteToken']);
    Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
    Route::post('/send-bulk-notification', [NotificationController::class, 'sendBulkNotification']);
    Route::post('/send-order-update', [NotificationController::class, 'sendOrderUpdate']);
    Route::post('/send-custom-cake-update', [NotificationController::class, 'sendCustomCakeUpdate']);
    Route::post('/send-bulk-order-update', [NotificationController::class, 'sendBulkOrderUpdate']);
    Route::post('/send-promotional', [NotificationController::class, 'sendPromotionalNotification']);
    Route::get('/preferences/{userId}', [NotificationController::class, 'getNotificationPreferences']);
    Route::post('/preferences', [NotificationController::class, 'updateNotificationPreferences']);
});

// Test FCM notification endpoint for debugging
Route::post('/test-fcm-notification', function (Request $request) {
    try {
        $userId = $request->input('user_id');
        $title = $request->input('title', 'Test Notification');
        $message = $request->input('message', 'This is a test notification');
        
        \Log::info("Test FCM notification request", [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message
        ]);
        
        $firebaseService = app(\App\Services\FirebaseNotificationService::class);
        $result = $firebaseService->sendToUser($userId, $title, $message, [
            'type' => 'test',
            'action' => 'test_notification'
        ]);
        
        \Log::info("Test FCM notification result", ['result' => $result]);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Notification sent successfully' : 'Failed to send notification',
            'user_id' => $userId,
            'title' => $title,
            'body' => $message
        ]);
        
    } catch (\Exception $e) {
        \Log::error("Test FCM notification error", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
