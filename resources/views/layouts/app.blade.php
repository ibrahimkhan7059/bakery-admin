<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>BakeHub - @yield('title', 'Admin Dashboard')</title>

        <!-- Bootstrap CSS -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
            rel="stylesheet"
        />
        <!-- Bootstrap Icons -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"
            rel="stylesheet"
        />
        <!-- Custom Theme -->
        <link href="{{ asset('css/custom-theme.css') }}" rel="stylesheet" />
        
        <!-- Custom Purple Badge Styles -->
        <style>
            .bg-purple {
                background-color: #6f42c1 !important;
            }
            .badge.bg-purple {
                background-color: #6f42c1 !important;
                color: white !important;
            }
            .text-purple {
                color: #6f42c1 !important;
            }
        </style>
        <!-- Font Awesome -->
        <link
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
            rel="stylesheet"
        />
    </head>
    <body>
        <div class="wrapper">
            <!-- Toggle Button -->
            <button
                id="sidebarToggle"
                class="btn position-fixed d-md-none"
                style="z-index: 1100; top: 20px; left: 20px;"
                aria-label="Toggle sidebar"
            >
                <i class="fas fa-bars"></i>
            </button>

            <!-- Overlay -->
            <div class="sidebar-overlay"></div>

            <!-- Sidebar -->
            <div class="sidebar" id="sidebar">
                <div class="sidebar-header text-center py-4">
                    <img
                        src="/images/bakehub-logo.png"
                        alt="BakeHub Logo"
                        class="bakehub-logo"
                    />
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}"
                        >
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                            href="{{ route('categories.index') }}"
                        >
                            <i class="bi bi-tags"></i> Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                            href="{{ route('products.index') }}"
                        >
                            <i class="bi bi-box"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"
                            href="{{ route('orders.index') }}"
                        >
                            <i class="bi bi-cart"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('bulk-orders.*') ? 'active' : '' }}"
                            href="{{ route('bulk-orders.index') }}"
                        >
                            <i class="bi bi-cart-check"></i> Bulk Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('custom-cake-orders.*') ? 'active' : '' }}"
                            href="{{ route('custom-cake-orders.index') }}"
                        >
                            <i class="bi bi-palette-fill"></i> Custom Cake Orders
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"
                            href="{{ route('customers.index') }}"
                        >
                            <i class="bi bi-people"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"
                            href="{{ route('settings.index') }}"
                        >
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Top Navbar -->
                <div
                    class="top-navbar d-flex justify-content-between align-items-center flex-wrap"
                >
                    <h4 class="mb-3 mb-md-4 mt-3 mt-md-0">@yield('title', 'Dashboard')</h4>
                    <div class="mb-4 user-dropdown">
                        <div class="dropdown">
                            <button 
                                class="admin-dropdown-btn" 
                                type="button" 
                                id="adminDropdown" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false"
                            >
                                <div class="d-flex align-items-center">
                                    <div class="admin-avatar">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="admin-info ms-2">
                                        <div class="admin-name">{{ Auth::user()->name }}</div>
                                        <div class="admin-role">Administrator</div>
                                    </div>
                                    <i class="bi bi-chevron-down ms-2 dropdown-arrow"></i>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end admin-dropdown-menu shadow-lg">
                                <li class="dropdown-header">
                                    <div class="d-flex align-items-center">
                                        <div class="admin-avatar-large">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div class="ms-3">
                                            <div class="fw-bold">{{ Auth::user()->name }}</div>
                                            <div class="text-muted small">{{ Auth::user()->email }}</div>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider my-2" /></li>
                                <li>
                                    <a class="dropdown-item admin-dropdown-item" href="{{ route('profile.edit') }}">
                                        <div class="d-flex align-items-center">
                                            <div class="dropdown-icon">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-medium">Profile Settings</div>
                                                <div class="text-muted small">Manage your account</div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item admin-dropdown-item" href="{{ route('settings') }}">
                                        <div class="d-flex align-items-center">
                                            <div class="dropdown-icon">
                                                <i class="bi bi-gear"></i>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-medium">System Settings</div>
                                                <div class="text-muted small">Configure application</div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item admin-dropdown-item" href="{{ route('dashboard') }}">
                                        <div class="d-flex align-items-center">
                                            <div class="dropdown-icon">
                                                <i class="bi bi-speedometer2"></i>
                                            </div>
                                            <div class="ms-3">
                                                <div class="fw-medium">Dashboard</div>
                                                <div class="text-muted small">View analytics</div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-2" /></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item admin-dropdown-item logout-item">
                                            <div class="d-flex align-items-center">
                                                <div class="dropdown-icon text-danger">
                                                    <i class="bi bi-box-arrow-right"></i>
                                                </div>
                                                <div class="ms-3">
                                                    <div class="fw-medium text-danger">Logout</div>
                                                    <div class="text-muted small">Sign out of your account</div>
                                                </div>
                                            </div>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Page Content -->
                <div class="content">@yield('content')</div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        @stack('scripts')

        <!-- Toggle Script -->
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const sidebarToggle = document.getElementById("sidebarToggle");
                const sidebar = document.getElementById("sidebar");
                const overlay = document.querySelector(".sidebar-overlay");
                const toggleIcon = sidebarToggle.querySelector("i");

                function updateToggleIcon() {
                    if (sidebar.classList.contains("active")) {
                        toggleIcon.classList.remove("fa-bars");
                        toggleIcon.classList.add("fa-times");
                        sidebarToggle.setAttribute("aria-label", "Close sidebar");
                    } else {
                        toggleIcon.classList.remove("fa-times");
                        toggleIcon.classList.add("fa-bars");
                        sidebarToggle.setAttribute("aria-label", "Toggle sidebar");
                    }
                }

                sidebarToggle.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                    overlay.classList.toggle("active");
                    
                    // Prevent body scroll when sidebar is open on mobile
                    if (sidebar.classList.contains("active")) {
                        document.body.style.overflow = "hidden";
                    } else {
                        document.body.style.overflow = "";
                    }
                    
                    updateToggleIcon();
                });

                overlay.addEventListener("click", function () {
                    sidebar.classList.remove("active");
                    overlay.classList.remove("active");
                    document.body.style.overflow = "";
                    updateToggleIcon();
                });

                // Initialize icon on page load
                updateToggleIcon();
            });
        </script>
    </body>
</html>
