@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar min-vh-100 shadow-lg">
            <div class="position-sticky pt-4">
                <div class="d-flex align-items-center justify-content-center mb-4">
                    <div class="bg-white p-2 rounded-circle me-2 reflection">
                        <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span class="text-white fw-bold fs-5">Bakery Admin</span>
                </div>
                
                <!-- Modules Section Header -->
                <div class="text-white px-3 py-2 mb-2">
                    <h6 class="text-uppercase opacity-75 mb-0 fw-bold">Modules</h6>
                </div>
                
                <ul class="nav flex-column p-3">
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="{{ route('admin.dashboard') }}">
                            <span class="me-3"><i class="bi bi-house-door-fill"></i></span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center active bg-white bg-opacity-10 rounded-lg text-white py-2 px-3 hover-lift" href="{{ route('orders.index') }}">
                            <span class="me-3"><i class="bi bi-box-seam-fill"></i></span>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="{{ route('categories.index') }}">
                            <span class="me-3"><i class="bi bi-grid-fill"></i></span>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="{{ route('products.index') }}">
                            <span class="me-3"><i class="bi bi-bag-fill"></i></span>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="{{ route('customers.index') }}">
                            <span class="me-3"><i class="bi bi-people-fill"></i></span>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="#">
                            <span class="me-3"><i class="bi bi-gear-fill"></i></span>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-gray-50">
            <!-- Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2 text-gray-800 fw-bold">Create New Order</h1>
            </div>

            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-body">
                    <form action="{{ route('orders.store') }}" method="POST" id="orderForm">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Customer Name</label>
                                <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Customer Phone</label>
                                <input type="tel" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" required>
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Delivery Address</label>
                            <textarea name="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" rows="3" required>{{ old('delivery_address') }}</textarea>
                            @error('delivery_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                                    <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="">Select Priority</option>
                                    <option value="1" {{ old('priority') == '1' ? 'selected' : '' }}>High</option>
                                    <option value="2" {{ old('priority') == '2' ? 'selected' : '' }}>Medium</option>
                                    <option value="3" {{ old('priority') == '3' ? 'selected' : '' }}>Low</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Order Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Order Items -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Order Items</h5>
                                <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                                    <i class="bi bi-plus-lg me-2"></i>Add Item
                                </button>
                            </div>

                            <div id="orderItems">
                                <!-- Items will be added here dynamically -->
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Create Order
                            </button>
                            <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    let itemCount = 0;

    function addItem() {
        const itemsDiv = document.getElementById('orderItems');
        const itemDiv = document.createElement('div');
        itemDiv.className = 'card mb-3';
        itemDiv.innerHTML = `
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <label class="form-label">Product</label>
                        <select name="items[${itemCount}][product_id]" class="form-select" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} - ${{ $product->price }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="items[${itemCount}][quantity]" class="form-control" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Notes</label>
                        <input type="text" name="items[${itemCount}][notes]" class="form-control">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger" onclick="removeItem(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        itemsDiv.appendChild(itemDiv);
        itemCount++;
    }

    function removeItem(button) {
        button.closest('.card').remove();
    }

    // Add first item on page load
    document.addEventListener('DOMContentLoaded', addItem);
</script>
@endpush
@endsection
