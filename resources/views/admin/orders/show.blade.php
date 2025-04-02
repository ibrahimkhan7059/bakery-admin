@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Order Details</h2>

    <div class="card">
        <div class="card-header">
            Order #{{ $order->id }} - <strong>{{ $order->customer_name }}</strong>
        </div>
        <div class="card-body">
            <p><strong>Product:</strong> {{ $order->product }}</p>
            <p><strong>Quantity:</strong> {{ $order->quantity }}</p>
            <p><strong>Price:</strong> ${{ $order->price }}</p>
            <p><strong>Total Price:</strong> ${{ $order->total_price }}</p>
            <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <a href="{{ route('orders.index') }}" class="btn btn-secondary mt-3">⬅ Back to Orders</a>
</div>
@endsection
