@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Order</h2>

    <form action="{{ route('orders.update', $order->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label>Order ID:</label>
            <input type="text" class="form-control" value="{{ $order->id }}" disabled>
        </div>

        <div class="mb-3">
            <label>Status:</label>
            <select name="status" class="form-control">
                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Order</button>
    </form>
</div>
@endsection
