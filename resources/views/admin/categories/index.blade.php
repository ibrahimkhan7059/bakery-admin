@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Categories</h2>

    <div class="d-flex justify-content-between mb-3">
        <!-- 🔍 Search Form (Right Side) -->
        <form action="{{ route('categories.index') }}" method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="🔍 Search categories..."
                value="{{ request()->query('search') }}">
            <button class="btn btn-primary" type="submit">Search</button>
        </form>

        <!-- ➕ Add Category Button -->
        <a href="{{ route('categories.create') }}" class="btn btn-primary">➕ Add Category</a>
    </div>

    <!-- 🔙 Back to Dashboard Button -->
    <a href="{{ route('dashboard') }}" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- 📄 Categories Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th># <a href="{{ request()->fullUrlWithQuery(['sort' => 'id']) }}">🔽</a></th>
                <th>Category Name <a href="{{ request()->fullUrlWithQuery(['sort' => 'name']) }}">🔽</a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>
                        <a href="{{ route('categories.products', $category->id) }}" class="btn btn-info btn-sm">View Products</a>
                        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-warning btn-sm">✏ Edit</a>
                        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">🗑 Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 📄 Pagination Controls -->
    <div class="d-flex justify-content-center">
        {{ $categories->links() }}  <!-- ✅ Laravel Built-in Pagination -->
    </div>
</div>
@endsection
