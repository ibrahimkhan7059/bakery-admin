@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Product</h2>

    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="mb-3">
            <label>Product Name:</label>
            <input type="text" name="name" value="{{ $product->name }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Category:</label>
            <select name="category_id" class="form-control">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ $category->id == $product->category_id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Price:</label>
            <input type="number" name="price" value="{{ $product->price }}" class="form-control" required>
        </div>

        <!-- Current Image Preview with Delete Button -->
        <div class="mb-3">
            <label>Current Image:</label>
            <br>
            @if($product->image)
                <img id="productImage" src="{{ asset('storage/' . $product->image) }}" alt="Product Image" width="150">
                <br><br>
                <button type="button" class="btn btn-danger btn-sm" id="deleteImageBtn">🗑 Delete Image</button>
            @else
                <p>No Image Available</p>
            @endif
        </div>

        <!-- Upload New Image -->
        <div class="mb-3">
            <label>Upload New Image:</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
</div>
<!-- ✅ JavaScript for AJAX Image Deletion -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('deleteImageBtn')?.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this image?')) {
            fetch('{{ route("products.deleteImage", $product->id) }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    alert('Image deleted successfully!');
                    document.getElementById('productImage').remove(); // Remove image preview
                    document.getElementById('deleteImageBtn').remove(); // Remove delete button
                } else {
                    alert('Error deleting image.');
                }
            }).catch(error => console.error('Error:', error));
        }
    });
});
</script>
@endsection