@extends('layouts.app')

@section('title', 'Create Order')

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
        <div class="card-header">
            <h4 class="card-title">Create Order</h4>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('orders.store') }}" method="POST" id="orderForm" class="needs-validation" novalidate>
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="customer_name" 
                                   class="form-control" 
                                   value="{{ old('customer_name') }}" 
                                   required
                                   minlength="3"
                                   maxlength="255"
                                   autocomplete="off">
                            <div class="form-text">Enter customer's full name</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer Email <span class="text-danger">*</span></label>
                            <input type="email" 
                                   name="customer_email" 
                                   class="form-control" 
                                   value="{{ old('customer_email') }}" 
                                   required
                                   autocomplete="off">
                            <div class="form-text">Enter customer's email address</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Customer Phone <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="customer_phone" 
                                   class="form-control" 
                                   value="{{ old('customer_phone') }}" 
                                   required
                                   pattern="[0-9]{11}"
                                   autocomplete="off">
                            <div class="form-text">Enter valid phone number (03XXXXXXXXX)</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                            <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" 
                                rows="2" required maxlength="255">{{ old('delivery_address') }}</textarea>
                            @error('delivery_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>


                <div class="mb-4">
                    <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash on Delivery</option>
                        <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment (PayFast)</option>
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
                                                {{ $product->name }} - PKR {{ number_format($product->price, 2) }} (Stock: {{ $product->stock }})
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

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('orders.index') }}" class="btn btn-primary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-label {
        font-weight: 500;
        color: var(--text-primary);
    }
    .text-danger {
        color: var(--accent-color) !important;
    }
    .is-invalid {
        border-color: var(--accent-color) !important;
    }
    .invalid-feedback {
        color: var(--accent-color);
        font-size: 0.875rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 0.2rem rgba(255, 111, 97, 0.25);
    }
    .form-text {
        color: var(--text-secondary);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    .alert-danger {
        background-color: #fff5f5;
        border-color: #fed7d7;
        color: var(--accent-color);
    }
</style>
@endpush

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
            productTotal.value = `PKR ${total.toFixed(2)}`;
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
            subtotal += parseFloat(input.value.replace('PKR', '')) || 0;
        });

        document.getElementById('subtotal').value = `PKR ${subtotal.toFixed(2)}`;
        document.getElementById('total').value = `PKR ${subtotal.toFixed(2)}`;
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
                input.value = 'PKR 0.00';
            } else if (input.classList.contains('quantity-input')) {
                input.value = '1';
            }
        });
        newRow.querySelector('.remove-product').disabled = false;
        productsContainer.appendChild(newRow);
        productCount++;
        updateProductRow(newRow);
    });

    // Real-time validation for customer name
    const customerNameInput = document.querySelector('input[name="customer_name"]');
    customerNameInput.addEventListener('input', function() {
        const value = this.value.trim();
        let errorMessage = '';
        
        if (value.length === 0) {
            errorMessage = 'Customer name is required';
        } else if (value.length < 3) {
            errorMessage = 'Name must be at least 3 characters long';
        } else if (/[0-9]/.test(value)) {
            errorMessage = 'Name cannot contain numbers';
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

    // Real-time validation for phone number (Pakistan format)
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

    // Form submission validation
    orderForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validate customer name
        const customerName = customerNameInput.value.trim();
        if (customerName.length < 3 || /[0-9]/.test(customerName)) {
            customerNameInput.classList.add('is-invalid');
            isValid = false;
        }

        // Validate phone number
        const phoneNumber = phoneInput.value.trim();
        if (!/^03[0-9]{9}$/.test(phoneNumber)) {
            phoneInput.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly');
        }
    });

    // Initialize first row
    updateProductRow(document.querySelector('.product-row'));
});
</script>
@endpush
@endsection
