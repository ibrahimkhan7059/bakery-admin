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
        
        // Get order status counts
        $statusCounts = [
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];
        
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
        
        // Get recent categories with their images
        $recentCategories = Category::withCount('products')->latest()->limit(5)->get();
        
        // Get low stock products (stock <= 5)
        $lowStockProducts = Product::where('stock', '<=', 5)->orderBy('stock', 'asc')->limit(5)->get();
        
        // Get monthly sales data - only show months where we actually have orders
        $monthlySales = [];
        $monthlyLabels = [];
        
        // Get the first order date to determine when project actually started
        $firstOrderDate = Order::orderBy('created_at', 'asc')->first();
        $projectStartDate = $firstOrderDate ? $firstOrderDate->created_at : now();
        
        // Calculate how many months to show (max 12, but only from project start)
        $monthsToShow = min(12, now()->diffInMonths($projectStartDate) + 1);
        
        for ($i = $monthsToShow - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            // Only show months from project start date onwards
            if ($date->gte($projectStartDate->startOfMonth())) {
                $monthlyLabels[] = $date->format('M Y');
                
                $monthSales = Order::whereYear('created_at', $date->year)
                                 ->whereMonth('created_at', $date->month)
                                 ->where(function($query) {
                                     $query->whereIn('status', ['completed', 'delivered'])
                                           ->orWhere('payment_status', 'paid');
                                 })
                                 ->sum('total_amount');
                
                $monthlySales[] = (float) $monthSales;
            }
        }
        
        // Get weekly sales data for current month
        $weeklySales = [];
        $weeklyLabels = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();
            
            $weeklyLabels[] = 'Week ' . ($i == 0 ? 'Current' : $i + 1);
            
            $weekSales = Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->where(function($query) {
                                $query->whereIn('status', ['completed', 'delivered'])
                                      ->orWhere('payment_status', 'paid');
                            })
                            ->sum('total_amount');
            
            $weeklySales[] = (float) $weekSales;
        }
        
        // Get today's hourly sales data (24 hours)
        $todaySales = [];
        $todayLabels = [];
        
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $todayLabels[] = $hour->format('H:00');
            
            $hourSales = Order::whereBetween('created_at', [
                                $hour->startOfHour(), 
                                $hour->endOfHour()
                            ])
                            ->where(function($query) {
                                $query->whereIn('status', ['completed', 'delivered'])
                                      ->orWhere('payment_status', 'paid');
                            })
                            ->sum('total_amount');
            
            $todaySales[] = (float) $hourSales;
        }
        
        return view('admin.dashboard', compact(
            'totalOrders', 'totalProducts', 'totalCustomers', 'totalRevenue', 
            'statusCounts', 'recentOrders', 'popularProducts', 'recentCategories', 
            'lowStockProducts', 'monthlySales', 'monthlyLabels', 'weeklySales', 'weeklyLabels',
            'todaySales', 'todayLabels'
        ));
    }

    public function getChartData()
    {
        // Get monthly sales data - only show months where we actually have orders
        $monthlySales = [];
        $monthlyLabels = [];
        
        // Get the first order date to determine when project actually started
        $firstOrderDate = Order::orderBy('created_at', 'asc')->first();
        $projectStartDate = $firstOrderDate ? $firstOrderDate->created_at : now();
        
        // Calculate how many months to show (max 12, but only from project start)
        $monthsToShow = min(12, now()->diffInMonths($projectStartDate) + 1);
        
        for ($i = $monthsToShow - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            // Only show months from project start date onwards
            if ($date->gte($projectStartDate->startOfMonth())) {
                $monthlyLabels[] = $date->format('M Y');
                
                $monthSales = Order::whereYear('created_at', $date->year)
                                 ->whereMonth('created_at', $date->month)
                                 ->where(function($query) {
                                     $query->whereIn('status', ['completed', 'delivered'])
                                           ->orWhere('payment_status', 'paid');
                                 })
                                 ->sum('total_amount');
                
                $monthlySales[] = (float) $monthSales;
            }
        }
        
        // Get weekly sales data for current month
        $weeklySales = [];
        $weeklyLabels = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();
            
            $weeklyLabels[] = 'Week ' . ($i == 0 ? 'Current' : $i + 1);
            
            $weekSales = Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->where(function($query) {
                                $query->whereIn('status', ['completed', 'delivered'])
                                      ->orWhere('payment_status', 'paid');
                            })
                            ->sum('total_amount');
            
            $weeklySales[] = (float) $weekSales;
        }

        // Get today's hourly sales data (24 hours)
        $todaySales = [];
        $todayLabels = [];
        
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $todayLabels[] = $hour->format('H:00');
            
            $hourSales = Order::whereBetween('created_at', [
                                $hour->startOfHour(), 
                                $hour->endOfHour()
                            ])
                            ->where(function($query) {
                                $query->whereIn('status', ['completed', 'delivered'])
                                      ->orWhere('payment_status', 'paid');
                            })
                            ->sum('total_amount');
            
            $todaySales[] = (float) $hourSales;
        }

        return response()->json([
            'monthly' => [
                'labels' => $monthlyLabels,
                'data' => $monthlySales
            ],
            'weekly' => [
                'labels' => $weeklyLabels,
                'data' => $weeklySales
            ],
            'today' => [
                'labels' => $todayLabels,
                'data' => $todaySales
            ]
        ]);
    }
} 