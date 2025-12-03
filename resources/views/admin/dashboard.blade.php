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
                        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 fw-bold text-gray-800">
                                    <i class="bi bi-star-fill text-warning me-2"></i>Popular Products
                                </h5>
                                <small class="text-muted">Most ordered items</small>
                            </div>
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary hover-lift">
                                <i class="bi bi-eye me-1"></i> View All
                            </a>
                        </div>
                        <div class="card-body">
            @forelse($popularProducts as $product)
            <div class="d-flex align-items-center mb-3 p-2 rounded hover-lift" style="transition: all 0.3s;">
                <div class="rounded-circle overflow-hidden me-3 position-relative" style="width: 50px; height: 50px;">
                    @if($product->image && file_exists(public_path('storage/' . $product->image)))
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-100 h-100" style="object-fit: cover;">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light">
                            <i class="bi bi-image text-muted"></i>
                        </div>
                    @endif
                    @if($loop->index < 3)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-{{ $loop->index == 0 ? 'warning' : ($loop->index == 1 ? 'secondary' : 'danger') }}" style="font-size: 0.65rem;">
                            {{ $loop->index + 1 }}
                        </span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-0">{{ $product->name }}</h6>
                            <small class="text-muted">{{ $product->category->name ?? 'No Category' }}</small>
                        </div>
                        <div class="text-end">
                            <span class="text-success fw-bold">Rs{{ number_format($product->price, 2) }}</span>
                            <br>
                            <small class="text-muted">
                                <i class="bi bi-cart-check-fill text-primary"></i> {{ $product->total_ordered ?? 0 }} orders
                            </small>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 5px;">
                        @php
                            $maxOrdered = $popularProducts->max('total_ordered') ?: 1;
                            $popularity = $product->total_ordered 
                                ? min(($product->total_ordered / $maxOrdered) * 100, 100)
                                : 0;
                        @endphp
                        <div class="progress-bar bg-gradient" role="progressbar" 
                             style="width: {{ $popularity }}%; background: linear-gradient(90deg, #6366f1 0%, #a855f7 100%);" 
                             aria-valuenow="{{ $popularity }}" aria-valuemin="0" aria-valuemax="100">
                        </div>
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
                        <div class="card-header py-3" style="background: linear-gradient(135deg, #FF6F61 0%, #FF8A7A 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-white">
                                    <i class="fas fa-chart-line me-2"></i>Revenue Overview
                                </h5>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-white" id="revenueDisplay">
                                        <span class="opacity-75" id="periodLabel">This Month:</span>
                                        <span class="fw-bold" id="periodAmount">Rs{{ number_format(array_sum($monthlySales), 0) }}</span>
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-light active" id="monthlyBtn">Monthly</button>
                                        <button type="button" class="btn btn-outline-light" id="weeklyBtn">Weekly</button>
                                        <button type="button" class="btn btn-outline-light" id="todayBtn">Today</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height: 400px; width: 100%;">
                                <canvas id="salesChart"></canvas>
                            </div>
