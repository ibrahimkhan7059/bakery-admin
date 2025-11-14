@extends('layouts.app')

@section('title', 'Bulk Order Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <div>
<a href="{{ route('bulk-orders.index') }}" class="btn btn-secondary back-to-list-btn">
    <i class="fas fa-arrow-left"></i> Back to List
</a>
            <a href="{{ route('bulk-orders.invoice', $bulkOrder) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-file-invoice"></i> View Invoice
            </a>
            @if($bulkOrder->status !== 'completed')
                <a href="{{ route('bulk-orders.edit', $bulkOrder) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Order
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order Number:</strong> {{ $bulkOrder->order_number }}</p>
                            <p><strong>Order Type:</strong> {{ $bulkOrder->formatted_order_type }}</p>
                            <!-- <p><strong>Status:</strong> 
                                <span id="order-status-badge" class="badge badge-{{ 
                                    $bulkOrder->status === 'completed' ? 'success' : 
                                    ($bulkOrder->status === 'cancelled' ? 'danger' : 
                                    ($bulkOrder->status === 'ready' ? 'info' : 
                                    ($bulkOrder->status === 'processing' ? 'primary' : 'warning')))
                                }}">
                                    {{ ucfirst($bulkOrder->status) }}
                                </span>
                            </p> -->
                            <p><strong>Payment Status:</strong> 
                                <span class="badge badge-{{ $bulkOrder->payment_status === 'paid' ? 'success' : ($bulkOrder->payment_status === 'partial' ? 'warning' : 'danger') }}">
                                    {{ $bulkOrder->formatted_payment_status }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Created By:</strong> {{ $bulkOrder->user->name }}</p>
                            <p><strong>Created At:</strong> {{ $bulkOrder->created_at->format('M d, Y H:i') }}</p>
                            <p><strong>Last Updated:</strong> {{ $bulkOrder->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $bulkOrder->customer_name }}</p>
                            <p><strong>Phone:</strong> {{ $bulkOrder->customer_phone }}</p>
                            <p><strong>Email:</strong> {{ $bulkOrder->customer_email ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Delivery Address:</strong> {{ $bulkOrder->delivery_address }}</p>
                            <p><strong>Delivery Date:</strong> {{ $bulkOrder->delivery_date->format('M d, Y') }}</p>
                            <p><strong>Delivery Time:</strong> {{ $bulkOrder->delivery_time ? $bulkOrder->delivery_time->format('h:i A') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bulkOrder->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>PKR {{ number_format($item->price, 2) }}</td>
                                    <td>PKR {{ number_format($item->price * $item->quantity * (1 - $item->discount), 2) }}</td>
                                    <td>{{ $item->notes ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total Amount:</th>
                                    <th>PKR {{ number_format($bulkOrder->items->sum(function($item) { return $item->price * $item->quantity; }), 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Discount Amount:</th>
                                    <th>PKR {{ number_format(($bulkOrder->items->sum(function($item) { return $item->price * $item->quantity; }) - $bulkOrder->total_amount), 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Final Amount:</th>
                                    <th>PKR {{ number_format($bulkOrder->total_amount, 2) }}</th>
                                </tr>
                                @if($bulkOrder->advance_payment > 0)
                                <tr>
                                    <th colspan="4" class="text-end">Advance Payment:</th>
                                    <th>PKR {{ number_format($bulkOrder->advance_payment, 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Remaining Balance:</th>
                                    <th>PKR {{ number_format($bulkOrder->final_amount - $bulkOrder->advance_payment, 2) }}</th>
                                </tr>
                                @endif
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="col-md-4">
            <!-- Payment Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Payment Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Payment Method:</strong> {{ ucfirst($bulkOrder->payment_method) }}</p>
                    <p><strong>Advance Payment:</strong> PKR {{ number_format($bulkOrder->advance_payment, 2) }}</p>
                    <p><strong>Remaining Balance:</strong> PKR {{ number_format($bulkOrder->remaining_payment, 2) }}</p>
                </div>
            </div>

            <!-- Event Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Event Details</h6>
                </div>
                <div class="card-body">
                    <p><strong>Event Type:</strong> {{ $bulkOrder->formatted_order_type }}</p>
                    <p><strong>Event Details:</strong> {{ $bulkOrder->event_details ?? 'N/A' }}</p>
                    <p><strong>Special Instructions:</strong> {{ $bulkOrder->special_instructions ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Status Update -->
            @if($bulkOrder->status !== 'cancelled')
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Status</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('bulk-orders.update-status', $bulkOrder) }}" method="POST" id="status-update-form">
                        @csrf
                        <div class="form-group">
                            <select name="status" class="form-control" id="status-select">
                                <option value="pending" {{ $bulkOrder->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $bulkOrder->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="ready" {{ $bulkOrder->status === 'ready' ? 'selected' : '' }}>Ready</option>
                                <option value="completed" {{ $bulkOrder->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $bulkOrder->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-3" id="update-status-btn">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('status-update-form');
    const submitBtn = document.getElementById('update-status-btn');
    const statusSelect = document.getElementById('status-select');
    
    // Function to update status badge in real-time
    function updateStatusBadge(newStatus) {
        console.log('🔄 updateStatusBadge called with:', newStatus);
        
        const statusBadge = document.getElementById('order-status-badge');
        console.log('🔍 Status badge element:', statusBadge);
        
        if (statusBadge) {
            console.log('📍 Current badge class:', statusBadge.className);
            console.log('📍 Current badge text:', statusBadge.textContent);
            
            // Map status to Bootstrap badge classes (Bootstrap 4 format)
            const statusClasses = {
                'pending': 'badge-warning',
                'processing': 'badge-primary',
                'ready': 'badge-info',
                'completed': 'badge-success',
                'cancelled': 'badge-danger'
            };
            
            const badgeClass = statusClasses[newStatus] || 'badge-secondary';
            statusBadge.className = 'badge ' + badgeClass;
            statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            
            console.log('✅ Status badge updated to:', newStatus);
            console.log('📍 New badge class:', statusBadge.className);
            console.log('📍 New badge text:', statusBadge.textContent);
        } else {
            console.log('❌ Status badge not found - checking all badges on page');
            const allBadges = document.querySelectorAll('.badge');
            console.log('🔍 All badges found:', allBadges);
            allBadges.forEach((badge, index) => {
                console.log(`Badge ${index}:`, badge.id, badge.className, badge.textContent);
            });
        }
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            const selectedStatus = statusSelect.value;
            console.log('Form submission triggered');
            console.log('Selected Status:', selectedStatus);
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;
            
            // Submit form via AJAX
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    // Update the status badge immediately
                    updateStatusBadge(selectedStatus);
                    
                    // Show success state
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Updated!';
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-success');
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Status';
                        submitBtn.classList.remove('btn-success');
                        submitBtn.classList.add('btn-primary');
                        submitBtn.disabled = false;
                    }, 2000);
                    
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success';
                    alertDiv.innerHTML = `
                        <strong>Success!</strong> Status updated to "${selectedStatus.charAt(0).toUpperCase() + selectedStatus.slice(1)}" successfully.
                    `;
                    
                    // Insert alert at top of container
                    const container = document.querySelector('.container-fluid');
                    container.insertBefore(alertDiv, container.firstChild);
                    
                    // Auto-remove alert after 3 seconds
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.remove();
                        }
                    }, 3000);
                    
                } else {
                    throw new Error('Status update failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Show error state
                submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-danger');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Status';
                    submitBtn.classList.remove('btn-danger');
                    submitBtn.classList.add('btn-primary');
                    submitBtn.disabled = false;
                }, 3000);
                
                // Show error message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = `
                    <strong>Error!</strong> Failed to update status. Please try again.
                `;
                
                const container = document.querySelector('.container-fluid');
                container.insertBefore(alertDiv, container.firstChild);
                
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 3000);
            });
        });
    }
});
</script>
@endsection