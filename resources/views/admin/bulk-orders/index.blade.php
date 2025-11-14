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
                    <div class="position-relative">
                        <select class="form-control" name="status">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="position-relative">
                        <select class="form-control" name="order_type">
                            <option value="all" {{ request('order_type') == 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="birthday" {{ request('order_type') == 'birthday' ? 'selected' : '' }}>Birthday</option>
                            <option value="party" {{ request('order_type') == 'party' ? 'selected' : '' }}>Party</option>
                            <option value="corporate" {{ request('order_type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                            <option value="other" {{ request('order_type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="position-relative">
                        <select class="form-control" name="payment_status">
                            <option value="all" {{ request('payment_status') == 'all' ? 'selected' : '' }}>All Payments</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                        <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="position-relative">
                        <select name="per_page" class="form-control" onchange="this.form.submit()">
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
                            <th class="text-center">Actions</th>
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
                                <td class="text-center">
                                    <div class="btn-group" role="group" aria-label="Bulk order actions">
                                        <a href="{{ route('bulk-orders.show', $order) }}" 
                                           class="btn btn-sm btn-outline-info hover-lift" 
                                           title="View Details"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($order->status !== 'completed')
                                        <a href="{{ route('bulk-orders.edit', $order) }}" 
                                           class="btn btn-sm btn-outline-primary hover-lift" 
                                           title="Edit Order"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        @endif
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger hover-lift" 
                                                title="Delete Order"
                                                data-bs-toggle="tooltip"
                                                onclick="deleteBulkOrder({{ $order->id }}, '{{ $order->order_number }}')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Hidden form for deletion -->
                                    <form id="delete-form-{{ $order->id }}" 
                                          action="{{ route('bulk-orders.destroy', $order) }}" 
                                          method="POST" 
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
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
            @if($orders->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-center">
                            {{-- Previous Page Link --}}
                            @if ($orders->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $orders->previousPageUrl() }}" rel="prev">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @if($orders->lastPage() > 1)
                                @foreach ($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                                    @if ($page == $orders->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            @endif

                            {{-- Next Page Link --}}
                            @if ($orders->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $orders->nextPageUrl() }}" rel="next">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

<style>
/* Action buttons styling */
.btn-group .btn {
    border-radius: 6px !important;
    margin: 0 2px;
}

.btn-group .btn i {
    font-size: 0.875rem;
}

.btn-outline-info:hover {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-success:hover {
    background-color: #198754;
    border-color: #198754;
}

/* Tooltip positioning fix */
.tooltip {
    font-size: 12px !important;
}

.tooltip-inner {
    max-width: 200px;
    padding: 6px 10px;
    background-color: #333 !important;
    border-radius: 4px;
}

.bs-tooltip-bottom .tooltip-arrow::before {
    border-bottom-color: #333 !important;
}
</style>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'bottom',
            delay: { show: 300, hide: 100 }
        });
    });
});

// Sweet delete confirmation
function deleteBulkOrder(orderId, orderNumber) {
    if (confirm(`Are you sure you want to delete bulk order "${orderNumber}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('delete-form-' + orderId).submit();
    }
}

// Auto-refresh every 10 seconds
setInterval(function() {
    location.reload();
}, 10000);
</script> 