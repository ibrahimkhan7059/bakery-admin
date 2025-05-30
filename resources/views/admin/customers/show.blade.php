@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">Customer Details</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('customers.index') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Customers
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Customer Information -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Customer Information</h5>
                    <div class="mb-3">
                        <label class="form-label text-muted">Name</label>
                        <p class="mb-0">{{ $customer->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p class="mb-0">{{ $customer->email }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Phone</label>
                        <p class="mb-0">{{ $customer->phone }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Address</label>
                        <p class="mb-0">{{ $customer->address ?? 'Not provided' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Joined Date</label>
                        <p class="mb-0">{{ $customer->created_at->format('M d, Y') }}</p>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary hover-lift">
                            <i class="bi bi-pencil-fill me-1"></i> Edit Customer
                        </a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger hover-lift" onclick="return confirm('Are you sure you want to delete this customer?')">
                                <i class="bi bi-trash-fill me-1"></i> Delete Customer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-lg glass-card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Order History</h5>
                    @if($customer->orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->orders as $order)
                                        <tr>
                                            <td>#{{ $order->id }}</td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'info') }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>₨{{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                <a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-info hover-lift" title="View Order">
                                                    <i class="bi bi-eye-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-cart-x display-4"></i>
                                <p class="mt-2">No orders found for this customer</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 