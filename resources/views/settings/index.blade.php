@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <h1 class="h2 text-gray-800 fw-bold">
            <i class="bi bi-gear me-2"></i>Settings
        </h1>
    </div>

    <div class="row">
        <!-- General Settings -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="bi bi-sliders me-2"></i>General Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Language</label>
                            <select class="form-select" name="language">
                                <option value="en">English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Time Zone</label>
                            <select class="form-select" name="timezone">
                                <option value="UTC">UTC</option>
                                <option value="EST">Eastern Time</option>
                                <option value="PST">Pacific Time</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date Format</label>
                            <select class="form-select" name="date_format">
                                <option value="Y-m-d">YYYY-MM-DD</option>
                                <option value="d/m/Y">DD/MM/YYYY</option>
                                <option value="m/d/Y">MM/DD/YYYY</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary hover-lift">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-lg glass-card mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="bi bi-bell me-2"></i>Notification Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.notifications') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" name="email_notifications" checked>
                                <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                            </div>
                            <small class="text-muted">Receive email notifications for important updates</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="orderUpdates" name="order_updates" checked>
                                <label class="form-check-label" for="orderUpdates">Order Updates</label>
                            </div>
                            <small class="text-muted">Get notified about order status changes</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="marketingEmails" name="marketing_emails">
                                <label class="form-check-label" for="marketingEmails">Marketing Emails</label>
                            </div>
                            <small class="text-muted">Receive promotional offers and updates</small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary hover-lift">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card border-0 shadow-sm rounded-lg glass-card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="mb-0 text-primary">
                        <i class="bi bi-shield-lock me-2"></i>Security Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.security') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="twoFactorAuth" name="two_factor_auth">
                                <label class="form-check-label" for="twoFactorAuth">Two-Factor Authentication</label>
                            </div>
                            <small class="text-muted">Add an extra layer of security to your account</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="loginAlerts" name="login_alerts" checked>
                                <label class="form-check-label" for="loginAlerts">Login Alerts</label>
                            </div>
                            <small class="text-muted">Get notified when someone logs into your account</small>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary hover-lift">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-select:focus, .form-check-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
    }
</style>
@endpush
@endsection 