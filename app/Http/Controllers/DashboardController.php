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
        
        // Calculate total revenue from completed orders only
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        
        // Simple monthly revenue data (last 6 months)
        $monthlySales = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');
            $monthSales = Order::whereYear('created_at', $date->year)
                             ->whereMonth('created_at', $date->month)
                             ->where('status', 'completed')
                             ->sum('total_amount');
            $monthlySales[] = (float) $monthSales;
        }
        
        // Simple weekly revenue data (last 4 weeks)
        $weeklySales = [];
        $weeklyLabels = [];
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();
            $weeklyLabels[] = $i == 0 ? 'This Week' : $i . ' weeks ago';
            $weekSales = Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->where('status', 'completed')
                            ->sum('total_amount');
            $weeklySales[] = (float) $weekSales;
        }
        
        // Today revenue data - TEST MODE: Last 60 seconds in 10-second intervals
        $todaySales = [];
        $todayLabels = [];
        for ($i = 60; $i >= 0; $i -= 10) {
            $timeStart = now()->subSeconds($i);
            $timeEnd = now()->subSeconds(max(0, $i - 10));
            $todayLabels[] = $timeStart->format('H:i:s');
            $intervalSales = Order::whereBetween('created_at', [$timeStart, $timeEnd])
                            ->where('status', 'completed')
                            ->sum('total_amount');
            $todaySales[] = (float) $intervalSales;
        }
        
        // Get other dashboard data
        $recentOrders = Order::latest()->take(5)->get();
        
        // Get most ordered products (popular products based on order count)
        $popularProducts = Product::with(['category', 'orderItems'])
            ->withSum('orderItems as total_ordered', 'quantity')
            ->orderByDesc('total_ordered')
            ->limit(6) // Show top 6 most ordered products
            ->get();
        
        $recentCategories = Category::withCount('products')->latest()->limit(5)->get();
        $lowStockProducts = Product::where('stock', '<=', 5)->orderBy('stock', 'asc')->limit(5)->get();
        

        
        return view('admin.dashboard', compact(
            'totalOrders', 'totalProducts', 'totalCustomers', 'totalRevenue', 
            'statusCounts', 'recentOrders', 'popularProducts', 'recentCategories', 
            'lowStockProducts', 'monthlySales', 'monthlyLabels', 'weeklySales', 'weeklyLabels',
            'todaySales', 'todayLabels'
        ));
    }

    public function getChartData()
    {
        // Simple chart data for AJAX requests (completed orders only)
        
        // Monthly data (last 6 months)
        $monthlySales = [];
        $monthlyLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');
            $monthlySales[] = (float) Order::whereYear('created_at', $date->year)
                                          ->whereMonth('created_at', $date->month)
                                          ->where('status', 'completed')
                                          ->sum('total_amount');
        }
        
        // Weekly data (last 4 weeks)
        $weeklySales = [];
        $weeklyLabels = [];
        for ($i = 3; $i >= 0; $i--) {
            $startOfWeek = now()->subWeeks($i)->startOfWeek();
            $endOfWeek = now()->subWeeks($i)->endOfWeek();
            $weeklyLabels[] = $i == 0 ? 'This Week' : $i . ' weeks ago';
            $weeklySales[] = (float) Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                                          ->where('status', 'completed')
                                          ->sum('total_amount');
        }

        // Today data - TEST MODE: Last 60 seconds in 10-second intervals
        $todaySales = [];
        $todayLabels = [];
        for ($i = 60; $i >= 0; $i -= 10) {
            $timeStart = now()->subSeconds($i);
            $timeEnd = now()->subSeconds(max(0, $i - 10));
            $todayLabels[] = $timeStart->format('H:i:s');
            $intervalSales = Order::whereBetween('created_at', [$timeStart, $timeEnd])
                                  ->where('status', 'completed')
                                  ->sum('total_amount');
            $todaySales[] = (float) $intervalSales;
        }

        return response()->json([
            'monthly' => ['labels' => $monthlyLabels, 'data' => $monthlySales],
            'weekly' => ['labels' => $weeklyLabels, 'data' => $weeklySales],
            'today' => ['labels' => $todayLabels, 'data' => $todaySales]
        ]);
    }

public function incomeReport(Request $request)
{
    $period = $request->get('period', 'today');
    $query = Order::where('status', 'completed');

    // Set date ranges based on period
    switch ($period) {
        case 'today':
            $start = now()->startOfDay();
            $end = now()->endOfDay();
            $label = 'Today';
            $dateFormat = 'M d, Y';
            break;
        case 'weekly':
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
            $label = 'This Week';
            $dateFormat = 'M d, Y';
            break;
        case 'monthly':
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $label = 'This Month';
            $dateFormat = 'M Y';
            break;
        default:
            $start = now()->startOfDay();
            $end = now()->endOfDay();
            $label = 'Today';
            $dateFormat = 'M d, Y';
    }

    // Calculate income and fetch orders
    $totalIncome = $query->whereBetween('created_at', [$start, $end])->sum('total_amount');
    $totalOrders = $query->whereBetween('created_at', [$start, $end])->count();
    $orders = $query->whereBetween('created_at', [$start, $end])
                   ->with('user')
                   ->orderBy('created_at', 'desc')
                   ->get();

    // Calculate growth comparison with previous period
    switch ($period) {
        case 'today':
            $prevStart = now()->subDay()->startOfDay();
            $prevEnd = now()->subDay()->endOfDay();
            break;
        case 'weekly':
            $prevStart = now()->subWeek()->startOfWeek();
            $prevEnd = now()->subWeek()->endOfWeek();
            break;
        case 'monthly':
            $prevStart = now()->subMonth()->startOfMonth();
            $prevEnd = now()->subMonth()->endOfMonth();
            break;
    }
    
    $prevIncome = Order::where('status', 'completed')
                      ->whereBetween('created_at', [$prevStart, $prevEnd])
                      ->sum('total_amount');
    
    $growth = $prevIncome > 0 ? (($totalIncome - $prevIncome) / $prevIncome) * 100 : 0;

    return view('admin.income_report', compact(
        'totalIncome', 
        'totalOrders', 
        'orders', 
        'label', 
        'period', 
        'start', 
        'end', 
        'growth',
        'dateFormat'
    ));
}
} 