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
                            <p><strong>Status:</strong> 
                                <span class="badge badge-{{ $bulkOrder->status === 'completed' ? 'success' : ($bulkOrder->status === 'cancelled' ? 'danger' : 'warning') }}">
                                    {{ $bulkOrder->formatted_status }}
                                </span>
                            </p>
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
                                    <th>Discount</th>
                                    <th>Subtotal</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bulkOrder->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>₨{{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->discount * 100 }}%</td>
                                    <td>₨{{ number_format($item->price * $item->quantity * (1 - $item->discount), 2) }}</td>
                                    <td>{{ $item->notes ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">Total Amount:</th>
                                    <th>₨{{ number_format($bulkOrder->total_amount, 2) }}</th>
                                </tr>
                                @if($bulkOrder->advance_payment > 0)
                                <tr>
                                    <th colspan="4" class="text-end">Advance Payment:</th>
                                    <th>₨{{ number_format($bulkOrder->advance_payment, 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">Remaining Balance:</th>
                                    <th>₨{{ number_format($bulkOrder->total_amount - $bulkOrder->advance_payment, 2) }}</th>
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
                    <p><strong>Advance Payment:</strong> ₨{{ number_format($bulkOrder->advance_payment, 2) }}</p>
                    <p><strong>Remaining Balance:</strong> ₨{{ number_format($bulkOrder->remaining_payment, 2) }}</p>
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
            @if($bulkOrder->status !== 'completed' && $bulkOrder->status !== 'cancelled')
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Status</h6>
                </div>
                <div class="card-body">
                    @if($bulkOrder->status === 'pending' || $bulkOrder->status === 'confirmed')
                    <form action="{{ route('bulk-orders.update-status', $bulkOrder) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="processing">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-cog"></i> Start Processing
                        </button>
                    </form>
                    @endif

                    @if($bulkOrder->status === 'processing')
                    <form action="{{ route('bulk-orders.update-status', $bulkOrder) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Mark as Completed
                        </button>
                    </form>
                    @endif

                    @if($bulkOrder->status !== 'cancelled')
                    <form action="{{ route('bulk-orders.update-status', $bulkOrder) }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                            <i class="fas fa-times"></i> Cancel Order
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection 