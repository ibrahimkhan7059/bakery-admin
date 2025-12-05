@extends('layouts.app')

@section('title', 'Create Bulk Order')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <div>
<a href="{{ route('bulk-orders.index') }}" class="btn btn-secondary back-to-list-btn">
    <i class="fas fa-arrow-left"></i> Back to List
</a>
        </div>
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
                            <div id="name-error" class="invalid-feedback" style="display: none;"></div>
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
                            <div id="phone-error" class="invalid-feedback" style="display: none;"></div>
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
                            <div id="email-error" class="invalid-feedback" style="display: none;"></div>
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
                                id="delivery_date" name="delivery_date" value="{{ old('delivery_date') }}" 
                                min="{{ date('Y-m-d', strtotime('+5 days')) }}" required>
                            <small class="form-text text-muted">Minimum delivery date is 5 days from today</small>
                            <div id="date-error" class="invalid-feedback" style="display: none;"></div>
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
                            <div class="position-relative">
                                <select class="form-control @error('order_type') is-invalid @enderror" 
                                    id="order_type" name="order_type" required>
                                    <option value="">Select Type</option>
                                    <option value="birthday" {{ old('order_type') == 'birthday' ? 'selected' : '' }}>Birthday</option>
                                    <option value="party" {{ old('order_type') == 'party' ? 'selected' : '' }}>Party</option>
                                    <option value="corporate" {{ old('order_type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                                    <option value="other" {{ old('order_type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                            </div>
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
                            <div class="position-relative">
                                <select class="form-control @error('payment_method') is-invalid @enderror" 
                                    id="payment_method" name="payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash on Delivery</option>
                                    <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Payment (PayFast)</option>
                                </select>
                                <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                            </div>
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Product <span class="text-danger">*</span></label>
                                            <div class="position-relative">
                                                <select class="form-control product-select" name="products[0][id]" required>
                                                    <option value="">Select Product</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" 
                                                            data-price="{{ $product->price }}"
                                                            data-stock="{{ $product->stock }}">
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Unit Price</label>
                                            <input type="text" class="form-control product-price" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control product-quantity" 
                                                name="products[0][quantity]" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Subtotal</label>
                                            <input type="text" class="form-control product-subtotal" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Notes</label>
                                            <input type="text" class="form-control" 
                                                name="products[0][notes]" placeholder="Special instructions">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
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
                        <button type="button" class="btn btn-success" id="add-product">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="row mb-4">
                    <div class="col-md-6 offset-md-6">
                        <div class="form-group">
                            <label for="total_amount">Total Amount</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" readonly>
                        </div>
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

                <div class="text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Bulk Order
                    </button>
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

    // Function to format currency
    function formatCurrency(amount) {
        return '₨' + parseFloat(amount).toFixed(2);
    }

    // Function to calculate subtotal and update total
    function calculateTotals() {
        let total = 0;
        document.querySelectorAll('.product-item').forEach(item => {
            const price = parseFloat(item.querySelector('.product-price').value.replace('₨', '')) || 0;
            const quantity = parseInt(item.querySelector('.product-quantity').value) || 0;
            const subtotal = price * quantity;
            item.querySelector('.product-subtotal').value = formatCurrency(subtotal);
            total += subtotal;
        });
        document.getElementById('total_amount').value = formatCurrency(total);
    }

    // Function to update product price when selected
    function updateProductPrice(select) {
        const option = select.options[select.selectedIndex];
        const price = option.dataset.price || 0;
        const priceInput = select.closest('.product-item').querySelector('.product-price');
        priceInput.value = formatCurrency(price);
        calculateTotals();
    }

    // Add event listeners to existing product selects
    document.querySelectorAll('.product-select').forEach(select => {
        select.addEventListener('change', function() {
            updateProductPrice(this);
            // Set quantity to 1 if empty when product is selected
            const quantityInput = this.closest('.product-item').querySelector('.product-quantity');
            if (quantityInput && (!quantityInput.value || quantityInput.value === '0')) {
                quantityInput.value = '1';
                calculateTotals();
            }
        });
    });

    // Add event listeners to existing quantity inputs
    document.querySelectorAll('.product-quantity').forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    // Add new product row
    addProductBtn.addEventListener('click', function() {
        const template = document.querySelector('.product-item').cloneNode(true);
        const newIndex = productCount++;

        // Update names and IDs
        template.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace('[0]', `[${newIndex}]`);
            input.value = '';
        });

        // Clear and update product select
        const select = template.querySelector('.product-select');
        select.value = '';
        select.addEventListener('change', function() {
            updateProductPrice(this);
        });

        // Clear and update quantity input
        const quantity = template.querySelector('.product-quantity');
        quantity.value = '1'; // Set default quantity to 1
        quantity.addEventListener('input', calculateTotals);

        // Show remove button
        template.querySelector('.remove-product').style.display = 'block';
        template.querySelector('.remove-product').addEventListener('click', function() {
            template.remove();
            calculateTotals();
        });

        // Clear price and subtotal
        template.querySelector('.product-price').value = '';
        template.querySelector('.product-subtotal').value = '';

        productsContainer.appendChild(template);
    });

    // Add remove functionality to first product's remove button
    document.querySelector('.remove-product').addEventListener('click', function() {
        if (document.querySelectorAll('.product-item').length > 1) {
            this.closest('.product-item').remove();
            calculateTotals();
        }
    });

    const phoneInput = document.getElementById('customer_phone');
    const phoneError = document.getElementById('phone-error');

    function validatePhoneNumber(phone) {
        // Pakistani phone number regex
        const regex = /^(03[0-9]{9}|\+923[0-9]{9})$/;
        return regex.test(phone);
    }

    function showError(message) {
        phoneError.textContent = message;
        phoneError.style.display = 'block';
        phoneInput.classList.add('is-invalid');
    }

    function hideError() {
        phoneError.textContent = '';
        phoneError.style.display = 'none';
        phoneInput.classList.remove('is-invalid');
    }

    phoneInput.addEventListener('input', function() {
        const phone = this.value.trim();
        
        if (phone === '') {
            showError('Phone number is required');
        } else if (!validatePhoneNumber(phone)) {
            showError('Please enter a valid Pakistani phone number (e.g., 03001234567 or +923001234567)');
        } else {
            hideError();
        }
    });

    // Validate on blur as well
    phoneInput.addEventListener('blur', function() {
        const phone = this.value.trim();
        
        if (phone === '') {
            showError('Phone number is required');
        } else if (!validatePhoneNumber(phone)) {
            showError('Please enter a valid Pakistani phone number (e.g., 03001234567 or +923001234567)');
        } else {
            hideError();
        }
    });

    // Name validation
    const nameInput = document.getElementById('customer_name');
    const nameError = document.getElementById('name-error');

    function validateName(name) {
        return /^[A-Za-z\s]+$/.test(name);
    }

    nameInput.addEventListener('input', function() {
        const name = this.value.trim();
        if (name === '') {
            nameError.textContent = 'Name is required';
            nameError.style.display = 'block';
            nameInput.classList.add('is-invalid');
        } else if (!validateName(name)) {
            nameError.textContent = 'Name should contain only alphabets and spaces';
            nameError.style.display = 'block';
            nameInput.classList.add('is-invalid');
        } else {
            nameError.style.display = 'none';
            nameInput.classList.remove('is-invalid');
        }
    });

    // Email validation
    const emailInput = document.getElementById('customer_email');
    const emailError = document.getElementById('email-error');

    function validateEmail(email) {
        return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);
    }

    emailInput.addEventListener('input', function() {
        const email = this.value.trim();
        if (email !== '' && !validateEmail(email)) {
            emailError.textContent = 'Please enter a valid email address';
            emailError.style.display = 'block';
            emailInput.classList.add('is-invalid');
        } else {
            emailError.style.display = 'none';
            emailInput.classList.remove('is-invalid');
        }
    });

    // Delivery date validation
    const deliveryDateInput = document.getElementById('delivery_date');
    const dateError = document.getElementById('date-error');
    
    function validateDeliveryDate(selectedDate) {
        const today = new Date();
        const minimumDate = new Date(today);
        minimumDate.setDate(minimumDate.getDate() + 5);
        
        const selected = new Date(selectedDate);
        const minimumDateString = minimumDate.toISOString().split('T')[0];
        
        return selectedDate >= minimumDateString;
    }

    function showDateError(message) {
        dateError.textContent = message;
        dateError.style.display = 'block';
        deliveryDateInput.classList.add('is-invalid');
    }

    function hideDateError() {
        dateError.textContent = '';
        dateError.style.display = 'none';
        deliveryDateInput.classList.remove('is-invalid');
    }

    deliveryDateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        
        if (selectedDate === '') {
            showDateError('Delivery date is required');
        } else if (!validateDeliveryDate(selectedDate)) {
            showDateError('Delivery date must be at least 5 days from today. Orders require advance notice.');
            this.value = ''; // Clear invalid date
        } else {
            hideDateError();
        }
    });

    // Set minimum date on page load
    const today = new Date();
    const minimumDeliveryDate = new Date(today);
    minimumDeliveryDate.setDate(minimumDeliveryDate.getDate() + 5);
    const minimumDateString = minimumDeliveryDate.toISOString().split('T')[0];
    deliveryDateInput.setAttribute('min', minimumDateString);
});
</script>
@endpush
@endsection