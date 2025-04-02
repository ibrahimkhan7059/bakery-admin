@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Products</h2>

    <div class="d-flex justify-content-between mb-3">
        <!-- 🔍 Search Form (Right Side) -->
        <form action="{{ route('products.index') }}" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Search products..."
                value="{{ request()->query('search') }}">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>

        <!-- ➕ Add Product Button -->
        <a href="{{ route('products.create') }}" class="btn btn-primary">➕ Add Product</a>
    </div>

    <!-- 🔙 Back to Categories Button -->
    <a href="{{ route('categories.index') }}" class="btn btn-secondary mb-3">⬅ Back to Categories</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- 📄 Products Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th># <a href="{{ request()->fullUrlWithQuery(['sort' => 'id']) }}">🔽</a></th>
                <th>Product Name <a href="{{ request()->fullUrlWithQuery(['sort' => 'name']) }}">🔽</a></th>
                <th>Category</th>
                <th>Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ optional($product->category)->name ?? 'No Category' }}</td> <!-- ✅ Fix applied -->
                    <td>${{ $product->price }}</td>
                    <td>
                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">✏ Edit</a>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">🗑 Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 📄 Pagination Controls -->
@if (isset($total) && $total > 0)  <!-- ✅ Check if $total exists -->
    @php
        $totalPages = max(1, ceil($total / $perPage)); // ✅ Ensure at least 1 Page
    @endphp

    <div class="d-flex justify-content-center mt-3">
        @if ($page > 1)
            <a href="{{ route('products.index', ['page' => $page - 1]) }}" class="btn btn-secondary">⬅ Prev</a>
        @endif

        <span class="mx-3">Page {{ $page }} of {{ $totalPages }}</span>

        @if ($page < $totalPages)
            <a href="{{ route('products.index', ['page' => $page + 1]) }}" class="btn btn-secondary">Next ➡</a>
        @endif
    </div>
@endif

</div>
@endsection
