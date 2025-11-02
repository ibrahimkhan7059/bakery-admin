@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
            <!-- Stats Cards -->
            <div class="row g-4 mb-3">       
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-md hover-lift reflection">
                        <div class="card-body position-relative p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 fs-6">Total Orders</p>
                            <h3 class="fw-bold mb-0" style="font-size: 1.8rem;">{{ $totalOrders }}</h3>
                            <p class="small text-success mt-2 mb-0"><i class="bi bi-arrow-up-right"></i> Active orders</p>
                                </div>
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(45deg, #6366f1, #a855f7); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);">
                                    <i class="bi bi-bag-fill text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="progress mt-4" style="height: 4px;">
                                <div class="progress-bar bg-indigo-500" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-md hover-lift reflection">
                        <div class="card-body position-relative p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 fs-6">Total Products</p>
                            <h3 class="fw-bold mb-0" style="font-size: 1.8rem;">{{ $totalProducts }}</h3>
                            <p class="small text-success mt-2 mb-0"><i class="bi bi-arrow-up-right"></i> Available items</p>
                                </div>
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(45deg, #10b981, #059669); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">
                                    <i class="bi bi-box2-fill text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="progress mt-4" style="height: 4px;">
                                <div class="progress-bar bg-green-500" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-md hover-lift reflection">
                        <div class="card-body position-relative p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 fs-6">Customers</p>
                            <h3 class="fw-bold mb-0" style="font-size: 1.8rem;">{{ $totalCustomers }}</h3>
                            <p class="small text-success mt-2 mb-0"><i class="bi bi-arrow-up-right"></i> Registered users</p>
                                </div>
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(45deg, #f59e0b, #d97706); box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2);">
                                    <i class="bi bi-people-fill text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="progress mt-4" style="height: 4px;">
                                <div class="progress-bar bg-yellow-500" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-md hover-lift reflection">
                        <div class="card-body position-relative p-4">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="text-muted mb-1 fs-6">Revenue</p>
                            <h3 class="fw-bold mb-0" style="font-size: 1.8rem;">Rs{{ number_format($totalRevenue, 0) }}</h3>
                            <p class="small text-success mt-2 mb-0"><i class="bi bi-arrow-up-right"></i> Total earnings</p>
                                </div>
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: linear-gradient(45deg, #ef4444, #dc2626); box-shadow: 0 10px 20px rgba(239, 68, 68, 0.2);">
                                    <i class="bi bi-currency-dollar text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="progress mt-4" style="height: 4px;">
                                <div class="progress-bar bg-red-500" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm rounded-lg bg-warning bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="bi bi-clock-fill text-warning fs-2 mb-2"></i>
                            <h4 class="fw-bold text-warning">{{ $statusCounts['pending'] }}</h4>
                            <p class="text-muted mb-0 small">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm rounded-lg bg-info bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="bi bi-gear-fill text-info fs-2 mb-2"></i>
                            <h4 class="fw-bold text-info">{{ $statusCounts['processing'] }}</h4>
                            <p class="text-muted mb-0 small">Processing</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm rounded-lg" style="background: rgba(128, 0, 128, 0.1);">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle-fill fs-2 mb-2" style="color: purple;"></i>
                            <h4 class="fw-bold" style="color: purple;">{{ $statusCounts['ready'] }}</h4>
                            <p class="text-muted mb-0 small">Ready</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm rounded-lg bg-success bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="bi bi-check2-all text-success fs-2 mb-2"></i>
                            <h4 class="fw-bold text-success">{{ $statusCounts['completed'] }}</h4>
                            <p class="text-muted mb-0 small">Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card border-0 shadow-sm rounded-lg bg-danger bg-opacity-10">
                        <div class="card-body text-center">
                            <i class="bi bi-x-circle-fill text-danger fs-2 mb-2"></i>
                            <h4 class="fw-bold text-danger">{{ $statusCounts['cancelled'] }}</h4>
                            <p class="text-muted mb-0 small">Cancelled</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-lg glass-card">
                        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-gray-800">Recent Orders</h5>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-primary hover-lift">
                                <i class="bi bi-eye me-1"></i> View All
                    </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0">Order ID</th>
                                            <th class="border-0">Customer</th>
                                            
                                            <th class="border-0">Date</th>
                                            <th class="border-0">Amount</th>
                                            <th class="border-0">Status</th>
                                            <th class="border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td>#ORD-{{ $order->id }}</td>
                                    <td>{{ $order->customer_name }}</td>
                                    
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>Rs{{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @if($order->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($order->status == 'processing')
                                            <span class="badge bg-info">Processing</span>
                                        @elseif($order->status == 'ready')
                                            <span class="badge bg-purple text-white">Ready</span>
                                        @elseif($order->status == 'completed')
                                            <span class="badge bg-success">Delivered</span>
                                        @elseif($order->status == 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                            </td>
                                    <td>
                                        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-sm btn-light hover-lift" title="View Order">
                                                    <i class="bi bi-eye"></i>
                                        </a>
                                            </td>
                                        </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-3">No orders found</td>
                                        </tr>
                                @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-lg glass-card">
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0 fw-bold text-gray-800">Popular Products</h5>
                        </div>
                        <div class="card-body">
                            @forelse($popularProducts as $product)
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle overflow-hidden me-3" style="width: 50px; height: 50px;">
                                    @if($product->image && file_exists(public_path('storage/' . $product->image)))
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit: cover;">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-0">{{ $product->name }}</h6>
                                        <span class="text-success">Rs{{ number_format($product->price, 2) }}</span>
                                    </div>
                                    <small class="text-muted">{{ $product->category->name ?? 'No Category' }}</small>
                                    <div class="progress mt-2" style="height: 5px;">
                                        @php
                                            $popularity = isset($product->total_ordered) 
                                                ? min(($product->total_ordered / max($popularProducts->max('total_ordered'), 1)) * 100, 100)
                                                : rand(40, 80);
                                        @endphp
                                        <div class="progress-bar bg-indigo-500" role="progressbar" style="width: {{ $popularity }}%" aria-valuenow="{{ $popularity }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-3">
                                <i class="bi bi-box2 text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0 mt-2">No products found</p>
                                <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary mt-2">Add First Product</a>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-lg glass-card">
                        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-gray-800">Recent Categories</h5>
                            <a href="{{ route('categories.index') }}" class="btn btn-sm btn-outline-primary hover-lift">
                                <i class="bi bi-eye me-1"></i> View All
                            </a>
                        </div>
                        <div class="card-body">
                            @forelse($recentCategories as $category)
                            <div class="py-3 border-bottom">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="d-flex align-items-center">
                                        @if($category->image && $category->image !== 'default.jpg')
                                            <img src="{{ asset('storage/' . $category->image) }}" 
                                                 alt="{{ $category->name }}" 
                                                 class="rounded-circle me-3" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(45deg, #6366f1, #a855f7);">
                                                <i class="bi bi-tag-fill text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $category->name }}</h6>
                                            <small class="text-muted">{{ $category->products_count }} products</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">{{ $category->created_at->format('M d') }}</small>
                                    </div>
                                </div>

                            </div>
                            @empty
                            <div class="text-center py-3">
                                <i class="bi bi-tags text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mb-0 mt-2">No categories found</p>
                                <a href="{{ route('categories.create') }}" class="btn btn-sm btn-primary mt-2">Add First Category</a>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sales Chart and Quick Actions -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-lg glass-card">
                        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-gray-800">Sales Overview</h5>
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-sm">
                                    <span class="text-muted">Today:</span>
                                    <span class="fw-bold text-success">Rs{{ number_format(\App\Models\Order::whereDate('created_at', today())->where(function($q) { $q->whereIn('status', ['completed', 'delivered'])->orWhere('payment_status', 'paid'); })->sum('total_amount'), 0) }}</span>
                                </div>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary active" id="monthlyBtn">Monthly</button>
                                    <button type="button" class="btn btn-outline-primary" id="weeklyBtn">Weekly</button>
                                    <button type="button" class="btn btn-outline-primary" id="todayBtn">Today</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height: 400px; width: 100%;">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-lg glass-card">
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0 fw-bold text-gray-800">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('products.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center">
                                    <i class="bi bi-plus-circle me-2"></i> Add New Product
                                </a>
                                <a href="{{ route('categories.create') }}" class="btn btn-outline-primary d-flex align-items-center justify-content-center">
                                    <i class="bi bi-tag me-2"></i> Add Category
                                </a>
                                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center">
                                    <i class="bi bi-list-ul me-2"></i> Manage Orders
                                </a>
                                <a href="{{ route('customers.index') }}" class="btn btn-outline-info d-flex align-items-center justify-content-center">
                                    <i class="bi bi-people me-2"></i> View Customers
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Low Stock Alert -->
                    @if($lowStockProducts->count() > 0)
                    <div class="card border-0 shadow-sm rounded-lg glass-card mt-3">
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0 fw-bold text-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>Low Stock Alert
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($lowStockProducts as $product)
                            <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div>
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">Only {{ $product->stock }} left</small>
                                </div>
                                <span class="badge {{ $product->stock == 0 ? 'bg-danger' : ($product->stock <= 2 ? 'bg-warning' : 'bg-secondary') }}">
                                    {{ $product->stock }}
                                </span>
                            </div>
                            @endforeach
                            <div class="mt-3">
                                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-danger w-100">
                                    <i class="bi bi-arrow-right me-1"></i> Restock Products
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Check what data we're getting
    console.log('📊 Chart Debug Info:');
    console.log('Monthly Labels:', @json($monthlyLabels));
    console.log('Monthly Sales:', @json($monthlySales));
    console.log('Weekly Labels:', @json($weeklyLabels));
    console.log('Weekly Sales:', @json($weeklySales));
    console.log('Today Labels:', @json($todayLabels));
    console.log('Today Sales:', @json($todaySales));
    
    // Sales chart data with enhanced styling
    const monthlyData = {
        labels: @json($monthlyLabels).length > 0 ? @json($monthlyLabels) : ['Nov 2025'],
        datasets: [{
            label: '💰 Monthly Revenue',
            data: @json($monthlySales).length > 0 ? @json($monthlySales) : [0],
            borderColor: 'rgba(99, 102, 241, 1)',
            backgroundColor: function(context) {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) return null;
                
                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
                gradient.addColorStop(0.7, 'rgba(99, 102, 241, 0.1)');
                gradient.addColorStop(1, 'rgba(99, 102, 241, 0.02)');
                return gradient;
            },
            tension: 0.4,
            fill: true,
            borderWidth: 4,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: 'rgba(99, 102, 241, 1)',
            pointBorderWidth: 3,
            pointRadius: 7,
            pointHoverRadius: 10,
            pointHoverBackgroundColor: 'rgba(99, 102, 241, 1)',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 4,
            shadowOffsetX: 0,
            shadowOffsetY: 4,
            shadowBlur: 10,
            shadowColor: 'rgba(99, 102, 241, 0.3)'
        }]
    };

    const weeklyData = {
        labels: @json($weeklyLabels).length > 0 ? @json($weeklyLabels) : ['Week 1', 'Week 2', 'Week 3', 'Current'],
        datasets: [{
            label: '📈 Weekly Revenue',
            data: @json($weeklySales).length > 0 ? @json($weeklySales) : [0, 0, 0, 0],
            borderColor: 'rgba(16, 185, 129, 1)',
            backgroundColor: function(context) {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) return null;
                
                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
                gradient.addColorStop(0.7, 'rgba(16, 185, 129, 0.1)');
                gradient.addColorStop(1, 'rgba(16, 185, 129, 0.02)');
                return gradient;
            },
            tension: 0.4,
            fill: true,
            borderWidth: 4,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: 'rgba(16, 185, 129, 1)',
            pointBorderWidth: 3,
            pointRadius: 7,
            pointHoverRadius: 10,
            pointHoverBackgroundColor: 'rgba(16, 185, 129, 1)',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 4,
            shadowOffsetX: 0,
            shadowOffsetY: 4,
            shadowBlur: 10,
            shadowColor: 'rgba(16, 185, 129, 0.3)'
        }]
    };

    const todayData = {
        labels: @json($todayLabels).length > 0 ? @json($todayLabels) : ['00:00', '06:00', '12:00', '18:00', '23:00'],
        datasets: [{
            label: '⏰ Hourly Revenue',
            data: @json($todaySales).length > 0 ? @json($todaySales) : [0, 0, 0, 0, 0],
            borderColor: 'rgba(245, 158, 11, 1)',
            backgroundColor: function(context) {
                const chart = context.chart;
                const {ctx, chartArea} = chart;
                if (!chartArea) return null;
                
                const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                gradient.addColorStop(0, 'rgba(245, 158, 11, 0.3)');
                gradient.addColorStop(0.7, 'rgba(245, 158, 11, 0.1)');
                gradient.addColorStop(1, 'rgba(245, 158, 11, 0.02)');
                return gradient;
            },
            tension: 0.4,
            fill: true,
            borderWidth: 4,
            pointBackgroundColor: '#ffffff',
            pointBorderColor: 'rgba(245, 158, 11, 1)',
            pointBorderWidth: 3,
            pointRadius: 7,
            pointHoverRadius: 10,
            pointHoverBackgroundColor: 'rgba(245, 158, 11, 1)',
            pointHoverBorderColor: '#ffffff',
            pointHoverBorderWidth: 4,
            shadowOffsetX: 0,
            shadowOffsetY: 4,
            shadowBlur: 10,
            shadowColor: 'rgba(245, 158, 11, 0.3)'
        }]
    };

    const config = {
        type: 'line',
        data: monthlyData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 20,
                    right: 20,
                    bottom: 20,
                    left: 20
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 25,
                        font: {
                            size: 14,
                            weight: 'bold',
                            family: 'Inter, system-ui, sans-serif'
                        },
                        color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.95)',
                    titleColor: '#f9fafb',
                    bodyColor: '#f9fafb',
                    borderColor: 'rgba(99, 102, 241, 0.8)',
                    borderWidth: 2,
                    cornerRadius: 12,
                    displayColors: true,
                    padding: 16,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        title: function(context) {
                            return 'Sales for ' + context[0].label;
                        },
                        label: function(context) {
                            return '💰 Revenue: Rs ' + new Intl.NumberFormat('en-PK').format(context.parsed.y);
                        },
                        afterLabel: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                            return `📊 ${percentage}% of total sales`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: '💰 Revenue (PKR)',
                        font: {
                            size: 14,
                            weight: 'bold',
                            family: 'Inter, system-ui, sans-serif'
                        },
                        color: '#374151',
                        padding: 10
                    },
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(99, 102, 241, 0.1)',
                        drawBorder: true,
                        borderColor: 'rgba(99, 102, 241, 0.3)',
                        borderWidth: 2,
                        lineWidth: 1
                    },
                    border: {
                        display: true,
                        color: 'rgba(99, 102, 241, 0.5)',
                        width: 2
                    },
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) {
                                return 'Rs ' + (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return 'Rs ' + (value / 1000).toFixed(0) + 'K';
                            }
                            return 'Rs ' + new Intl.NumberFormat('en-PK').format(value);
                        },
                        font: {
                            size: 12,
                            family: 'Inter, system-ui, sans-serif',
                            weight: '500'
                        },
                        color: '#374151',
                        padding: 10,
                        maxTicksLimit: 8
                    }
                },
                x: {
                    type: 'category',
                    display: true,
                    position: 'bottom',
                    title: {
                        display: true,
                        text: '📅 Time Period',
                        font: {
                            size: 14,
                            weight: 'bold',
                            family: 'Inter, system-ui, sans-serif'
                        },
                        color: '#374151',
                        padding: 10
                    },
                    grid: {
                        display: true,
                        color: 'rgba(99, 102, 241, 0.1)',
                        drawBorder: true,
                        borderColor: 'rgba(99, 102, 241, 0.3)',
                        borderWidth: 2,
                        lineWidth: 1
                    },
                    border: {
                        display: true,
                        color: 'rgba(99, 102, 241, 0.5)',
                        width: 2
                    },
                    ticks: {
                        font: {
                            size: 12,
                            family: 'Inter, system-ui, sans-serif',
                            weight: '600'
                        },
                        color: '#374151',
                        maxRotation: 45,
                        minRotation: 0,
                        padding: 10,
                        callback: function(value, index, values) {
                            // Force show labels instead of values
                            const labels = this.chart.data.labels;
                            return labels[index] || '';
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutCubic'
            },
            elements: {
                point: {
                    hoverRadius: 10,
                    hoverBorderWidth: 3
                }
            }
        }
    };

    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, config);

    // Toggle between monthly, weekly and today views
    const monthlyBtn = document.getElementById('monthlyBtn');
    const weeklyBtn = document.getElementById('weeklyBtn');
    const todayBtn = document.getElementById('todayBtn');

    monthlyBtn.addEventListener('click', function() {
        monthlyBtn.classList.add('active');
        weeklyBtn.classList.remove('active');
        todayBtn.classList.remove('active');
        
        salesChart.data = monthlyData;
        salesChart.update('active');
        
        // Add smooth transition effect
        salesChart.options.animation.duration = 800;
        console.log('📊 Switched to Monthly View');
    });

    weeklyBtn.addEventListener('click', function() {
        weeklyBtn.classList.add('active');
        monthlyBtn.classList.remove('active');
        todayBtn.classList.remove('active');
        
        salesChart.data = weeklyData;
        salesChart.update('active');
        
        // Add smooth transition effect
        salesChart.options.animation.duration = 800;
        console.log('📈 Switched to Weekly View');
    });

    todayBtn.addEventListener('click', function() {
        todayBtn.classList.add('active');
        monthlyBtn.classList.remove('active');
        weeklyBtn.classList.remove('active');
        
        salesChart.data = todayData;
        salesChart.update('active');
        
        // Add smooth transition effect
        salesChart.options.animation.duration = 800;
        console.log('⏰ Switched to Today View');
    });

    // Auto-refresh chart every 2 minutes
    setInterval(function() {
        fetch('{{ route("admin.dashboard.chart-data") }}')
            .then(response => response.json())
            .then(data => {
                // Update monthly data
                monthlyData.labels = data.monthly.labels;
                monthlyData.datasets[0].data = data.monthly.data;
                
                // Update weekly data
                weeklyData.labels = data.weekly.labels;
                weeklyData.datasets[0].data = data.weekly.data;
                
                // Update today data
                todayData.labels = data.today.labels;
                todayData.datasets[0].data = data.today.data;
                
                // Update current chart
                if (monthlyBtn.classList.contains('active')) {
                    salesChart.data = monthlyData;
                } else if (weeklyBtn.classList.contains('active')) {
                    salesChart.data = weeklyData;
                } else {
                    salesChart.data = todayData;
                }
                
                salesChart.update('none'); // Update without animation
                console.log('✅ Chart data refreshed successfully');
            })
            .catch(error => {
                console.log('❌ Auto-refresh failed:', error);
            });
    }, 120000); // 2 minutes

    // Add real-time indicator
    const chartHeader = document.querySelector('.card-header h5');
    const indicator = document.createElement('span');
    indicator.innerHTML = ' <i class="bi bi-circle-fill text-success" style="font-size: 8px;" title="Live Data"></i>';
    chartHeader.appendChild(indicator);
    
    // Animate indicator every 2 minutes
    setInterval(function() {
        indicator.innerHTML = ' <i class="bi bi-circle-fill text-warning" style="font-size: 8px;" title="Updating..."></i>';
        setTimeout(() => {
            indicator.innerHTML = ' <i class="bi bi-circle-fill text-success" style="font-size: 8px;" title="Live Data"></i>';
        }, 1000);
    }, 120000);
});
</script>
@endpush

@endsection
