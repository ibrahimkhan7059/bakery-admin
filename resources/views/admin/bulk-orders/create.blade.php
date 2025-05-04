@extends('layouts.app')

@section('title', 'Create Bulk Order')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Create Bulk Order</h1>
        <a href="{{ route('bulk-orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('bulk-orders.store') }}" method="POST" id="bulkOrderForm">
                @csrf
                
                <!-- Customer Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="font-weight-bold mb-3">Customer Information</h5>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="customer_phone">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" 
                                id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="customer_email">Email Address</label>
                            <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                                id="customer_email" name="customer_email" value="{{ old('customer_email') }}">
                            @error('customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="font-weight-bold mb-3">Delivery Information</h5>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="delivery_address">Delivery Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('delivery_address') is-invalid @enderror" 
                                id="delivery_address" name="delivery_address" rows="2" required>{{ old('delivery_address') }}</textarea>
                            @error('delivery_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="delivery_date">Delivery Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('delivery_date') is-invalid @enderror" 
                                id="delivery_date" name="delivery_date" value="{{ old('delivery_date') }}" required>
                            @error('delivery_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="delivery_time">Delivery Time</label>
                            <input type="time" class="form-control @error('delivery_time') is-invalid @enderror" 
                                id="delivery_time" name="delivery_time" value="{{ old('delivery_time') }}">
                            @error('delivery_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="font-weight-bold mb-3">Order Details</h5>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="order_type">Order Type <span class="text-danger">*</span></label>
                            <select class="form-control @error('order_type') is-invalid @enderror" 
                                id="order_type" name="order_type" required>
                                <option value="">Select Type</option>
                                <option value="birthday" {{ old('order_type') == 'birthday' ? 'selected' : '' }}>Birthday</option>
                                <option value="party" {{ old('order_type') == 'party' ? 'selected' : '' }}>Party</option>
                                <option value="corporate" {{ old('order_type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                                <option value="other" {{ old('order_type') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('order_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="event_details">Event Details</label>
                            <textarea class="form-control @error('event_details') is-invalid @enderror" 
                                id="event_details" name="event_details" rows="2">{{ old('event_details') }}</textarea>
                            @error('event_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="font-weight-bold mb-3">Payment Information</h5>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-control @error('payment_method') is-invalid @enderror" 
                                id="payment_method" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="advance_payment">Advance Payment</label>
                            <input type="number" step="0.01" class="form-control @error('advance_payment') is-invalid @enderror" 
                                id="advance_payment" name="advance_payment" value="{{ old('advance_payment', 0) }}">
                            @error('advance_payment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="font-weight-bold mb-3">Products</h5>
                        <div id="products-container">
                            <div class="product-item mb-3">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Product <span class="text-danger">*</span></label>
                                            <select class="form-control product-select" name="products[0][id]" required>
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}" 
                                                        data-price="{{ $product->price }}"
                                                        data-stock="{{ $product->stock }}">
                                                        {{ $product->name }} (₱{{ number_format($product->price, 2) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control product-quantity" 
                                                name="products[0][quantity]" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <input type="text" class="form-control" 
                                                name="products[0][notes]" placeholder="Special instructions">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-block remove-product" style="display: none;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" id="add-product">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </div>
                </div>

                <!-- Special Instructions -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="special_instructions">Special Instructions</label>
                            <textarea class="form-control @error('special_instructions') is-invalid @enderror" 
                                id="special_instructions" name="special_instructions" rows="2">{{ old('special_instructions') }}</textarea>
                            @error('special_instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Order
                        </button>
                        <a href="{{ route('bulk-orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productsContainer = document.getElementById('products-container');
    const addProductBtn = document.getElementById('add-product');
    let productCount = 1;

    // Add new product row
    addProductBtn.addEventListener('click', function() {
        const template = productsContainer.querySelector('.product-item').cloneNode(true);
        const newIndex = productCount++;
        
        // Update names and IDs
        template.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace('[0]', `[${newIndex}]`);
            input.value = '';
        });

        // Show remove button
        template.querySelector('.remove-product').style.display = 'block';

        productsContainer.appendChild(template);
    });

    // Remove product row
    productsContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-product')) {
            e.target.closest('.product-item').remove();
        }
    });

    // Update quantity validation based on stock
    productsContainer.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const option = e.target.options[e.target.selectedIndex];
            const stock = parseInt(option.dataset.stock);
            const quantityInput = e.target.closest('.product-item').querySelector('.product-quantity');
            quantityInput.max = stock;
        }
    });

    // Form validation
    document.getElementById('bulkOrderForm').addEventListener('submit', function(e) {
        const products = document.querySelectorAll('.product-item');
        let isValid = true;

        products.forEach(product => {
            const select = product.querySelector('.product-select');
            const quantity = product.querySelector('.product-quantity');
            
            if (!select.value || !quantity.value) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required product fields.');
        }
    });
});
</script>
@endpush
@endsection 