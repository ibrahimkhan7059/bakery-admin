@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <div></div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('products.create') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-plus-lg me-1"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('products.index') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        <option value="5" {{ request('per_page', 5) == '5' ? 'selected' : '' }}>5 per page</option>
                        <option value="10" {{ request('per_page', 5) == '10' ? 'selected' : '' }}>10 per page</option>
                        <option value="15" {{ request('per_page', 5) == '15' ? 'selected' : '' }}>15 per page</option>
                        <option value="25" {{ request('per_page', 5) == '25' ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page', 5) == '50' ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page', 5) == '100' ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0">Image</th>
                            <th class="border-0">Name</th>
                            <th class="border-0">Category</th>
                            <th class="border-0">Price</th>
                            <th class="border-0">Stock</th>
                            <th class="border-0">Allergens</th>
                            <th class="border-0 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td>PKR {{ number_format($product->price, 2) }}</td>
                            <td>{{ $product->stock }}</td>
                            <td>{{ $product->allergens ?? '-' }}</td>
                            <td class="text-center">
                                <div class="btn-group" role="group" aria-label="Product actions">
                                    <a href="{{ route('products.show', $product->id) }}" 
                                       class="btn btn-sm btn-outline-info hover-lift" 
                                       title="View Product"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product->id) }}" 
                                       class="btn btn-sm btn-outline-primary hover-lift" 
                                       title="Edit Product"
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger hover-lift" 
                                            title="Delete Product"
                                            data-bs-toggle="tooltip"
                                            onclick="deleteProduct({{ $product->id }}, '{{ $product->name }}')">
                                        <i class="bi bi-trash3"></i>
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
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">No products found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($products->hasPages())
        <div class="d-flex justify-content-center mt-4">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm justify-content-center compact-pagination">
                    {{-- Previous Page Link --}}
                    @if ($products->onFirstPage())
                        <li class="page-item disabled">
                            <span class="page-link compact-page-link">
                                <i class="bi bi-chevron-left"></i>
                            </span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link compact-page-link" href="{{ $products->previousPageUrl() }}" rel="prev">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @if($products->lastPage() > 1)
                        @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                            @if ($page == $products->currentPage())
                                <li class="page-item active">
                                    <span class="page-link compact-page-link">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link compact-page-link" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif

                    {{-- Next Page Link --}}
                    @if ($products->hasMorePages())
                        <li class="page-item">
                            <a class="page-link compact-page-link" href="{{ $products->nextPageUrl() }}" rel="next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled">
                            <span class="page-link compact-page-link">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
    @endif
</div>
@endsection

<style>
/* Compact Pagination Styles */
.compact-pagination .page-link {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.875rem !important;
    min-width: 32px !important;
    height: 32px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border-radius: 6px !important;
    margin: 0 2px !important;
}

.compact-pagination .page-item {
    margin: 0 !important;
}

.compact-pagination .page-link i {
    font-size: 0.75rem !important;
}

.compact-pagination .page-item.active .page-link {
    background-color: #FF6F61 !important;
    border-color: #FF6F61 !important;
    color: white !important;
    font-weight: 600 !important;
}

.compact-pagination .page-link:hover {
    background-color: rgba(255, 111, 97, 0.1) !important;
    border-color: #FF6F61 !important;
    color: #FF6F61 !important;
}

/* Action buttons styling */
.btn-group .btn {
    border-radius: 6px !important;
    margin: 0 2px;
}

.btn-group .btn i {
    font-size: 0.875rem;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-success:hover {
    background-color: #198754;
    border-color: #198754;
}

/* Tooltip positioning fix */
.tooltip {
    font-size: 12px !important;
}

.tooltip-inner {
    max-width: 200px;
    padding: 6px 10px;
    background-color: #333 !important;
    border-radius: 4px;
}

.bs-tooltip-bottom .tooltip-arrow::before {
    border-bottom-color: #333 !important;
}
</style>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            placement: 'bottom',
            delay: { show: 300, hide: 100 }
        });
    });
});

// Sweet delete confirmation
function deleteProduct(productId, productName) {
    if (confirm(`Are you sure you want to delete product "${productName}"?\n\nThis action cannot be undone.`)) {
        document.getElementById('delete-form-' + productId).submit();
    }
}

// Auto-refresh every 10 seconds
setInterval(function() {
    location.reload();
}, 10000);
</script>
