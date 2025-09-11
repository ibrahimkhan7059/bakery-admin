@extends('layouts.app')

@section('title', 'Bulk Orders')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <div>
            <a href="{{ route('bulk-orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Order
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Processing</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['processing'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Confirmed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['confirmed'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Cancelled</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['cancelled'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('bulk-orders.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Search orders..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="status">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="order_type">
                        <option value="all" {{ request('order_type') == 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="birthday" {{ request('order_type') == 'birthday' ? 'selected' : '' }}>Birthday</option>
                        <option value="party" {{ request('order_type') == 'party' ? 'selected' : '' }}>Party</option>
                        <option value="corporate" {{ request('order_type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                        <option value="other" {{ request('order_type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="payment_status">
                        <option value="all" {{ request('payment_status') == 'all' ? 'selected' : '' }}>All Payments</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}" placeholder="Start Date">
                        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" placeholder="End Date">
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
<a href="{{ route('bulk-orders.index') }}" class="btn btn-secondary reset-btn">
    <i class="fas fa-sync"></i> Reset
</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Delivery Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>
                                    <div>{{ $order->customer_name }}</div>
                                    <small class="text-muted">{{ $order->customer_phone }}</small>
                                </td>
                                <td>{{ $order->formatted_order_type }}</td>
                                <td>
                                    <div>{{ $order->delivery_date->format('M d, Y') }}</div>
                                    @if($order->delivery_time)
                                        <small class="text-muted">{{ $order->delivery_time->format('h:i A') }}</small>
                                    @endif
                                </td>
                                <td>PKR {{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'cancelled' ? 'danger' : ($order->status === 'processing' ? 'info' : ($order->status === 'confirmed' ? 'secondary' : 'warning'))) }}">
                                        {{ $order->formatted_status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'partial' ? 'info' : 'warning') }}">
                                        {{ $order->formatted_payment_status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('bulk-orders.show', $order) }}" 
                                            class="btn btn-light btn-sm hover-lift" 
                                            title="View Details">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        @if($order->status !== 'completed')
                                        <a href="{{ route('bulk-orders.edit', $order) }}" 
                                            class="btn btn-light btn-sm hover-lift" 
                                            title="Edit Order">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        @endif
                                        <form action="{{ route('bulk-orders.destroy', $order) }}" 
                                            method="POST" 
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this order?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="btn btn-light btn-sm hover-lift" 
                                                title="Delete Order">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                        <p class="mb-0">No bulk orders found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 