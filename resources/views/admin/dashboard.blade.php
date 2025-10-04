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
                            <h3 class="fw-bold mb-0">{{ $totalOrders }}</h3>
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
                            <h3 class="fw-bold mb-0">{{ $totalProducts }}</h3>
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
                            <h3 class="fw-bold mb-0">{{ $totalCustomers }}</h3>
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
                                    <p class="text-muted mb-1 fs-6">Total Revenue</p>
                            <h3 class="fw-bold mb-0">Rs{{ number_format($totalRevenue, 0) }}</h3>
                            <p class="small text-success mt-2 mb-0"><i class="bi bi-arrow-up-right"></i> Completed orders</p>
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
                                        @elseif($order->status == 'completed')
                                            <span class="badge bg-success">Delivered</span>
                                        @elseif($order->status == 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
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
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(45deg, #6366f1, #a855f7);">
                                        <i class="bi bi-tag-fill text-white"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $category->name }}</h6>
                                        <small class="text-muted">{{ $category->products_count }} products</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">{{ $category->created_at->format('M d') }}</small>
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
                        <div class="card-header bg-transparent py-3">
                            <h5 class="mb-0 fw-bold text-gray-800">Monthly Sales Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-5">
                                <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-0 mt-3">Sales chart coming soon...</p>
                                <small class="text-muted">Integrate with Chart.js for detailed analytics</small>
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
@endsection
