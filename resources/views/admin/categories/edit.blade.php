@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <div></div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('categories.index') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Categories
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <!-- Debug Information (Remove this after fixing) -->
            @if(config('app.debug'))
                <div class="alert alert-info mb-4">
                    <h6><i class="bi bi-bug me-2"></i>Debug Information:</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Category ID:</strong> {{ $category->id }}<br>
                            <strong>Category Name:</strong> {{ $category->name }}<br>
                            <strong>Image Path:</strong> {{ $category->image ?? 'NULL' }}<br>
                            <strong>Full Image URL:</strong> {{ $category->image ? asset('storage/' . $category->image) : 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Storage Exists:</strong> {{ $category->image && Storage::disk('public')->exists($category->image) ? 'Yes' : 'No' }}<br>
                            <strong>Public Path:</strong> {{ $category->image ? public_path('storage/' . $category->image) : 'N/A' }}
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Category Image</label>
                            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            
                            @if($category->image)
                                <div class="mt-3">
                                    <label class="form-label">Current Image:</label>
                                    <div class="debug-image">
                                        <img src="{{ asset('storage/' . $category->image) }}" 
                                             alt="{{ $category->name }}" 
                                             class="img-thumbnail" 
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div class="alert alert-warning" style="display: none;">
                                            <i class="bi bi-exclamation-triangle me-2"></i>Image failed to load. Check the path: {{ $category->image }}
                                        </div>
                                        <div class="debug-info">
                                            <strong>Image Path:</strong> {{ $category->image }}<br>
                                            <strong>Full URL:</strong> {{ asset('storage/' . $category->image) }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mt-3">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>No image uploaded for this category yet.
                                    </div>
                                </div>
                            @endif
                            
                            <small class="text-muted">Recommended size: 300x300px. Max file size: 2MB</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary hover-lift">
                        <i class="bi bi-save me-1"></i> Update Category
                    </button>
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary hover-lift">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<style>
.debug-image {
    border: 2px solid #007bff;
    border-radius: 8px;
    padding: 10px;
    background-color: #f8f9fa;
}

.debug-image img {
    max-width: 200px;
    height: auto;
    display: block;
    margin: 0 auto;
}

.debug-info {
    font-family: monospace;
    font-size: 12px;
    background-color: #e9ecef;
    padding: 5px;
    border-radius: 4px;
    margin-top: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Debug image loading
    const debugImage = document.querySelector('.debug-image img');
    if (debugImage) {
        debugImage.addEventListener('load', function() {
            console.log('Image loaded successfully:', this.src);
        });
        
        debugImage.addEventListener('error', function() {
            console.error('Image failed to load:', this.src);
            this.style.display = 'none';
            const warning = this.nextElementSibling;
            if (warning) warning.style.display = 'block';
        });
    }
    
    // Log debug information
    console.log('Category Image Path:', '{{ $category->image }}');
    console.log('Full Image URL:', '{{ asset("storage/" . $category->image) }}');
    console.log('Storage Path:', '{{ public_path("storage/" . $category->image) }}');
});
</script>
