<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::get('/', function () {
    return redirect('/login'); // Redirect to login page
});

// ✅ Temporary route to set admin role (remove after use)
Route::get('/set-admin-role', function() {
    $user = User::find(1); // Assuming the admin is the first user
    if ($user) {
        $user->update(['role' => 'admin']);
        return "Admin role set for user: " . $user->email;
    }
    return "No user found";
});

// ✅ User Dashboard (Protected)
Route::get('/dashboard', function () {
    return redirect('/admin/dashboard'); // Redirect users to the admin panel
})->middleware(['auth', 'verified'])->name('dashboard');

// ✅ Admin Dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});

// ✅ Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ✅ Category Routes (Without `show()`)
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
});

// ✅ Product Routes 
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/products/{product}/delete-image', [ProductController::class, 'deleteImage'])->name('products.deleteImage');

    // ✅ Show products by category
    Route::get('/categories/{category}/products', [ProductController::class, 'showByCategory'])->name('categories.products');
});

// Orders
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index'); // Show all orders
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create'); // Show form to create order
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store'); // Store order in DB
    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit'); // Show edit order form
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update'); // Update order
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy'); // Delete order
    Route::get('/admin/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
});

// ✅ Customer Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
});

// ✅ Laravel Breeze Auth Routes
require __DIR__.'/auth.php';
