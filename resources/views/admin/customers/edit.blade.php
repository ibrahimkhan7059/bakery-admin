@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">Edit Customer</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Customers
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <form action="{{ route('customers.update', $customer->id) }}" method="POST" id="editCustomerForm">
                @csrf
                @method('PUT')
                
                <div class="row g-4">
                    <!-- Name -->
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $customer->name) }}" 
                               required minlength="3" maxlength="50"
                               pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed">
                        <div class="form-text">Enter full name (3-50 characters, letters only)</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $customer->email) }}" 
                               required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                               title="Please enter a valid email address">
                        <div class="form-text">Enter a valid email address</div>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone', $customer->phone) }}"
                               required pattern="^(\+92|0)[0-9]{10}$"
                               title="Please enter a valid Pakistani phone number (e.g., +923001234567 or 03001234567)">
                        <div class="form-text">Format: +923001234567 or 03001234567</div>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="col-md-6">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" minlength="8">
                        <div class="form-text">Leave blank to keep current password (min 8 characters if changing)</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm New Password -->
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" minlength="8">
                        <div class="form-text">Re-enter new password if changing</div>
                    </div>

                    <!-- Address -->
                    <div class="col-12">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" 
                                  required minlength="10" maxlength="500">{{ old('address', $customer->address) }}</textarea>
                        <div class="form-text">Enter complete address (10-500 characters)</div>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary hover-lift">
                        <i class="bi bi-check-lg me-1"></i> Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('editCustomerForm').addEventListener('submit', function(e) {
    const phone = document.getElementById('phone').value;
    const phonePattern = /^(\+92|0)[0-9]{10}$/;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    
    if (!phonePattern.test(phone)) {
        e.preventDefault();
        alert('Please enter a valid Pakistani phone number (e.g., +923001234567 or 03001234567)');
    }

    if (password && password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
    }
});
</script>
@endpush
@endsection 