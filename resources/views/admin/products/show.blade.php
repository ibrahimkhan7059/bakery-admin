@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">Product Details</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary me-2 hover-lift">
                <i class="bi bi-pencil-square me-1"></i> Edit Product
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Products
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Product Image -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title text-gray-800 fw-bold mb-4">Product Image</h5>
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="img-fluid rounded shadow-sm mb-3" 
                             style="max-height: 300px; object-fit: cover;">
                        
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                            <p class="text-muted mt-3">No image available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Information -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-gray-800 fw-bold mb-4">Product Information</h5>
                    <div class="mb-3">
                        <label class="text-muted small">Product ID</label>
                        <p class="mb-0">{{ $product->id }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Product Name</label>
                        <p class="mb-0 fw-bold">{{ $product->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Category</label>
                        <p class="mb-0">
                            <span class="badge bg-primary">{{ $product->category->name }}</span>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Price</label>
                        <p class="mb-0 fw-bold text-success">PKR {{ number_format($product->price, 2) }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Stock</label>
                        <p class="mb-0">
                            @if($product->stock > 10)
                                <span class="badge bg-success">{{ $product->stock }} units</span>
                            @elseif($product->stock > 0)
                                <span class="badge bg-warning">{{ $product->stock }} units (Low Stock)</span>
                            @else
                                <span class="badge bg-danger">Out of Stock</span>
                            @endif
                        </p>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Additional Details -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-lg glass-card h-100">
                <div class="card-body">
                    <h5 class="card-title text-gray-800 fw-bold mb-4">Additional Details</h5>
                    <div class="mb-3">
                        <label class="text-muted small">Allergens</label>
                        <p class="mb-0">{{ $product->allergens ?? 'None specified' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Alternative Product</label>
                        <p class="mb-0">
                            @if($product->alternative_product_id)
                                <a href="{{ route('products.show', $product->alternative_product_id) }}" class="text-decoration-none">
                                    {{ $product->alternativeProduct->name ?? 'N/A' }}
                                </a>
                            @else
                                <span class="text-muted">No alternative</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Created Date</label>
                        <p class="mb-0">{{ $product->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0">{{ $product->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description -->
    @if($product->description)
    <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
        <div class="card-body">
            <h5 class="card-title text-gray-800 fw-bold mb-4">Product Description</h5>
            <p class="mb-0">{{ $product->description }}</p>
        </div>
    </div>
    @endif

    <!-- Actions Card -->
    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <h5 class="card-title text-gray-800 fw-bold mb-4">Actions</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary hover-lift">
                    <i class="bi bi-pencil-square me-1"></i> Edit Product
                </a>
                @if($product->image)
                <form action="{{ route('products.deleteImage', $product) }}" method="POST" class="d-inline" 
                      onsubmit="return confirm('Are you sure you want to delete this product image?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning hover-lift">
                        <i class="bi bi-trash3 me-1"></i> Delete Image
                    </button>
                </form>
                @endif
                <button type="button" class="btn btn-danger hover-lift" 
                        onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')">
                    <i class="bi bi-trash3 me-1"></i> Delete Product
                </button>
            </div>
            
            <!-- Hidden form for deletion -->
            <form id="delete-form-{{ $product->id }}" 
                  action="{{ route('products.destroy', $product->id) }}" 
                  method="POST" 
                  style="display: none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<script>
function deleteProduct(productId, productName) {
    if (confirm(`Are you sure you want to delete product "${productName}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('delete-form-' + productId).submit();
    }
}
</script>
@endsection
