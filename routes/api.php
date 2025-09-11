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

// Temporary: Bulk Orders (will be moved back to protected after auth is fixed)
Route::post('/v1/bulk-orders', [BulkOrderController::class, 'store']);
Route::get('/v1/bulk-orders', [BulkOrderController::class, 'index']);
Route::get('/v1/bulk-orders/{bulkOrder}', [BulkOrderController::class, 'show']);
Route::put('/v1/bulk-orders/{bulkOrder}/status', [BulkOrderController::class, 'updateStatus']);
Route::delete('/v1/bulk-orders/{bulkOrder}', [BulkOrderController::class, 'destroy']);

// Protected routes (Sanctum middleware)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::get('/v1/profile', [ProfileController::class, 'show']);
    Route::put('/v1/profile', [ProfileController::class, 'update']);

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
});

// AI Cake routes (public - no auth required)
Route::prefix('v1/ai-cake')->group(function () {
    Route::post('/predict', [AICakeController::class, 'predictCake']);
    Route::get('/health', [AICakeController::class, 'healthCheck']);
    Route::get('/categories', [AICakeController::class, 'getCakeCategories']);
    Route::get('/debug', [AICakeController::class, 'debugProducts']); // Debug endpoint
    Route::get('/products-by-category', [AICakeController::class, 'getProductsByCategory']); // Get products by category
});
