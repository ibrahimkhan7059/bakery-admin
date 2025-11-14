@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Income Report - {{ $label }}</h1>
            <p class="text-muted">{{ $start->format($dateFormat) }} @if($period != 'today') - {{ $end->format($dateFormat) }}@endif</p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Income ({{ $label }})
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rs. {{ number_format($totalIncome, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Orders
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalOrders }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Average Order Value
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Rs. {{ $totalOrders > 0 ? number_format($totalIncome / $totalOrders, 2) : '0.00' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Growth Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="{{ $growth >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $growth >= 0 ? '+' : '' }}{{ number_format($growth, 1) }}%
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Filter Buttons -->
    <div class="card shadow mb-4 no-print">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Filter by Period</h6>
            <!-- Print Button -->
            <button onclick="window.print()" class="btn btn-success btn-sm">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="{{ route('admin.report.income', ['period' => 'today']) }}" 
                   class="btn {{ $period == 'today' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Today
                </a>
                <a href="{{ route('admin.report.income', ['period' => 'weekly']) }}" 
                   class="btn {{ $period == 'weekly' ? 'btn-primary' : 'btn-outline-primary' }}">
                    This Week
                </a>
                <a href="{{ route('admin.report.income', ['period' => 'monthly']) }}" 
                   class="btn {{ $period == 'monthly' ? 'btn-primary' : 'btn-outline-primary' }}">
                    This Month
                </a>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Orders Detail - {{ $label }}</h6>
        </div>
        <div class="card-body">
            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>
                                        @if($order->user)
                                            {{ $order->user->name }}
                                            <br>
                                            <small class="text-muted">{{ $order->user->email }}</small>
                                        @else
                                            <span class="text-muted">Guest Order</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="font-weight-bold text-success">
                                            Rs. {{ number_format($order->total_amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <th colspan="3" class="text-right">Total Income:</th>
                                <th class="text-success">Rs. {{ number_format($totalIncome, 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No completed orders found for {{ $label }}</h5>
                    <p class="text-muted">There are no completed orders in the selected time period.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide all UI elements */
    .btn, .card-header, nav, .navbar, .sidebar, .breadcrumb, 
    .filter-buttons, .btn-group, .no-print {
        display: none !important;
    }
    
    /* Hide navigation and sidebar */
    .navbar-nav, .sidebar-wrapper, .main-sidebar, .navbar-expand,
    .main-header, .navbar-brand, .dropdown, .dropdown-menu,
    .navbar-toggler, .navbar-collapse, .nav-link, .dropdown-toggle {
        display: none !important;
    }
    
    /* Hide admin dashboard elements */
    .content-wrapper, .main-sidebar, .navbar-static-top,
    header, .main-header, .navbar-header, .sidebar-toggle {
        display: none !important;
    }
    
    /* Reset body and container */
    body {
        background: white !important;
        color: black !important;
        font-family: Arial, sans-serif !important;
        font-size: 12px !important;
        margin: 0 !important;
        padding: 15px !important;
    }
    
    /* Hide entire layout wrapper */
    .wrapper, .content-wrapper, .main-content, 
    .layout-navbar, .layout-menu, .layout-page,
    .app-wrapper, .main-wrapper {
        all: unset !important;
        display: block !important;
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
    }
    
    /* Card styling for print */
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
        page-break-inside: avoid;
    }
    
    .card-body {
        padding: 15px !important;
    }
    
    /* Table styling for print */
    .table {
        font-size: 11px !important;
        border-collapse: collapse !important;
    }
    
    .table th, .table td {
        border: 1px solid #ddd !important;
        padding: 8px !important;
        text-align: left !important;
    }
    
    .table th {
        background-color: #f8f9fa !important;
        font-weight: bold !important;
    }
    
    /* Summary cards styling */
    .row {
        margin: 0 !important;
    }
    
    .col-xl-3, .col-md-6 {
        width: 24% !important;
        float: left !important;
        margin-right: 1% !important;
        margin-bottom: 10px !important;
    }
    
    /* Print header */
    .container-fluid::before {
        content: "BAKERY INCOME REPORT";
        display: block !important;
        text-align: center;
        font-size: 18px !important;
        font-weight: bold !important;
        margin-bottom: 20px !important;
        padding: 10px 0 !important;
        border-bottom: 2px solid #000 !important;
    }
    
    /* Print footer */
    .container-fluid::after {
        content: "Generated on: " attr(data-date) " | Bakery Management System";
        display: block !important;
        text-align: center;
        font-size: 10px !important;
        margin-top: 20px !important;
        padding-top: 10px !important;
        border-top: 1px solid #ddd !important;
        color: #666 !important;
    }
    
    /* Remove background colors for print */
    .bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    
    .text-success, .text-primary, .text-info, .text-warning, .text-danger {
        color: #000 !important;
    }
    
    /* Badge styling for print */
    .badge {
        border: 1px solid #000 !important;
        background-color: white !important;
        color: black !important;
        padding: 2px 5px !important;
    }
    
    /* Icons - hide or replace with text */
    .fas, .fa, .bi {
        display: none !important;
    }
    
    /* Page break control */
    .page-break {
        page-break-before: always;
    }
    
    /* Ensure proper margins for printing */
    @page {
        margin: 0.5in;
        size: A4;
    }
    
    /* Laravel specific layout elements to hide */
    [class*="layout-"], [class*="navbar-"], [class*="sidebar-"],
    [id*="navbar"], [id*="sidebar"], [class*="admin-"],
    .topbar, .topnav, .header, .footer, .navigation,
    .menu, .user-menu, .admin-menu, .brand, .logo,
    .top-navbar, h4 {
        display: none !important;
    }
    
    /* Bootstrap navigation elements */
    .nav, .nav-tabs, .nav-pills, .navbar-text,
    .collapse, .collapsing, .navbar-collapse {
        display: none !important;
    }
    
    /* Force clean print layout */
    html, body {
        overflow: visible !important;
        height: auto !important;
        width: auto !important;
    }
    
    /* Hide any remaining admin interface */
    *:not(.container-fluid):not(.card):not(.card-body):not(.table):not(.row):not(.col-*):not(h1):not(h2):not(h3):not(h4):not(h5):not(h6):not(p):not(span):not(div.text-*):not(td):not(th):not(tr):not(thead):not(tbody) {
        background: transparent !important;
        box-shadow: none !important;
    }
}

/* Add print-specific class to hide elements */
.no-print {
    /* This class will be hidden in print mode */
}

/* Print button styling for screen */
@media screen {
    .print-button {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }
}
</style>

<script>
// Add current date for print footer
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.setAttribute('data-date', new Date().toLocaleDateString());
    }
});
</script>
@endsection