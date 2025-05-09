@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">Customers</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('customers.create') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-plus-lg me-2"></i>Add New Customer
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->address ?? 'Not provided' }}</td>
                                <td>{{ $customer->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-info hover-lift" title="View">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-primary hover-lift" title="Edit">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger hover-lift" title="Delete" onclick="return confirm('Are you sure you want to delete this customer?')">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-people display-4"></i>
                                        <p class="mt-2">No customers found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection 