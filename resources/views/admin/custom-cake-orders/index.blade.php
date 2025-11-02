@extends('layouts.app')

@section('title', 'Custom Cake Orders')

@section('content')
<style>
    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }
    .cake-details {
        max-width: 250px;
    }
    .cake-details small {
        display: block;
        margin-top: 0.25rem;
        color: #6c757d;
    }
    .badge {
        font-weight: 500;
        letter-spacing: 0.3px;
    }
    .btn-group .btn {
        padding: 0.375rem 0.75rem;
    }
    .btn-group .btn i {
        font-size: 1rem;
    }
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
        background-color: #f8f9fa;
    }
    /* Hide default dropdown arrow */
    .position-relative select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: none;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <div></div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('custom-cake-orders.create') }}" class="btn btn-primary hover-lift me-2">
                <i class="bi bi-plus-lg me-1"></i> Create New Order
            </a>
            <a href="{{ route('admin.cake-config.index') }}" class="btn btn-outline-primary hover-lift">
                <i class="bi bi-sliders me-1"></i> Cake Config
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-cart3 fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-hourglass-split fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Today's Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('custom-cake-orders.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search orders..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="position-relative">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="position-relative">
                        <select name="per_page" class="form-select" onchange="this.form.submit()">
                            <option value="5" {{ request('per_page', 10) == '5' ? 'selected' : '' }}>5 per page</option>
                            <option value="10" {{ request('per_page', 10) == '10' ? 'selected' : '' }}>10 per page</option>
                            <option value="15" {{ request('per_page', 10) == '15' ? 'selected' : '' }}>15 per page</option>
                            <option value="25" {{ request('per_page', 10) == '25' ? 'selected' : '' }}>25 per page</option>
                            <option value="50" {{ request('per_page', 10) == '50' ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ request('per_page', 10) == '100' ? 'selected' : '' }}>100 per page</option>
                        </select>
                        <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Order ID</th>
                            <th class="border-0">Customer</th>
                            <th class="border-0">Cake Details</th>
                            <th class="border-0">Size</th>
                            <th class="border-0">Price</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Delivery Date</th>
                            <th class="border-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td>
                                <strong>Flavor:</strong> {{ $order->cake_flavor }}<br>
                                <small class="text-muted">
                                    <strong>Filling:</strong> {{ $order->cake_filling }}<br>
                                    <strong>Frosting:</strong> {{ $order->cake_frosting }}
                                </small>
                            </td>
                            <td>{{ $order->cake_size }}</td>
                            <td>PKR {{ number_format($order->price, 2) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $statusColor = $statusColors[$order->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $statusColor }} text-white" style="font-size: 0.85rem; padding: 0.5em 0.8em;">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>{{ $order->delivery_date->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('custom-cake-orders.show', $order) }}" 
                                       class="btn btn-sm btn-outline-primary hover-lift" 
                                       title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('custom-cake-orders.edit', $order) }}" 
                                       class="btn btn-sm btn-outline-secondary hover-lift" 
                                       title="Edit Order">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('custom-cake-orders.destroy', $order) }}" 
                                          method="POST" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this order?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="btn btn-sm btn-outline-danger hover-lift" 
                                                title="Delete Order">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4"></i>
                                    <p class="mt-2 mb-0">No custom cake orders found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $orders->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection 