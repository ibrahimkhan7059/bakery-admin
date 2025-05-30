@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">Order Details #{{ $order->id }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('orders.edit', $order) }}" class="btn btn-primary me-2 hover-lift">
                <i class="bi bi-pencil-square me-1"></i> Edit Order
            </a>
            <a href="{{ route('orders.print-receipt', $order) }}" class="btn btn-success me-2 hover-lift" target="_blank">
                <i class="bi bi-printer me-1"></i> Print Receipt
            </a>
<a href="{{ route('orders.index') }}" class="btn btn-primary hover-lift">
    <i class="bi bi-arrow-left me-1"></i> Back to Orders
</a>
        </div>
    </div>

    <div class="row">
        <!-- Order Information -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-gray-800 fw-bold mb-4">Order Information</h5>
                    <div class="mb-3">
                        <label class="text-muted small">Order ID</label>
                        <p class="mb-0">{{ $order->id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Order Date</label>
                        <p class="mb-0">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <p class="mb-0">{!! $order->formatted_status !!}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Payment Status</label>
                        <p class="mb-0">{!! $order->formatted_payment_status !!}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Priority</label>
                        <p class="mb-0">{!! $order->formatted_priority !!}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-gray-800 fw-bold mb-4">Customer Information</h5>
                    <div class="mb-3">
                        <label class="text-muted small">Customer Name</label>
                        <p class="mb-0">{{ $order->customer_name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Phone Number</label>
                        <p class="mb-0">{{ $order->customer_phone }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Delivery Address</label>
                        <p class="mb-0">{{ $order->delivery_address }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Payment Method</label>
                        <p class="mb-0">{{ ucfirst($order->payment_method) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-gray-800 fw-bold mb-4">Order Summary</h5>
                    <div class="mb-3">
                        <label class="text-muted small">Total Items</label>
                        <p class="mb-0">{{ $order->products->sum('pivot.quantity') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Subtotal</label>
                        <p class="mb-0">₨{{ number_format($receipt['subtotal'], 2) }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Discount</label>
                        <p class="mb-0">₨{{ number_format($receipt['discount'], 2) }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Total Amount</label>
                        <p class="mb-0 fw-bold">₨{{ number_format($order->total_amount, 2) }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Estimated Delivery</label>
                        <p class="mb-0">{{ $order->estimated_delivery_time ? $order->estimated_delivery_time->format('M d, Y h:i A') : 'Not estimated yet' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
        <div class="card-body">
            <h5 class="card-title text-gray-800 fw-bold mb-4">Order Items</h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Total</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipt['items'] as $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>₨{{ number_format($item['price'], 2) }}</td>
                            <td>₨{{ number_format($item['discount'], 2) }}</td>
                            <td>₨{{ number_format($item['total'], 2) }}</td>
                            <td>{{ $item['notes'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Notes -->
    @if($order->notes)
    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <h5 class="card-title text-gray-800 fw-bold mb-4">Order Notes</h5>
            <p class="mb-0">{{ $order->notes }}</p>
        </div>
    </div>
    @endif
</div>
@endsection