<!-- Income Report Button -->
<div class="text-center mt-4 pt-3 border-top">
    <a href="{{ route('admin.report.income') }}" class="btn btn-primary btn-lg px-4 py-2">
        <i class="bi bi-file-text"></i> Report
    </a>
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
    console.log('📊 Revenue Chart Loading...');
    
    // Simple chart data - only completed order revenue
    const monthlyData = {
        labels: @json($monthlyLabels).length > 0 ? @json($monthlyLabels) : ['Nov 2025'],
        datasets: [{
            label: 'Monthly Revenue (Rs)',
            data: @json($monthlySales).length > 0 ? @json($monthlySales) : [0],
            borderColor: '#FF6F61',
            backgroundColor: 'rgba(255, 111, 97, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.3,
            pointBackgroundColor: '#FF6F61',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    };

    const weeklyData = {
        labels: @json($weeklyLabels).length > 0 ? @json($weeklyLabels) : ['Week 1', 'Week 2', 'Week 3', 'Current'],
        datasets: [{
            label: 'Weekly Revenue (Rs)',
            data: @json($weeklySales).length > 0 ? @json($weeklySales) : [0, 0, 0, 0],
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.3,
            pointBackgroundColor: '#28a745',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    };

    const todayData = {
        labels: @json($todayLabels).length > 0 ? @json($todayLabels) : ['00:00', '06:00', '12:00', '18:00', '23:00'],
        datasets: [{
            label: 'Hourly Revenue (Rs)',
            data: @json($todaySales).length > 0 ? @json($todaySales) : [0, 0, 0, 0, 0],
            borderColor: '#ffc107',
            backgroundColor: 'rgba(255, 193, 7, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.3,
            pointBackgroundColor: '#ffc107',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    };

    const config = {
        type: 'line',
        data: monthlyData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { size: 14, weight: 'bold' },
                        color: '#333',
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    cornerRadius: 8,
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rs ' + new Intl.NumberFormat('en-PK').format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue (PKR)',
                        font: { size: 12, weight: 'bold' },
                        color: '#666'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
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
                        font: { size: 11 },
                        color: '#666'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Time Period',
                        font: { size: 12, weight: 'bold' },
                        color: '#666'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        font: { size: 11 },
                        color: '#666',
                        maxRotation: 45
                    }
                }
            },
            animation: {
                duration: 800
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
        monthlyBtn.classList.remove('btn-outline-light');
        monthlyBtn.classList.add('btn-light');
        
        weeklyBtn.classList.remove('active', 'btn-light');
        weeklyBtn.classList.add('btn-outline-light');
        
        todayBtn.classList.remove('active', 'btn-light');
        todayBtn.classList.add('btn-outline-light');
        
        // Update header display
        const totalMonthly = @json($monthlySales).reduce((a, b) => a + b, 0);
        document.getElementById('periodLabel').textContent = 'This Month:';
        document.getElementById('periodAmount').textContent = 'Rs' + new Intl.NumberFormat('en-PK').format(totalMonthly);
        
        salesChart.data = monthlyData;
        salesChart.update();
        console.log('📊 Monthly Revenue View');
    });

    weeklyBtn.addEventListener('click', function() {
        weeklyBtn.classList.add('active');
        weeklyBtn.classList.remove('btn-outline-light');
        weeklyBtn.classList.add('btn-light');
        
        monthlyBtn.classList.remove('active', 'btn-light');
        monthlyBtn.classList.add('btn-outline-light');
        
        todayBtn.classList.remove('active', 'btn-light');
        todayBtn.classList.add('btn-outline-light');
        
        // Update header display
        const totalWeekly = @json($weeklySales).reduce((a, b) => a + b, 0);
        document.getElementById('periodLabel').textContent = 'This Week:';
        document.getElementById('periodAmount').textContent = 'Rs' + new Intl.NumberFormat('en-PK').format(totalWeekly);
        
        salesChart.data = weeklyData;
        salesChart.update();
        console.log('📈 Weekly Revenue View');
    });

    todayBtn.addEventListener('click', function() {
        todayBtn.classList.add('active');
        todayBtn.classList.remove('btn-outline-light');
        todayBtn.classList.add('btn-light');
        
        monthlyBtn.classList.remove('active', 'btn-light');
        monthlyBtn.classList.add('btn-outline-light');
        
        weeklyBtn.classList.remove('active', 'btn-light');
        weeklyBtn.classList.add('btn-outline-light');
        
        // Update header display
        const totalToday = @json($todaySales).reduce((a, b) => a + b, 0);
        document.getElementById('periodLabel').textContent = 'Today:';
        document.getElementById('periodAmount').textContent = 'Rs' + new Intl.NumberFormat('en-PK').format(totalToday);
        
        salesChart.data = todayData;
        salesChart.update();
        console.log('⏰ Today Revenue View');
    });

    // Simple auto-refresh every 5 minutes
    setInterval(function() {
        location.reload();
    }, 50000); // 50 seconds
    
    console.log('✅ Revenue Chart Ready - Shows completed orders only');
});
</script>
@endpush

@endsection
