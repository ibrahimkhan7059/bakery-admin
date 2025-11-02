@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">
            <i class="bi bi-info-circle me-2"></i>Order #{{ $customCakeOrder->id }} Details
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('custom-cake-orders.edit', $customCakeOrder) }}" class="btn btn-primary hover-lift me-2">
                <i class="bi bi-pencil me-1"></i> Edit Order
            </a>
            <a href="{{ route('custom-cake-orders.index') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Order Status Card -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-clipboard-check me-2"></i>Order Status
                    </h5>
                    
                    <div class="text-center mb-4">
                        <span class="badge bg-{{ $customCakeOrder->status === 'completed' ? 'success' : ($customCakeOrder->status === 'cancelled' ? 'danger' : ($customCakeOrder->status === 'in_progress' ? 'info' : 'warning')) }} rounded-pill px-4 py-2">
                            {{ ucfirst(str_replace('_', ' ', $customCakeOrder->status)) }}
                        </span>
                    </div>
                    
                    <p class="text-muted mb-0">Last Updated: {{ $customCakeOrder->updated_at->format('M d, Y h:i A') }}</p>
                    
                    <hr>
                    
                    <!-- Quick Status Update Form -->
                    <form action="{{ route('custom-cake-orders.update-status', $customCakeOrder) }}" method="POST" id="statusUpdateForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Update Status</label>
                            @if($customCakeOrder->status !== 'confirmed')
                            <button type="submit" name="status" value="confirmed" class="btn btn-outline-primary btn-sm d-block w-100 mb-2">
                                <i class="bi bi-check-circle me-1"></i> Mark as Confirmed
                            </button>
                            @endif
                            @if($customCakeOrder->status !== 'in_progress')
                            <button type="submit" name="status" value="in_progress" class="btn btn-outline-info btn-sm d-block w-100 mb-2">
                                <i class="bi bi-gear me-1"></i> Mark as In Progress
                            </button>
                            @endif
                            @if($customCakeOrder->status !== 'completed')
                            <button type="submit" name="status" value="completed" class="btn btn-outline-success btn-sm d-block w-100 mb-2">
                                <i class="bi bi-check-square me-1"></i> Mark as Completed
                            </button>
                            @endif
                            @if($customCakeOrder->status !== 'cancelled')
                            <button type="submit" name="status" value="cancelled" class="btn btn-outline-danger btn-sm d-block w-100">
                                <i class="bi bi-x-circle me-1"></i> Mark as Cancelled
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Order Details Card -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-person-circle me-2"></i>Customer Information
                    </h5>
                    
                    <dl class="row">
                        <dt class="col-sm-4">Customer Name:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->user->name }}</dd>
                        
                        <dt class="col-sm-4">Phone:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->user->phone ?? 'Not provided' }}</dd>
                        
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->user->email ?? 'Not provided' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Cake Specifications Card -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-cake2 me-2"></i>Cake Specifications
                    </h5>
                    
                    <dl class="row">
                        <dt class="col-sm-4">Cake Size:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->cake_size }}</dd>
                        
                        <dt class="col-sm-4">Flavor:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->cake_flavor }}</dd>
                        
                        <dt class="col-sm-4">Filling:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->cake_filling }}</dd>
                        
                        <dt class="col-sm-4">Frosting:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->cake_frosting }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Price Card -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card text-center">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        Order Total
                    </h5>
                    
                    <h3 class="mb-0">PKR {{ number_format($customCakeOrder->price, 2) }}</h3>
                    <p class="text-muted">Total Amount</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Delivery Information Card -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-truck me-2"></i>Delivery Information
                    </h5>
                    
                    <dl class="row">
                        <dt class="col-sm-4">Delivery Date:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->delivery_date ? $customCakeOrder->delivery_date->format('M d, Y') : 'Not specified' }}</dd>
                        
                        <dt class="col-sm-4">Delivery Address:</dt>
                        <dd class="col-sm-8">{{ $customCakeOrder->delivery_address ?? 'Not specified' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Special Instructions Card -->
        <div class="col-xl-4 col-lg-5 mb-4">
            @if($customCakeOrder->special_instructions)
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-chat-left-text me-2"></i>Special Instructions
                    </h5>
                    
                    <p class="mb-0">{{ $customCakeOrder->special_instructions }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if($customCakeOrder->reference_image)
    <div class="row">
        <!-- Reference Image Card -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-image me-2"></i>Reference Image
                    </h5>
                    
                    <img src="{{ asset('storage/' . $customCakeOrder->reference_image) }}" 
                         alt="Reference Image" 
                         class="img-fluid rounded"
                         style="max-height: 400px;">
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Order Timeline Card -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-clock-history me-2"></i>Order Timeline
                    </h5>
                    
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Order Placed</h6>
                                <p class="mb-0 text-muted">{{ $customCakeOrder->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        
                        @if($customCakeOrder->status !== 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Status Updated</h6>
                                <p class="mb-0 text-muted">Current status: {{ ucfirst(str_replace('_', ' ', $customCakeOrder->status)) }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}

.glass-card {
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.hover-lift {
    transition: transform 0.2s;
}

.hover-lift:hover {
    transform: translateY(-2px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusButtons = document.querySelectorAll('#statusUpdateForm button[type="submit"]');
    statusButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            const status = this.value;
            
            // Create a new form data object
            const formData = new FormData(form);
            formData.set('status', status);
            
            // Submit using fetch to ensure POST method
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            }).then(response => {
                if (response.ok) {
                    window.location.reload();
                } else {
                    console.error('Error updating status:', response.statusText);
                    alert('Error updating status. Please try again.');
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Error updating status. Please try again.');
            });
        });
    });
});
</script>

@endsection 