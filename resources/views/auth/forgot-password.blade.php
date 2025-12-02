<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            position: relative;
        }
        
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FFB6A3 0%, #F7E4DF 50%, #FFD6CC 100%);
            z-index: -1;
        }
        
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            width: 20px;
            height: 20px;
            background: rgba(255, 111, 97, 0.15);
            border-radius: 50%;
            animation: float 20s infinite;
        }
        
        .particle:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; width: 60px; height: 60px; }
        .particle:nth-child(2) { left: 70%; top: 60%; animation-delay: 5s; width: 40px; height: 40px; }
        .particle:nth-child(3) { left: 30%; top: 80%; animation-delay: 10s; width: 50px; height: 50px; }
        .particle:nth-child(4) { left: 85%; top: 25%; animation-delay: 15s; width: 45px; height: 45px; }
        .particle:nth-child(5) { left: 50%; top: 40%; animation-delay: 8s; width: 35px; height: 35px; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
        
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .reset-wrapper {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            animation: slideUp 0.6s ease;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .reset-left {
            flex: 1;
            background: linear-gradient(135deg, #FF6F61 0%, #ff8a7a 50%, #FFB6A3 100%);
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .reset-left::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: float 30s infinite linear;
        }
        
        .reset-logo {
            width: 140px;
            height: auto;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }
        
        .reset-left h1 {
            color: #fff;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .reset-left p {
            color: rgba(255, 255, 255, 0.95);
            text-align: center;
            font-size: 1.05rem;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }
        
        .reset-icon {
            font-size: 5rem;
            color: rgba(255, 255, 255, 0.3);
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .reset-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .reset-header {
            margin-bottom: 35px;
        }
        
        .reset-header h2 {
            color: #333;
            font-size: 1.9rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .reset-header p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-label {
            display: block;
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 13px 15px 13px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #FF6F61;
            background: #fff5f4;
            box-shadow: 0 0 0 4px rgba(255, 111, 97, 0.1);
        }
        
        .btn-reset {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #FF6F61 0%, #ff8a7a 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-reset:hover {
            background: linear-gradient(135deg, #ff3c2a 0%, #FF6F61 100%);
            box-shadow: 0 8px 20px rgba(255, 111, 97, 0.3);
            transform: translateY(-2px);
        }
        
        .btn-reset:active {
            transform: translateY(0);
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 25px;
        }
        
        .back-to-login a {
            color: #FF6F61;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .back-to-login a:hover {
            color: #ff3c2a;
            gap: 8px;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 6px;
            display: block;
        }
        
        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            border: none;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        @media (max-width: 768px) {
            .reset-wrapper {
                flex-direction: column;
                margin: 10px;
            }
            
            .reset-left {
                padding: 40px 30px;
                min-height: auto;
            }
            
            .reset-left h1 {
                font-size: 1.8rem;
            }
            
            .reset-left p {
                font-size: 0.95rem;
            }
            
            .reset-icon {
                font-size: 3.5rem;
                margin-bottom: 15px;
            }
            
            .reset-logo {
                width: 100px;
                margin-bottom: 20px;
            }
            
            .reset-right {
                padding: 40px 30px;
            }
            
            .reset-header h2 {
                font-size: 1.6rem;
            }
            
            .reset-header p {
                font-size: 0.9rem;
            }
            
            .form-group {
                margin-bottom: 20px;
            }
            
            .btn-reset {
                padding: 12px;
                font-size: 0.95rem;
            }
        }
        
        @media (max-width: 480px) {
            .reset-container {
                padding: 10px;
            }
            
            .reset-wrapper {
                border-radius: 15px;
            }
            
            .reset-left {
                padding: 30px 20px;
            }
            
            .reset-left h1 {
                font-size: 1.5rem;
            }
            
            .reset-icon {
                font-size: 3rem;
            }
            
            .reset-logo {
                width: 80px;
            }
            
            .reset-right {
                padding: 30px 20px;
            }
            
            .reset-header {
                margin-bottom: 25px;
            }
            
            .reset-header h2 {
                font-size: 1.4rem;
            }
            
            .form-control {
                padding: 12px 12px 12px 40px;
                font-size: 0.9rem;
            }
            
            .input-icon {
                font-size: 1rem;
                left: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="particles">
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
            <div class="particle"></div>
        </div>
    </div>

    <div class="reset-container">
        <div class="reset-wrapper">
            <div class="reset-left">
                <i class="bi bi-shield-lock reset-icon"></i>
                <img src="{{ asset('images/bakehub-logo.png') }}" alt="BakeHub Logo" class="reset-logo">
                <h1>Forgot Password?</h1>
                <p>Don't worry! It happens. Enter your email and we'll send you a link to reset your password.</p>
            </div>

            <div class="reset-right">
                <div class="reset-header">
                    <h2>Reset Password</h2>
                    <p>We'll email you instructions to reset your password</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="resetForm">
                    @csrf
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input id="email" type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" value="{{ old('email') }}" 
                                   required autocomplete="email" autofocus 
                                   placeholder="Enter your email address">
                        </div>
                        @error('email')
                            <span class="error-message">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </span>
                        @enderror
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <button type="submit" class="btn-reset" id="submitBtn">
                        <i class="bi bi-send-fill"></i>
                        <span>Send Reset Link</span>
                    </button>

                    <div class="back-to-login">
                        <a href="{{ route('login') }}">
                            <i class="bi bi-arrow-left"></i>
                            <span>Back to Login</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const resetForm = document.getElementById('resetForm');
        const emailInput = document.getElementById('email');
        const submitBtn = document.getElementById('submitBtn');
        const emailError = document.getElementById('emailError');
        
        // Email validation on input
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            
            if (email && !isValidEmail(email)) {
                emailError.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i>Please enter a valid email address';
                this.style.borderColor = '#dc3545';
            } else {
                emailError.innerHTML = '';
                this.style.borderColor = email ? '#FF6F61' : '#e0e0e0';
            }
        });
        
        // Form submission
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = emailInput.value.trim();
            
            // Validation
            if (!email) {
                showError('Please enter your email address');
                emailInput.focus();
                return;
            }
            
            if (!isValidEmail(email)) {
                showError('Please enter a valid email address');
                emailInput.focus();
                return;
            }
            
            // Show loading state
            const originalBtnContent = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span><span>Sending...</span>';
            
            // Submit form
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'passwords.sent' || data.message) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Sent!',
                        text: 'Password reset link has been sent to your email address.',
                        confirmButtonColor: '#FF6F61',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '{{ route("login") }}';
                    });
                } else if (data.errors && data.errors.email) {
                    showError(data.errors.email[0]);
                } else {
                    throw new Error('Something went wrong');
                }
            })
            .catch(error => {
                showError(error.message || 'Unable to send reset link. Please try again.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
            });
        });
        
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
        
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message,
                confirmButtonColor: '#FF6F61'
            });
        }
    </script>
</body>
</html>
