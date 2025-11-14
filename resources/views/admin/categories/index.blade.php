@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <Div></Div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('categories.create') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-plus-lg me-1"></i> Add New Category
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
        <div class="card-body">
            <form action="{{ route('categories.index') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search categories..." value="{{ request('search') }}">
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
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Products</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    @if($category->image)
                                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                                    @endif
                                </td>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->products_count }}</td>
                                <td class="text-center">
                                    <div class="btn-group" role="group" aria-label="Category actions">
                                        <a href="{{ route('categories.products', $category) }}" 
                                           class="btn btn-sm btn-outline-info hover-lift" 
                                           title="View Products"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-box-seam"></i>
                                        </a>
                                        <a href="{{ route('categories.edit', $category) }}" 
                                           class="btn btn-sm btn-outline-primary hover-lift" 
                                           title="Edit Category"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger hover-lift" 
                                                title="Delete Category"
                                                data-bs-toggle="tooltip"
                                                onclick="deleteCategory('{{ $category->id }}', '{{ $category->name }}')">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                        <form id="delete-form-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No categories found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
           

            @if($categories->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-center compact-pagination">
                            {{-- Previous Page Link --}}
                            @if ($categories->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link compact-page-link">
                                        <i class="bi bi-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link compact-page-link" href="{{ $categories->previousPageUrl() }}" rel="prev">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @if($categories->lastPage() > 1)
                                @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                                    @if ($page == $categories->currentPage())
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
                            @if ($categories->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link compact-page-link" href="{{ $categories->nextPageUrl() }}" rel="next">
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
    </div>
</div>

<!-- Custom Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: linear-gradient(45deg, #ff6b6b, #ff8e8e); border-radius: 50%;">
                        <i class="bi bi-exclamation-triangle text-white" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <h4 class="mb-3 fw-bold text-gray-800">Delete Category?</h4>
                <p class="text-muted mb-4">
                    Are you sure you want to delete "<strong id="categoryName"></strong>"?<br>
                    <small class="text-danger">This action cannot be undone.</small>
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
let deleteFormId = null;

function confirmDelete(categoryId, categoryName) {
    deleteFormId = 'delete-form-' + categoryId;
    document.getElementById('categoryName').textContent = categoryName;
    
    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (deleteFormId) {
            document.getElementById(deleteFormId).submit();
        }
    });
});
</script>

<style>
.modal-content {
    border-radius: 15px !important;
}

.modal-body {
    padding: 2rem !important;
}

.btn {
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-danger {
    background: linear-gradient(45deg, #ff6b6b, #ff5252);
    border: none;
}

.btn-danger:hover {
    background: linear-gradient(45deg, #ff5252, #ff4444);
}

.btn-outline-secondary:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    color: #6c757d;
}

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

.btn-outline-info:hover {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
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

// Sweet delete confirmation for categories
function deleteCategory(categoryId, categoryName) {
    if (confirm(`Are you sure you want to delete category "${categoryName}"?\n\nThis action cannot be undone and will affect all products in this category.`)) {
        document.getElementById('delete-form-' + categoryId).submit();
    }
}

// Auto-refresh every 10 seconds
setInterval(function() {
    location.reload();
}, 10000);
</script>
