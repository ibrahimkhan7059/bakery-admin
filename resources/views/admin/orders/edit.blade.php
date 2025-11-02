@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <div></div>
        <div class="btn-toolbar mb-2 mb-md-0">
<a href="{{ route('orders.index') }}" class="btn btn-primary hover-lift">
    <i class="bi bi-arrow-left me-1"></i> Back to Orders
</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <form action="{{ route('orders.update', $order) }}" method="POST" id="orderForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $order->customer_name) }}" required>
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Customer Phone</label>
                            <input type="text" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone', $order->customer_phone) }}" required>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Delivery Address</label>
                    <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" rows="2" required>{{ old('delivery_address', $order->delivery_address) }}</textarea>
                    @error('delivery_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash" {{ old('payment_method', $order->payment_method) == 'cash' ? 'selected' : '' }}>Cash on Delivery</option>
                                <option value="online" {{ old('payment_method', $order->payment_method) == 'online' ? 'selected' : '' }}>Online Payment (PayFast)</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label">Order Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">Select Status</option>
                                <option value="pending" {{ old('status', $order->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ old('status', $order->status) == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="ready" {{ old('status', $order->status) == 'ready' ? 'selected' : '' }}>Ready</option>
                                <option value="completed" {{ old('status', $order->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $order->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-4">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                <option value="">Select Payment Status</option>
                                <option value="pending" {{ old('payment_status', $order->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ old('payment_status', $order->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="failed" {{ old('payment_status', $order->payment_status) == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                            @error('payment_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Order Notes</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes', $order->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
        </div>

                <div class="mb-4">
                    <label class="form-label">Products</label>
                    <div id="products-container">
                        @foreach($order->products as $index => $product)
                        <div class="product-row mb-3">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <select name="products[{{ $index }}][id]" class="form-select product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->price }}" {{ $p->id == $product->id ? 'selected' : '' }}>
                                                {{ $p->name }} - ₨{{ number_format($p->price, 2) }}
                                            </option>
                                        @endforeach
            </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="products[{{ $index }}][quantity]" class="form-control quantity-input" min="1" value="{{ $product->pivot->quantity }}" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control product-total" readonly>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-product" {{ $loop->first ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-2 hover-lift" id="add-product">
                        <i class="bi bi-plus-lg me-1"></i> Add Product
                    </button>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control" id="subtotal" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Total Amount</label>
                            <input type="text" class="form-control" id="total" readonly>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary hover-lift">
                        <i class="bi bi-save me-1"></i> Update Order
                    </button>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary hover-lift">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time validation for customer name
    const customerNameInput = document.querySelector('input[name="customer_name"]');
    customerNameInput.addEventListener('input', function() {
        const value = this.value.trim();
        const errorDiv = this.nextElementSibling.nextElementSibling;
        
        if (value.length < 3) {
            this.classList.add('is-invalid');
            if (!errorDiv.classList.contains('invalid-feedback')) {
                const newErrorDiv = document.createElement('div');
                newErrorDiv.className = 'invalid-feedback';
                newErrorDiv.textContent = 'Name must be at least 3 characters long';
                this.parentNode.insertBefore(newErrorDiv, this.nextElementSibling.nextElementSibling);
            }
        } else if (!/^[A-Za-z\s]+$/.test(value)) {
            this.classList.add('is-invalid');
            if (!errorDiv.classList.contains('invalid-feedback')) {
                const newErrorDiv = document.createElement('div');
                newErrorDiv.className = 'invalid-feedback';
                newErrorDiv.textContent = 'Name should only contain letters and spaces';
                this.parentNode.insertBefore(newErrorDiv, this.nextElementSibling.nextElementSibling);
            }
        } else {
            this.classList.remove('is-invalid');
            if (errorDiv.classList.contains('invalid-feedback')) {
                errorDiv.remove();
            }
        }
    });

    // Real-time validation for phone number
    const phoneInput = document.querySelector('input[name="customer_phone"]');
    phoneInput.addEventListener('input', function() {
        const value = this.value.trim();
        let errorMessage = '';
        
        // Remove any non-digit characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        if (value.length === 0) {
            errorMessage = 'Phone number is required';
        } else if (value.length < 11) {
            errorMessage = 'Phone number must be 11 digits';
        } else if (value.length > 11) {
            errorMessage = 'Phone number cannot exceed 11 digits';
        } else if (!value.startsWith('03')) {
            errorMessage = 'Phone number must start with 03';
        } else if (!/^[0-9]+$/.test(value)) {
            errorMessage = 'Phone number can only contain digits';
        }

        // Show or hide error message
        const errorDiv = this.nextElementSibling.nextElementSibling;
        if (errorMessage) {
            this.classList.add('is-invalid');
            if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                const newErrorDiv = document.createElement('div');
                newErrorDiv.className = 'invalid-feedback';
                newErrorDiv.textContent = errorMessage;
                this.parentNode.insertBefore(newErrorDiv, this.nextElementSibling.nextElementSibling);
            } else {
                errorDiv.textContent = errorMessage;
            }
        } else {
            this.classList.remove('is-invalid');
            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                errorDiv.remove();
            }
        }
    });

    // Real-time validation for delivery address
    const addressInput = document.querySelector('textarea[name="delivery_address"]');
    addressInput.addEventListener('input', function() {
        const value = this.value.trim();
        const errorDiv = this.nextElementSibling.nextElementSibling;
        
        if (value.length < 10) {
            this.classList.add('is-invalid');
            if (!errorDiv.classList.contains('invalid-feedback')) {
                const newErrorDiv = document.createElement('div');
                newErrorDiv.className = 'invalid-feedback';
                newErrorDiv.textContent = 'Address must be at least 10 characters long';
                this.parentNode.insertBefore(newErrorDiv, this.nextElementSibling.nextElementSibling);
            }
        } else if (value.length > 500) {
            this.classList.add('is-invalid');
            if (!errorDiv.classList.contains('invalid-feedback')) {
                const newErrorDiv = document.createElement('div');
                newErrorDiv.className = 'invalid-feedback';
                newErrorDiv.textContent = 'Address cannot exceed 500 characters';
                this.parentNode.insertBefore(newErrorDiv, this.nextElementSibling.nextElementSibling);
            }
        } else {
            this.classList.remove('is-invalid');
            if (errorDiv.classList.contains('invalid-feedback')) {
                errorDiv.remove();
            }
        }
    });

    const productsContainer = document.getElementById('products-container');
    const addProductBtn = document.getElementById('add-product');
    const orderForm = document.getElementById('orderForm');
    let productCount = {{ count($order->products) }};

    function updateProductRow(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const productTotal = row.querySelector('.product-total');
        const removeBtn = row.querySelector('.remove-product');

        function calculateTotal() {
            const price = parseFloat(productSelect.options[productSelect.selectedIndex]?.dataset.price || 0);
            const quantity = parseInt(quantityInput.value) || 0;
            const total = price * quantity;
            productTotal.value = `₨${total.toFixed(2)}`;
            updateOrderTotals();
        }

        productSelect.addEventListener('change', calculateTotal);
        quantityInput.addEventListener('input', calculateTotal);

        removeBtn.addEventListener('click', function() {
            if (productCount > 1) {
                row.remove();
                productCount--;
                updateOrderTotals();
            }
        });

        calculateTotal();
    }

    function updateOrderTotals() {
        let subtotal = 0;
        document.querySelectorAll('.product-total').forEach(input => {
            subtotal += parseFloat(input.value.replace('₨', '')) || 0;
        });

        document.getElementById('subtotal').value = `₨${subtotal.toFixed(2)}`;
        document.getElementById('total').value = `₨${subtotal.toFixed(2)}`;
    }

    addProductBtn.addEventListener('click', function() {
        const newRow = document.querySelector('.product-row').cloneNode(true);
        const inputs = newRow.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.name) {
                const index = productCount;
                input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
            }
            input.value = '';
            if (input.classList.contains('product-total')) {
                input.value = '₨0.00';
            } else if (input.classList.contains('quantity-input')) {
                input.value = '1';
            }
        });
        newRow.querySelector('.remove-product').disabled = false;
        productsContainer.appendChild(newRow);
        productCount++;
        updateProductRow(newRow);
    });

    // Initialize all rows
    document.querySelectorAll('.product-row').forEach(row => {
        updateProductRow(row);
    });
});
</script>
@endpush
@endsection
