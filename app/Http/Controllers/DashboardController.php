<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get real-time counts for dashboard cards
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();
        
        // Calculate total revenue using combined approach (status + payment based)
        $totalRevenue = Order::where(function($query) {
            $query->whereIn('status', ['completed', 'delivered'])
                  ->orWhere('payment_status', 'paid');
        })->sum('total_amount');
        
        // Get recent orders for the table
        $recentOrders = Order::latest()->take(5)->get();
        
        // Get popular products (simple fallback approach)
        $popularProducts = Product::with('category')->latest()->limit(3)->get();
        
        // Try to get actual popular products if order_items table exists
        try {
            $popularProductsQuery = DB::table('products')
                ->select('products.id', DB::raw('SUM(order_items.quantity) as total_ordered'))
                ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                ->groupBy('products.id')
                ->orderByDesc('total_ordered')
                ->limit(3)
                ->pluck('id');
            
            if ($popularProductsQuery->isNotEmpty()) {
                $popularProducts = Product::with('category')
                    ->whereIn('id', $popularProductsQuery)
                    ->get();
            }
        } catch (\Exception $e) {
            // If order_items table doesn't exist, use fallback
            \Log::info('Using fallback popular products due to: ' . $e->getMessage());
        }
        
        // Get recent categories
        $recentCategories = Category::withCount('products')->latest()->limit(5)->get();
        
        // Get low stock products (stock <= 5)
        $lowStockProducts = Product::where('stock', '<=', 5)->orderBy('stock', 'asc')->limit(5)->get();
        
        return view('admin.dashboard', compact('totalOrders', 'totalProducts', 'totalCustomers', 'totalRevenue', 'recentOrders', 'popularProducts', 'recentCategories', 'lowStockProducts'));
    }
} 