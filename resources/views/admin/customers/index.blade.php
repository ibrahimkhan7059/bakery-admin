@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
    <div></div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('customers.create') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-plus-lg me-2"></i>Add New Customer
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Joined Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->address ?? 'Not provided' }}</td>
                                <td>{{ $customer->created_at->format('M d, Y') }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group" aria-label="Customer actions">
                                        <a href="{{ route('customers.show', $customer) }}" 
                                           class="btn btn-sm btn-outline-info hover-lift" 
                                           title="View Customer"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" 
                                           class="btn btn-sm btn-outline-primary hover-lift" 
                                           title="Edit Customer"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger hover-lift" 
                                                title="Delete Customer"
                                                data-bs-toggle="tooltip"
                                                onclick="deleteCustomer({{ $customer->id }}, '{{ $customer->name }}')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Hidden form for deletion -->
                                    <form id="delete-form-{{ $customer->id }}" 
                                          action="{{ route('customers.destroy', $customer) }}" 
                                          method="POST" 
                                          style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people display-4"></i>
                                        <p class="mt-2">No customers found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $customers->links() }}
            </div>
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
function deleteCustomer(customerId, customerName) {
    if (confirm(`Are you sure you want to delete customer "${customerName}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('delete-form-' + customerId).submit();
    }
}

// Auto-refresh every 10 seconds
setInterval(function() {
    location.reload();
}, 10000);
</script> 