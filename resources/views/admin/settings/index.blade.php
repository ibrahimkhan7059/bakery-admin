@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Application Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="store_name" class="form-label">Store Name</label>
                            <input type="text" class="form-control" id="store_name" name="store_name" value="{{ old('store_name') }}">
                        </div>

                        <div class="mb-3">
                            <label for="store_address" class="form-label">Store Address</label>
                            <textarea class="form-control" id="store_address" name="store_address" rows="3">{{ old('store_address') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="store_phone" class="form-label">Store Phone</label>
                            <input type="text" class="form-control" id="store_phone" name="store_phone" value="{{ old('store_phone') }}">
                        </div>

                        <div class="mb-3">
                            <label for="store_email" class="form-label">Store Email</label>
                            <input type="email" class="form-control" id="store_email" name="store_email" value="{{ old('store_email') }}">
                        </div>

                        <div class="mb-3">
                            <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" value="{{ old('tax_rate') }}" min="0" max="100" step="0.01">
                        </div>

                        <div class="mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                                <option value="INR">INR (₹)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 