@extends('layouts.app')

@section('title', 'Create Order')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">Create New Order</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
        @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="customerName" class="form-control @error('customer_name') is-invalid @enderror" 
                                value="{{ old('customer_name') }}" required
                                pattern="[A-Za-z\s]+"
                                title="Customer name can only contain letters and spaces"
                                oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                            @error('customer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Customer Phone <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" id="customerPhone" class="form-control @error('customer_phone') is-invalid @enderror" 
                                value="{{ old('customer_phone') }}" required
                                pattern="(03[0-9]{9}|\+923[0-9]{9})"
                                title="Please enter a valid Pakistani phone number (e.g., 03001234567 or +923001234567)"
                                placeholder="03001234567 or +923001234567"
                                oninput="this.value = this.value.replace(/[^0-9+]/g, '')">
                            <small class="text-muted">Format: 03XXXXXXXXX or +923XXXXXXXXX</small>
                            @error('customer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                    <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" 
                        rows="2" required maxlength="255">{{ old('delivery_address') }}</textarea>
                    @error('delivery_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="gcash" {{ old('payment_method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Order Notes</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                        rows="2" maxlength="500">{{ old('notes') }}</textarea>
                    <small class="text-muted">Maximum 500 characters</small>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
        </div>

                <div class="mb-4">
                    <label class="form-label">Products <span class="text-danger">*</span></label>
                    <div id="products-container">
                        <div class="product-row mb-3">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <select name="products[0][id]" class="form-select product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">
                                                {{ $product->name }} - ₨{{ number_format($product->price, 2) }} (Stock: {{ $product->stock }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="products[0][quantity]" class="form-control quantity-input" 
                                        min="1" max="100" value="1" required
                                        oninput="validateQuantity(this)">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control product-total" readonly>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-product" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
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
                        <i class="bi bi-save me-1"></i> Create Order
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
    const productsContainer = document.getElementById('products-container');
    const addProductBtn = document.getElementById('add-product');
    const orderForm = document.getElementById('orderForm');
    let productCount = 1;

    function validateQuantity(input) {
        const productSelect = input.closest('.product-row').querySelector('.product-select');
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const maxStock = selectedOption ? parseInt(selectedOption.dataset.stock) : 0;
        
        if (parseInt(input.value) > maxStock) {
            input.setCustomValidity(`Maximum available stock is ${maxStock}`);
        } else if (parseInt(input.value) > 100) {
            input.setCustomValidity('Maximum quantity per product is 100');
        } else {
            input.setCustomValidity('');
        }
    }

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

        productSelect.addEventListener('change', function() {
            validateQuantity(quantityInput);
            calculateTotal();
        });
        
        quantityInput.addEventListener('input', function() {
            validateQuantity(this);
            calculateTotal();
        });

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

    // Form validation
    orderForm.addEventListener('submit', function(e) {
        const customerName = document.getElementById('customerName').value;
        const customerPhone = document.getElementById('customerPhone').value;
        
        if (!/^[A-Za-z\s]+$/.test(customerName)) {
            e.preventDefault();
            alert('Customer name can only contain letters and spaces');
            return false;
        }
        
        if (!/^(03[0-9]{9}|\+923[0-9]{9})$/.test(customerPhone)) {
            e.preventDefault();
            alert('Please enter a valid Pakistani phone number (e.g., 03001234567 or +923001234567)');
            return false;
        }

        // Check if at least one product is selected
        const hasProducts = Array.from(document.querySelectorAll('.product-select')).some(select => select.value !== '');
        if (!hasProducts) {
            e.preventDefault();
            alert('Please add at least one product to the order');
            return false;
        }
    });

    // Initialize first row
    updateProductRow(document.querySelector('.product-row'));
});
</script>
@endpush
@endsection
