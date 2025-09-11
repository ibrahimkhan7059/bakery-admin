<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/custom-theme.css') }}">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        body {
            background: var(--main-bg-dark, #F7E4DF);
            min-height: 100vh;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .forgot-password-container {
            background: var(--card-bg, #fff);
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 600px;
            margin: 0;
        }
        .bakehub-logo {
            width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 1.5rem auto;
        }
        .forgot-password-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .forgot-password-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .forgot-password-header p {
            color: #666;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-reset {
            background: var(--primary-color, #FF6F61);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-reset:hover {
            background: #ff3c2a;
            box-shadow: 0 5px 15px rgba(255, 111, 97, 0.2);
        }
        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }
        .back-to-login a {
            color: #667eea;
            text-decoration: none;
        }
        .back-to-login a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .input-group-text {
            background-color: transparent;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <div class="text-center">
            <img src="{{ asset('images/bakehub-logo.png') }}" alt="BakeHub Logo" class="bakehub-logo">
    </div>
        <div class="forgot-password-header">
            <h2>Reset Password</h2>
            <p>Enter your email address and we'll send you instructions to reset your password.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" id="resetForm">
            @csrf
            <div class="form-floating mb-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input id="email" type="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" 
                           required autocomplete="email" autofocus 
                           placeholder="Email Address">
                </div>
                @error('email')
                    <span class="error-message">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <div class="invalid-feedback" id="emailError"></div>
            </div>

            <button type="submit" class="btn btn-reset" id="submitBtn">
                <i class="bi bi-envelope-paper me-2"></i>Send Reset Link
            </button>

            <div class="back-to-login">
                <a href="{{ route('login') }}">
                    <i class="bi bi-arrow-left me-1"></i>Back to Login
                </a>
        </div>
    </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnText = submitBtn.innerHTML;

            // Email validation
            if (!email) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please enter your email address.',
                    icon: 'error',
                    confirmButtonColor: '#FF6F61'
                });
                return;
            }

            if (!email.match(/^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$/)) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please enter a valid email address.',
                    icon: 'error',
                    confirmButtonColor: '#FF6F61'
                });
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';

            // Send AJAX request
            fetch('{{ route("password.email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'Success!',
                        text: 'Password reset link has been sent to your email.',
                        icon: 'success',
                        confirmButtonColor: '#FF6F61',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirect to login page after 2 seconds
                        window.location.href = '{{ route("login") }}';
                    });
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Something went wrong. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#FF6F61'
                });
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            });
        });
    </script>
</body>
</html>
