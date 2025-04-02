@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar - Reusing from dashboard -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-gradient-to-b from-indigo-900 to-purple-900 sidebar min-vh-100 shadow-lg">
            <div class="position-sticky pt-4">
                <div class="d-flex align-items-center justify-content-center mb-4">
                    <div class="bg-white p-2 rounded-circle me-2 reflection">
                        <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span class="text-white fw-bold fs-5">Bakery Admin</span>
                </div>
                
                <!-- Modules Section Header -->
                <div class="text-white px-3 py-2 mb-2">
                    <h6 class="text-uppercase opacity-75 mb-0 fw-bold">Modules</h6>
                </div>
                
                <ul class="nav flex-column p-3">
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="{{ route('admin.dashboard') }}">
                            <span class="me-3"><i class="bi bi-house-door-fill"></i></span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="{{ route('orders.index') }}">
                            <span class="me-3"><i class="bi bi-box-seam-fill"></i></span>
                            <span>Orders</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="{{ route('categories.index') }}">
                            <span class="me-3"><i class="bi bi-grid-fill"></i></span>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="{{ route('products.index') }}">
                            <span class="me-3"><i class="bi bi-bag-fill"></i></span>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center active bg-white bg-opacity-10 rounded-lg text-white py-2 px-3 hover-lift" href="{{ route('customers.index') }}">
                            <span class="me-3"><i class="bi bi-people-fill"></i></span>
                            <span>Customers</span>
                        </a>
                    </li>
                    <li class="nav-item mb-3">
                        <a class="nav-link d-flex align-items-center text-white py-2 px-3 transition-all hover:bg-white hover:bg-opacity-10 rounded-lg hover-lift" href="#">
                            <span class="me-3"><i class="bi bi-gear-fill"></i></span>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-gray-50">
            <!-- Header with Back Button -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2 text-gray-800 fw-bold">Customer Details</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                        </svg>
                        Back to Customers
                    </a>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5 class="mb-0 fw-bold text-gray-800">Profile Information</h5>
                                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold text-gray-700">Customer ID</div>
                                <div class="col-md-8">#{{ $customer->id }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold text-gray-700">Name</div>
                                <div class="col-md-8">{{ $customer->name }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold text-gray-700">Email</div>
                                <div class="col-md-8">{{ $customer->email }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold text-gray-700">Registered On</div>
                                <div class="col-md-8">{{ $customer->created_at->format('F d, Y') }}</div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 fw-bold text-gray-700">Last Updated</div>
                                <div class="col-md-8">{{ $customer->updated_at->format('F d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold text-gray-800">Actions</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-grid gap-2">
                                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i> Edit Customer
                                </a>
                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to delete this customer?')">
                                        <i class="bi bi-trash me-2"></i> Delete Customer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection 