@extends('layouts.app')

@section('title', 'Settings')

@section('styles')
<style>
    .form-control:focus {
        border-color: #FF6F61;
        box-shadow: 0 0 0 0.2rem rgba(255, 111, 97, 0.25);
    }
    
    .form-select:focus {
        border-color: #FF6F61;
        box-shadow: 0 0 0 0.2rem rgba(255, 111, 97, 0.25);
    }
    
    .btn-save:hover {
        background: linear-gradient(135deg, #FF5A4A 0%, #FF7969 100%) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(255, 111, 97, 0.3);
    }
    
    .card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .card-header {
        border-radius: 12px 12px 0 0 !important;
        border: none;
    }
    
    .form-label {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .form-text {
        color: var(--text-secondary);
        font-size: 0.875rem;
    }
    
    .alert {
        border-radius: 8px;
        border: none;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 12px 16px;
        font-size: 14px;
        background-color: #fff;
        transition: all 0.3s ease;
    }
    
    .form-control:hover, .form-select:hover {
        border-color: #FF6F61;
    }
    
    .input-group {
        margin-bottom: 1rem;
    }
    
    .btn-outline-secondary {
        border-color: #e0e0e0;
        color: var(--text-secondary);
    }
    
    .btn-outline-secondary:hover {
        background-color: #f8f9fa;
        border-color: #FF6F61;
        color: #FF6F61;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-left: 4px solid #28a745;">
                    <i class="fas fa-check-circle me-2" style="color: #28a745;"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-left: 4px solid #dc3545;">
                    <i class="fas fa-exclamation-circle me-2" style="color: #dc3545;"></i>
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #FF6F61 0%, #FF8A7A 100%);">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Bakery Settings
                    </h5>
                    <p class="mb-0 mt-1 opacity-75">Manage your bakery's basic information and order settings</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="store_name" class="form-label">Bakery Name</label>
                            <input type="text" class="form-control" id="store_name" name="store_name" 
                                   value="{{ old('store_name', 'BakeHub') }}" required>
                            <div class="form-text">The name of your bakery business</div>
                        </div>

                        <div class="mb-3">
                            <label for="store_phone" class="form-label">Contact Phone</label>
                            <input type="tel" class="form-control" id="store_phone" name="store_phone" 
                                   value="{{ old('store_phone') }}" placeholder="+92 300 1234567" required>
                            <div class="form-text">Primary contact number for customers</div>
                        </div>

                        <div class="mb-3">
                            <label for="store_address" class="form-label">Delivery Area</label>
                            <textarea class="form-control" id="store_address" name="store_address" 
                                      rows="2" placeholder="Areas where you deliver orders" required>{{ old('store_address') }}</textarea>
                            <div class="form-text">Specify the areas or locations where you deliver orders</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Order Management</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="min_order_amount" class="form-label">Minimum Order Amount (Rs.)</label>
                                    <input type="number" class="form-control" id="min_order_amount" name="min_order_amount" 
                                           value="{{ old('min_order_amount', '500') }}" min="0" step="50">
                                    <div class="form-text">Minimum amount required for orders</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="advance_payment_percentage" class="form-label">Advance Payment (%)</label>
                                    <input type="number" class="form-control" id="advance_payment_percentage" 
                                           name="advance_payment_percentage" value="{{ old('advance_payment_percentage', '50') }}" 
                                           min="0" max="100" step="5">
                                    <div class="form-text">Required advance payment percentage</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-save text-white" style="background: linear-gradient(135deg, #FF6F61 0%, #FF8A7A 100%); border: none; transition: all 0.3s ease;">
                                <i class="fas fa-save me-2"></i>Save Settings
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-2"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 