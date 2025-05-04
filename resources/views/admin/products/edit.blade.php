@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">Edit Product</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg glass-card">
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

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="productName" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $product->name) }}" required
                                pattern="[A-Za-z\s\(\)\[\]]+"
                                title="Product name can only contain letters, spaces, and brackets"
                                oninput="this.value = this.value.replace(/[^A-Za-z\s\(\)\[\]]/g, '')">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
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

                <div class="mb-4">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                        rows="3" required minlength="10" maxlength="1000">{{ old('description', $product->description) }}</textarea>
                    <small class="text-muted">Minimum 10 characters, maximum 1000 characters</small>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Price (₨) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₨</span>
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" 
                                    value="{{ old('price', $product->price) }}" required min="0" max="999999.99" step="0.01">
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Stock <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" 
                                value="{{ old('stock', $product->stock) }}" required min="0" max="1000">
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Product Image</label>
                    @if($product->image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" 
                        accept="image/jpeg,image/png,image/jpg,image/gif"
                        onchange="validateImage(this)">
                    <small class="text-muted">Maximum file size: 2MB. Supported formats: JPEG, PNG, JPG, GIF. Minimum dimensions: 100x100 pixels</small>
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary hover-lift">
                        <i class="bi bi-save me-1"></i> Update Product
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary hover-lift">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Delete Confirmation Modal -->
<div class="modal fade" id="deleteImageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this image?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary hover-lift" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger hover-lift" id="confirmDeleteImage">Delete</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function validateImage(input) {
    const file = input.files[0];
    if (file) {
        // Check file size (2MB = 2 * 1024 * 1024 bytes)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image size cannot exceed 2MB');
            input.value = '';
            return;
        }

        // Check file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPEG, PNG, JPG, or GIF)');
            input.value = '';
            return;
        }

        // Check image dimensions
        const img = new Image();
        img.onload = function() {
            if (this.width < 100 || this.height < 100) {
                alert('Image dimensions must be at least 100x100 pixels');
                input.value = '';
            }
        };
        img.src = URL.createObjectURL(file);
    }
}

document.getElementById('productForm').addEventListener('submit', function(e) {
    const name = document.getElementById('productName').value;
    const description = document.querySelector('textarea[name="description"]').value;
    
    if (!/^[A-Za-z\s\(\)\[\]]+$/.test(name)) {
        e.preventDefault();
        alert('Product name can only contain letters, spaces, and brackets');
        return false;
    }
    
    if (description.length < 10) {
        e.preventDefault();
        alert('Description must be at least 10 characters long');
        return false;
    }
    
    if (description.length > 1000) {
        e.preventDefault();
        alert('Description cannot exceed 1000 characters');
        return false;
    }
});
</script>
@endpush
@endsection