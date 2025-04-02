@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Add Category</h2>
    <!--  Success Message -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Category Name:</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Save Category</button>
    </form>
</div>
@endsection
