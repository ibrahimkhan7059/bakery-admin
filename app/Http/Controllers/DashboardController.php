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
        
        // Simple today revenue data (every 4 hours)
        $todaySales = [];
        $todayLabels = [];
        for ($i = 0; $i < 24; $i += 4) {
            $hour = now()->startOfDay()->addHours($i);
            $todayLabels[] = $hour->format('H:i');
            $hourSales = Order::whereBetween('created_at', [
                                $hour, 
                                $hour->copy()->addHours(4)
                            ])
                            ->where('status', 'completed')
                            ->sum('total_amount');
            $todaySales[] = (float) $hourSales;
        }
        
        // Get other dashboard data
        $recentOrders = Order::latest()->take(5)->get();
        $popularProducts = Product::with('category')->latest()->limit(3)->get();
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

        // Today data (every 4 hours)
        $todaySales = [];
        $todayLabels = [];
        for ($i = 0; $i < 24; $i += 4) {
            $hour = now()->startOfDay()->addHours($i);
            $todayLabels[] = $hour->format('H:i');
            $todaySales[] = (float) Order::whereBetween('created_at', [
                                          $hour, 
                                          $hour->copy()->addHours(4)
                                      ])
                                      ->where('status', 'completed')
                                      ->sum('total_amount');
        }

        return response()->json([
            'monthly' => ['labels' => $monthlyLabels, 'data' => $monthlySales],
            'weekly' => ['labels' => $weeklyLabels, 'data' => $weeklySales],
            'today' => ['labels' => $todayLabels, 'data' => $todaySales]
        ]);
    }
} 