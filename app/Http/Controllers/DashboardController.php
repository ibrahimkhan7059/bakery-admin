<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get real-time counts for dashboard cards
        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();
        
        // Get recent orders for the table
        $recentOrders = Order::latest()->take(5)->get();
        
        return view('admin.dashboard', compact('totalOrders', 'totalProducts', 'totalCustomers', 'recentOrders'));
    }
} 