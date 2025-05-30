<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BulkOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All routes here are stateless and use Sanctum for token authentication.
*/

/**
 * Public Routes
 */
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Flutter specific routes
Route::post('/flutter/register', [AuthController::class, 'register']);
Route::post('/flutter/login', [AuthController::class, 'login']);

/**
 * Protected Routes - Require Sanctum Token
 */
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile
    Route::prefix('/v1/profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
    });

    // Products
    Route::prefix('/v1/products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{product}', [ProductController::class, 'show']);
        Route::get('/category/{category}', [ProductController::class, 'byCategory']);
    });

    // Categories
    Route::prefix('/v1/categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{category}', [CategoryController::class, 'show']);
    });

    // Cart
    Route::prefix('/v1/cart')->group(function () {
        Route::get('/', [CartController::class, 'show']);
        Route::post('/', [CartController::class, 'add']);
        Route::put('/{item}', [CartController::class, 'update']);
        Route::delete('/{item}', [CartController::class, 'remove']);
    });

    // Orders
    Route::prefix('/v1/orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show']);
    });

    // Bulk Orders
    Route::prefix('/v1/bulk-orders')->group(function () {
        Route::post('/', [BulkOrderController::class, 'store']);
        Route::get('/', [BulkOrderController::class, 'index']);
        Route::get('/{bulkOrder}', [BulkOrderController::class, 'show']);
        Route::put('/{bulkOrder}/status', [BulkOrderController::class, 'updateStatus']);
    });
});
