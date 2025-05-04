<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>BakeHub - @yield('title', 'Admin Dashboard')</title>
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <!-- Custom CSS -->
        <style>
            :root {
                --primary-color: #FF6B6B;
                --secondary-color: #4ECDC4;
                --accent-color: #FFE66D;
                --dark-color: #2C3E50;
                --light-color: #F7F9FC;
                --success-color: #2ECC71;
                --warning-color: #F1C40F;
                --danger-color: #E74C3C;
            }

            body {
                font-family: 'Poppins', sans-serif;
                background-color: var(--light-color);
            }

            .sidebar {
                background: linear-gradient(135deg, var(--dark-color), #1a252f);
                color: white;
                min-height: 100vh;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
                position: fixed;
                width: 250px;
                z-index: 1000;
            }

            .sidebar-header {
                padding: 20px;
                text-align: center;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }

            .sidebar-header h3 {
                margin: 0;
                color: var(--primary-color);
                font-weight: 700;
            }

            .nav-link {
                color: rgba(255,255,255,0.8);
                transition: all 0.3s ease;
                border-radius: 8px;
                margin: 4px 10px;
                padding: 12px 15px;
            }

            .nav-link:hover, .nav-link.active {
                background: rgba(255,255,255,0.1);
                color: white;
                transform: translateX(5px);
            }

            .nav-link i {
                margin-right: 10px;
                font-size: 1.1rem;
            }

            .main-content {
                margin-left: 250px;
                padding: 20px;
            }

            .top-navbar {
                background: white;
                padding: 15px 20px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                margin-bottom: 20px;
                border-radius: 10px;
            }

            .user-dropdown .dropdown-toggle {
                color: var(--dark-color);
                text-decoration: none;
            }

            .user-dropdown .dropdown-toggle:hover {
                color: var(--primary-color);
            }

            .card {
                border: none;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                transition: transform 0.3s ease;
                background: white;
            }

            .card:hover {
                transform: translateY(-5px);
            }

            .card-header {
                background: transparent;
                border-bottom: 1px solid rgba(0,0,0,0.1);
                padding: 15px 20px;
            }

            .btn-primary {
                background-color: var(--primary-color);
                border-color: var(--primary-color);
                padding: 8px 20px;
                border-radius: 8px;
                font-weight: 500;
            }

            .btn-primary:hover {
                background-color: #ff5252;
                border-color: #ff5252;
            }

            .btn-success {
                background-color: var(--success-color);
                border-color: var(--success-color);
            }

            .btn-warning {
                background-color: var(--warning-color);
                border-color: var(--warning-color);
            }

            .btn-danger {
                background-color: var(--danger-color);
                border-color: var(--danger-color);
            }

            .table {
                background: white;
                border-radius: 10px;
                overflow: hidden;
            }

            .table thead th {
                background-color: var(--dark-color);
                color: white;
                border: none;
                padding: 15px;
            }

            .table tbody td {
                padding: 12px 15px;
                vertical-align: middle;
            }

            .form-control {
                border-radius: 8px;
                padding: 10px 15px;
                border: 1px solid #ddd;
            }

            .form-control:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 0.2rem rgba(255,107,107,0.25);
            }

            .badge {
                padding: 6px 12px;
                border-radius: 20px;
                font-weight: 500;
            }

            .badge-success {
                background-color: var(--success-color);
            }

            .badge-warning {
                background-color: var(--warning-color);
            }

            .badge-danger {
                background-color: var(--danger-color);
            }

            @media (max-width: 768px) {
                .sidebar {
                    width: 100%;
                    position: relative;
                    min-height: auto;
                }
                .main-content {
                    margin-left: 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="d-flex">
            <!-- Sidebar -->
            <div class="sidebar">
                <div class="sidebar-header">
                    <h3>BakeHub</h3>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                            <i class="bi bi-tags"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                            <i class="bi bi-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('bulk-orders.*') ? 'active' : '' }}" href="{{ route('bulk-orders.index') }}">
                            <i class="bi bi-cart-check"></i> Bulk Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="main-content flex-grow-1">
                <!-- Top Navbar -->
                <div class="top-navbar d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@yield('title', 'Dashboard')</h4>
                    <div class="user-dropdown">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Page Content -->
                <div class="content">
                    @yield('content')
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        @stack('scripts')
    </body>
</html>
