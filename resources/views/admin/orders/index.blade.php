@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <div></div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('orders.create') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-plus-lg me-1"></i> Create Order
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('orders.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search orders..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>Ready</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-select">
                        <option value="all" {{ request('payment_status') == 'all' ? 'selected' : '' }}>All Payment</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        <option value="5" {{ request('per_page', 10) == '5' ? 'selected' : '' }}>5 per page</option>
                        <option value="10" {{ request('per_page', 10) == '10' ? 'selected' : '' }}>10 per page</option>
                        <option value="15" {{ request('per_page', 10) == '15' ? 'selected' : '' }}>15 per page</option>
                        <option value="25" {{ request('per_page', 10) == '25' ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page', 10) == '50' ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page', 10) == '100' ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
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
                            <th class="border-0">Products</th>
                            <th class="border-0">Total</th>
                            <th class="border-0">Status</th>
                            <th class="border-0">Date</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>#ORD-{{ $order->id }}</td>
                            <td>{{ $order->customer_name }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary">{{ $order->items_count }} items</span>
                                </div>
                            </td>
                            <td>PKR {{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($order->status == 'processing')
                                    <span class="badge bg-info">Processing</span>
                                @elseif($order->status == 'ready')
                                    <span class="badge bg-purple">Ready</span>
                                @elseif($order->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($order->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                            <td class="text-center">
                                <div class="btn-group" role="group" aria-label="Order actions">
                                    <a href="{{ route('orders.show', $order->id) }}" 
                                       class="btn btn-sm btn-outline-info hover-lift" 
                                       title="View Order"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('orders.edit', $order->id) }}" 
                                       class="btn btn-sm btn-outline-primary hover-lift" 
                                       title="Edit Order"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger hover-lift" 
                                            title="Delete Order"
                                            data-bs-toggle="tooltip"
                                            onclick="deleteOrder({{ $order->id }}, '#ORD-{{ $order->id }}')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                                
                                <!-- Hidden form for deletion -->
                                <form id="delete-form-{{ $order->id }}" 
                                      action="{{ route('orders.destroy', $order->id) }}" 
                                      method="POST" 
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">No orders found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm justify-content-center compact-pagination">
                    {{-- Previous Page Link --}}
                    @if ($orders->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link compact-page-link">
                                <i class="bi bi-chevron-left"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link compact-page-link" href="{{ $orders->previousPageUrl() }}" rel="prev">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @if($orders->lastPage() > 1)
                        @foreach ($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                            @if ($page == $orders->currentPage())
                                <li class="page-item active">
                                    <span class="page-link compact-page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link compact-page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif

                    {{-- Next Page Link --}}
                    @if ($orders->hasMorePages())
                        <li class="page-item">
                            <a class="page-link compact-page-link" href="{{ $orders->nextPageUrl() }}" rel="next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link compact-page-link">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    @endif
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

/* Compact Pagination Styles */
.compact-pagination .page-link {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
    min-width: 32px !important;
    height: 32px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 6px !important;
    margin: 0 2px !important;
}

.compact-pagination .page-item {
    margin: 0 !important;
}

.compact-pagination .page-link i {
    font-size: 0.75rem !important;
}

.compact-pagination .page-item.active .page-link {
    background-color: #FF6F61 !important;
    border-color: #FF6F61 !important;
    color: white !important;
    font-weight: 600 !important;
}

.compact-pagination .page-link:hover {
    background-color: rgba(255, 111, 97, 0.1) !important;
    border-color: #FF6F61 !important;
    color: #FF6F61 !important;
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

.bs-tooltip-top .tooltip-arrow::before {
    border-top-color: #333 !important;
}

.bs-tooltip-bottom .tooltip-arrow::before {
    border-bottom-color: #333 !important;
}

.bs-tooltip-start .tooltip-arrow::before {
    border-left-color: #333 !important;
}

.bs-tooltip-end .tooltip-arrow::before {
    border-right-color: #333 !important;
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
function deleteOrder(orderId, orderNumber) {
    if (confirm(`Are you sure you want to delete order "${orderNumber}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('delete-form-' + orderId).submit();
    }
}

// Auto-refresh every 10 seconds
setInterval(function() {
    location.reload();
}, 10000);
</script>
