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
                            <th>Actions</th>
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
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('categories.products', $category) }}" class="btn btn-sm btn-outline-primary hover-lift">
                                            <i class="bi bi-box-seam"></i>
                                        </a>
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary hover-lift">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger hover-lift" onclick="return confirm('Are you sure you want to delete this category?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
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
                        <ul class="pagination pagination-sm">
                            {{-- Previous Page Link --}}
                            @if ($categories->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="bi bi-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $categories->previousPageUrl() }}" rel="prev">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            @endif

                            {{-- Pagination Elements --}}
                            @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                                @if ($page == $categories->currentPage())
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($categories->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $categories->nextPageUrl() }}" rel="next">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
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
@endsection
