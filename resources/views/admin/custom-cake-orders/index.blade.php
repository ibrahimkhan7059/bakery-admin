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
                            <th class="border-0 text-center">Actions</th>
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
                            <td class="text-center">
                                <div class="btn-group" role="group" aria-label="Custom Cake Order actions">
                                    <a href="{{ route('custom-cake-orders.show', $order) }}" 
                                       class="btn btn-sm btn-outline-info hover-lift" 
                                       title="View Order Details"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('custom-cake-orders.edit', $order) }}" 
                                       class="btn btn-sm btn-outline-primary hover-lift" 
                                       title="Edit Order"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger hover-lift" 
                                            title="Delete Order"
                                            data-bs-toggle="tooltip"
                                            onclick="deleteOrder({{ $order->id }}, '#{{ $order->id }}')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                                
                                <!-- Hidden form for deletion -->
                                <form id="delete-form-{{ $order->id }}" 
                                      action="{{ route('custom-cake-orders.destroy', $order) }}" 
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
    if (confirm(`Are you sure you want to delete custom cake order "${orderNumber}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('delete-form-' + orderId).submit();
    }
}

// Auto-refresh every 10 seconds
setInterval(function() {
    location.reload();
}, 10000);
</script>

@endsection 