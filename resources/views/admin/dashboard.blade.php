@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
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
        
        <div class="col-md-4">
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
        
        <div class="col-md-4">
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
                                    <th class="border-0">Products</th>
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
                                    <td>{{ $order->product }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>₨{{ number_format($order->total_amount, 2) }}</td>
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
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle overflow-hidden me-3" style="width: 50px; height: 50px;">
                            <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=89&auto=format&fit=crop" alt="Chocolate Cake" class="w-100 h-100 object-cover">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">Chocolate Cake</h6>
                                <span class="text-success">₨32.00</span>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-indigo-500" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle overflow-hidden me-3" style="width: 50px; height: 50px;">
                            <img src="https://images.unsplash.com/photo-1602351447937-745cb720612f?q=80&w=86&auto=format&fit=crop" alt="Croissants" class="w-100 h-100 object-cover">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">Croissants</h6>
                                <span class="text-success">₨12.50</span>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-indigo-500" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle overflow-hidden me-3" style="width: 50px; height: 50px;">
                            <img src="https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=85&auto=format&fit=crop" alt="Cupcakes" class="w-100 h-100 object-cover">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">Cupcakes</h6>
                                <span class="text-success">₨8.00</span>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                <div class="progress-bar bg-indigo-500" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
