@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Orders</h2>

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    

    <!-- ✅ Search Form -->
<form method="GET" action="{{ route('orders.index') }}" class="mb-3">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search by Customer or Product"
               value="{{ request()->search }}">
        <button type="submit" class="btn btn-primary">🔍 Search</button>
    </div>
</form>

    <form method="GET" action="{{ route('orders.index') }}" class="mb-3">
    <select name="status" class="form-select" onchange="this.form.submit()">
        <option value="all">All</option>
        <option value="pending">Pending</option>
        <option value="processing">Processing</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
    </select>
</form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer Name</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->customer_phone }}</td>
                    <td>{{ $order->product }}</td>
                    <td>{{ $order->quantity }}</td>
                    <td>${{ $order->price }}</td>
                    <td>${{ $order->total_price }}</td>
                    <td>{{ $order->formatted_status }}</td>

                    <td>
                        <!-- 👁 View Details Button -->
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-info btn-sm">👁 View Details</a>
                        <!-- ✏ Edit Button -->
                        <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-warning btn-sm">✏ Edit</a>

                        <!-- 🗑 Delete Button -->
                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">🗑 Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
    {{ $orders->links() }}
</div>
</div>
@endsection
