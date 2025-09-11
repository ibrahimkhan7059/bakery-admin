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
                class="btn position-fixed"
                style="z-index: 1100; top: 70px; left: 20px;"
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
                    class="top-navbar d-flex justify-content-between align-items-center"
                >
                    <h4 class="mb-4">@yield('title', 'Dashboard')</h4>
                    <div class="mb-4 user-dropdown">
                        <a
                            class="dropdown-toggle"
                            href="#"
                            role="button"
                            data-bs-toggle="dropdown"
                        >
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="{{ route('profile.edit') }}"
                                    ><i class="bi bi-person"></i> Profile</a
                                >
                            </li>
                            <li>
                                <a
                                    class="dropdown-item"
                                    href="{{ route('settings') }}"
                                    ><i class="bi bi-gear"></i> Settings</a
                                >
                            </li>
                            <li><hr class="dropdown-divider" /></li>
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
                        sidebarToggle.querySelector(".btn-text").textContent = "Close";
                    } else {
                        toggleIcon.classList.remove("fa-times");
                        toggleIcon.classList.add("fa-bars");
                        sidebarToggle.setAttribute("aria-label", "Open sidebar");
                        sidebarToggle.querySelector(".btn-text").textContent = "Open";
                    }
                }

                sidebarToggle.addEventListener("click", function () {
                    sidebar.classList.toggle("active");
                    overlay.classList.toggle("active");
                    updateToggleIcon();
                });

                overlay.addEventListener("click", function () {
                    sidebar.classList.remove("active");
                    overlay.classList.remove("active");
                    updateToggleIcon();
                });

                // Initialize icon on page load
                updateToggleIcon();
            });
        </script>
    </body>
</html>
