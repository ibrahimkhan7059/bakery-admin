@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Profile</h2>

    <div class="row">
        <div class="col-md-6">
            <!-- Update Profile Information -->
            <div class="card mb-4">
                <div class="card-header">Update Profile Information</div>
                <div class="card-body">
                    @if(View::exists('profile.partials.update-profile-information-form'))
                        @include('profile.partials.update-profile-information-form')
                    @else
                        <p class="text-danger">Profile update form missing!</p>
                    @endif
                </div>
            </div>

            <!-- Update Password -->
            <div class="card mb-4">
                <div class="card-header">Update Password</div>
                <div class="card-body">
                    @if(View::exists('profile.partials.update-password-form'))
                        @include('profile.partials.update-password-form')
                    @else
                        <p class="text-danger">Password update form missing!</p>
                    @endif
                </div>
            </div>

            <!-- Delete User Account -->
            <div class="card">
                <div class="card-header">Delete Account</div>
                <div class="card-body">
                    @if(View::exists('profile.partials.delete-user-form'))
                        @include('profile.partials.delete-user-form')
                    @else
                        <p class="text-danger">Delete account form missing!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
