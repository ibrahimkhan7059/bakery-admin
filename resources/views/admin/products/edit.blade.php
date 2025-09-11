@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <div></div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('products.index') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-header">
            <h4 class="card-title">Edit Product</h4>
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

            <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $product->name) }}" 
                                   required
                                   minlength="3"
                                   maxlength="255"
                                   autocomplete="off">
                            <div class="form-text">Enter product name (letters and spaces only)</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category_id') is-invalid @enderror" 
                                    id="category_id" 
                                    name="category_id" 
                                    required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rs</span>
                                <input type="number"  
                                       min="1"
                                       max="999999.99"
                                       class="form-control @error('price') is-invalid @enderror" 
                                       id="price" 
                                       name="price" 
                                       value="{{ old('price', $product->price) }}" 
                                       required>
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter price between Rs 1 and Rs 999,999.99</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                            <input type="number" 
                                   class="form-control @error('stock') is-invalid @enderror" 
                                   id="stock" 
                                   name="stock" 
                                   value="{{ old('stock', $product->stock) }}" 
                                   required
                                   min="0"
                                   max="1000">
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter stock quantity (0-1000 units)</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" 
                              name="description" 
                              rows="4" 
                              required
                              minlength="10"
                              maxlength="1000">{{ old('description', $product->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Enter a detailed description (10-1000 characters)</div>
                </div>

                <div class="mb-3">
                    <label for="allergens" class="form-label">Allergens</label>
                    <textarea class="form-control @error('allergens') is-invalid @enderror" 
                              id="allergens" 
                              name="allergens" 
                              rows="2"
                              maxlength="500">{{ old('allergens', $product->allergens) }}</textarea>
                    @error('allergens')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">List any allergens (e.g. nuts, dairy, gluten). Separate by commas.</div>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Product Image</label>
                    <input type="file" 
                           class="form-control @error('image') is-invalid @enderror" 
                           id="image" 
                           name="image" 
                           accept="image/jpeg,image/png,image/jpg,image/gif">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Upload an image (max 2MB, JPG, PNG, or GIF)</div>
                    @if($product->image)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $product->image) }}" 
                                 alt="Current Image" 
                                 class="img-thumbnail" 
                                 style="max-height: 100px;">
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="alternative_product_id" class="form-label">Alternative Product</label>
                    <select class="form-select @error('alternative_product_id') is-invalid @enderror" id="alternative_product_id" name="alternative_product_id">
                        <option value="">None</option>
                        @foreach($allProducts as $altProduct)
                            @if($altProduct->id !== $product->id)
                                <option value="{{ $altProduct->id }}" {{ old('alternative_product_id', $product->alternative_product_id) == $altProduct->id ? 'selected' : '' }}>
                                    {{ $altProduct->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @error('alternative_product_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Select an alternative product to suggest if this one is unavailable.</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Product</button>
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
// Client-side validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        if (this.files[0].size > 2 * 1024 * 1024) {
            alert('Image size should not exceed 2MB');
            this.value = '';
            return;
        }
    }
});

// Real-time product name validation
document.getElementById('name').addEventListener('input', function(e) {
    const input = e.target.value;
    const errorDiv = this.nextElementSibling; // Get the error div
    
    // Check if input contains numbers only
    if(/[0-9]/.test(input)) {
        this.classList.add('is-invalid');
        if(!errorDiv.classList.contains('invalid-feedback')) {
            const newErrorDiv = document.createElement('div');
            newErrorDiv.className = 'invalid-feedback';
            newErrorDiv.textContent = 'Product name cannot contain numbers. Letters, spaces, and special characters are allowed.';
            this.parentNode.insertBefore(newErrorDiv, this.nextSibling);
        }
    } else {
        this.classList.remove('is-invalid');
        if(errorDiv && errorDiv.classList.contains('invalid-feedback')) {
            errorDiv.remove();
        }
    }
});
</script>
@endpush
@endsection