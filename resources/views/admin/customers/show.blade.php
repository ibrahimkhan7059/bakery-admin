@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">Customer Details</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary hover-lift me-2">
                <i class="bi bi-arrow-left me-1"></i> Back to Customers
            </a>
            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary hover-lift">
                <i class="bi bi-pencil me-1"></i> Edit Customer
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Customer Information -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h5 class="card-title mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle overflow-hidden me-3" style="width: 64px; height: 64px;">
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary bg-opacity-10">
                                    <i class="bi bi-person-fill text-primary fs-3"></i>
                                </div>
                            </div>
                            <div>
                                <h4 class="mb-1">{{ $customer->name }}</h4>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-envelope me-1"></i> {{ $customer->email }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Phone</label>
                            <p class="mb-0">{{ $customer->phone ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Status</label>
                            <p class="mb-0">
                                @if($customer->status == 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted">Address</label>
                            <p class="mb-0">{{ $customer->address ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Member Since</label>
                            <p class="mb-0">{{ $customer->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Last Updated</label>
                            <p class="mb-0">{{ $customer->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Stats -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h5 class="card-title mb-0">Customer Stats</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-primary bg-opacity-10">
                                <h6 class="text-primary mb-1">Total Orders</h6>
                                <h3 class="mb-0">{{ $customer->orders_count }}</h3>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-success bg-opacity-10">
                                <h6 class="text-success mb-1">Total Spent</h6>
                                <h3 class="mb-0">₨{{ number_format($customer->total_spent, 2) }}</h3>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="mt-4">
                        <h6 class="mb-3">Recent Orders</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Order ID</th>
                                        <th class="border-0">Date</th>
                                        <th class="border-0">Amount</th>
                                        <th class="border-0">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->orders()->latest()->take(5)->get() as $order)
                                    <tr>
                                        <td>#ORD-{{ $order->id }}</td>
                                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                                        <td>₨{{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            @if($order->status == 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($order->status == 'processing')
                                                <span class="badge bg-info">Processing</span>
                                            @elseif($order->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($order->status == 'cancelled')
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3">No orders found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($customer->orders_count > 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('customers.orders', $customer->id) }}" class="btn btn-outline-primary hover-lift">
                                View All Orders
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 