<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomCakeOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\BulkOrderController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Admin\CakeConfigController;

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

// ✅ Admin Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('admin.dashboard.chart-data');
Route::get('/report/income', [DashboardController::class, 'incomeReport'])->name('admin.report.income');
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Category Routes
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::get('/categories/{category}/products', [CategoryController::class, 'products'])->name('categories.products');

    // Product Routes
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/products/{product}/delete-image', [ProductController::class, 'deleteImage'])->name('products.deleteImage');
    Route::get('/products/export/cake-images', [ProductController::class, 'exportCakeImagesCsv'])->name('products.export.cake-images');

    // Orders Routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/bulk-update', [OrderController::class, 'bulkUpdate'])->name('orders.bulk-update');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/print-receipt', [OrderController::class, 'printReceipt'])->name('orders.print-receipt');

    // Bulk Orders Routes
    Route::get('/bulk-orders', [BulkOrderController::class, 'index'])->name('bulk-orders.index');
    Route::get('/bulk-orders/create', [BulkOrderController::class, 'create'])->name('bulk-orders.create');
    Route::post('/bulk-orders', [BulkOrderController::class, 'store'])->name('bulk-orders.store');
    Route::get('/bulk-orders/{bulkOrder}', [BulkOrderController::class, 'show'])->name('bulk-orders.show');
    Route::get('/bulk-orders/{bulkOrder}/edit', [BulkOrderController::class, 'edit'])->name('bulk-orders.edit');
    Route::put('/bulk-orders/{bulkOrder}', [BulkOrderController::class, 'update'])->name('bulk-orders.update');
    Route::delete('/bulk-orders/{bulkOrder}', [BulkOrderController::class, 'destroy'])->name('bulk-orders.destroy');
    Route::match(['post'], '/bulk-orders/{bulkOrder}/status', [BulkOrderController::class, 'updateStatus'])->name('bulk-orders.update-status');

    // Custom Cake Orders Routes
    Route::get('/custom-cake-orders', [CustomCakeOrderController::class, 'index'])->name('custom-cake-orders.index');
    Route::get('/custom-cake-orders/create', [CustomCakeOrderController::class, 'create'])->name('custom-cake-orders.create');
    Route::post('/custom-cake-orders', [CustomCakeOrderController::class, 'store'])->name('custom-cake-orders.store');
    Route::get('/custom-cake-orders/{customCakeOrder}', [CustomCakeOrderController::class, 'show'])->name('custom-cake-orders.show');
    Route::get('/custom-cake-orders/{customCakeOrder}/edit', [CustomCakeOrderController::class, 'edit'])->name('custom-cake-orders.edit');
    Route::put('/custom-cake-orders/{customCakeOrder}', [CustomCakeOrderController::class, 'update'])->name('custom-cake-orders.update');
    Route::delete('/custom-cake-orders/{customCakeOrder}', [CustomCakeOrderController::class, 'destroy'])->name('custom-cake-orders.destroy');
    Route::post('/custom-cake-orders/{customCakeOrder}/status', [CustomCakeOrderController::class, 'updateStatus'])->name('custom-cake-orders.update-status');
    Route::post('/custom-cake-orders/bulk-update', [CustomCakeOrderController::class, 'bulkUpdate'])->name('custom-cake-orders.bulk-update');
    Route::get('/bulk-orders/{bulkOrder}/invoice', [BulkOrderController::class, 'invoice'])->name('bulk-orders.invoice');

    // Customer Routes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Cake Config Routes
    Route::get('/cake-config', [CakeConfigController::class, 'index'])->name('admin.cake-config.index');
    Route::post('/cake-config/size', [CakeConfigController::class, 'storeSize'])->name('admin.cake-config.size.store');
    Route::put('/cake-config/size/{cakeSize}', [CakeConfigController::class, 'updateSize'])->name('admin.cake-config.size.update');
    Route::delete('/cake-config/size/{cakeSize}', [CakeConfigController::class, 'deleteSize'])->name('admin.cake-config.size.delete');

    Route::post('/cake-config/option', [CakeConfigController::class, 'storeOption'])->name('admin.cake-config.option.store');
    Route::put('/cake-config/option/{cakeOption}', [CakeConfigController::class, 'updateOption'])->name('admin.cake-config.option.update');
    Route::delete('/cake-config/option/{cakeOption}', [CakeConfigController::class, 'deleteOption'])->name('admin.cake-config.option.delete');
});

// ✅ Laravel Breeze Auth Routes
require __DIR__.'/auth.php';

// ✅ Admin Notification Routes
require __DIR__.'/admin_notifications.php';

// ✅ Settings Route (alias for convenience)
Route::middleware('auth')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
});

