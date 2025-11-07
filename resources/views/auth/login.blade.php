<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/custom-theme.css') }}">
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
        .login-container {
            background: var(--card-bg, #fff);
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 800px;
            margin: 0;
        }
        .bakehub-logo {
            width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 1.5rem auto;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .login-header p {
            color: #666;
            margin-bottom: 0;
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
        .form-floating {
            margin-bottom: 1.5rem;
            
        }
        .btn-login {
            background: var(--primary-color, #FF6F61);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: #ff3c2a;
            box-shadow: 0 5px 15px rgba(255, 111, 97, 0.2);
            color: #fff;
        }
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .remember-me input {
            margin-right: 0.5rem;
        }
        .forgot-password {
            text-align: right;
            margin-bottom: 1.5rem;
        }
        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .forgot-password a:hover {
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
        .form-control:focus {
            border-color: #ddd;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: #667eea;
        }
        
        /* Enhanced validation styles */
        .form-control.is-valid {
            border-color: #28a745;
            background-image: none;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: none;
        }
        
        .input-group .form-control.is-valid,
        .input-group .form-control.is-invalid {
            border-left: 1px solid;
        }
        
        .input-group:has(.form-control.is-valid) .input-group-text {
            border-color: #28a745;
            color: #28a745;
        }
        
        .input-group:has(.form-control.is-invalid) .input-group-text {
            border-color: #dc3545;
            color: #dc3545;
        }
        
        .custom-validation-message {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="text-center">
            <img src="{{ asset('images/bakehub-logo.png') }}" alt="BakeHub Logo" class="bakehub-logo" style="width:120px;height:auto;margin-bottom:1.5rem;">
        </div>
        <div class="login-header">
            <h2>Welcome Back</h2>
            <p>Please login to your account</p>
        </div>
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-floating">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                           placeholder="Email Address" 
                           pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                           title="Please enter a valid email address (e.g., user@example.com)"
                           maxlength="255">
                </div>
                @error('email')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-floating">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                           name="password" required autocomplete="current-password" placeholder="Password">
                </div>
                @error('password')
                    <span class="error-message" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="remember-me">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>
                <div class="forgot-password">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">
                            Forgot Password?
                        </a>
                    @endif
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                </button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const form = document.querySelector('form');
            const submitButton = document.querySelector('button[type="submit"]');
            
            // Email validation regex
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            // Create custom validation message element
            function createValidationMessage(message, type = 'error') {
                const existingMessage = emailInput.parentNode.parentNode.querySelector('.custom-validation-message');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                const messageElement = document.createElement('div');
                messageElement.className = `custom-validation-message ${type === 'error' ? 'text-danger' : 'text-success'}`;
                messageElement.style.fontSize = '0.875rem';
                messageElement.style.marginTop = '0.25rem';
                messageElement.innerHTML = `<i class="bi bi-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i> ${message}`;
                
                emailInput.parentNode.parentNode.appendChild(messageElement);
                return messageElement;
            }
            
            // Real-time email validation
            emailInput.addEventListener('input', function() {
                const email = this.value.trim();
                
                // Remove custom validation message if field is empty
                if (email === '') {
                    const existingMessage = this.parentNode.parentNode.querySelector('.custom-validation-message');
                    if (existingMessage) {
                        existingMessage.remove();
                    }
                    this.classList.remove('is-invalid', 'is-valid');
                    return;
                }
                
                // Validate email format
                if (!emailRegex.test(email)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    createValidationMessage('Please enter a valid email address (e.g., user@example.com)');
                } else if (email.length > 255) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    createValidationMessage('Email address cannot exceed 255 characters');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    createValidationMessage('Valid email address', 'success');
                }
            });
            
            // Enhanced form submission validation
            form.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                
                if (email === '') {
                    e.preventDefault();
                    emailInput.classList.add('is-invalid');
                    createValidationMessage('Please enter your email address');
                    emailInput.focus();
                    return false;
                }
                
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    emailInput.classList.add('is-invalid');
                    createValidationMessage('Please enter a valid email address');
                    emailInput.focus();
                    return false;
                }
                
                if (email.length > 255) {
                    e.preventDefault();
                    emailInput.classList.add('is-invalid');
                    createValidationMessage('Email address cannot exceed 255 characters');
                    emailInput.focus();
                    return false;
                }
                
                // Show loading state
                submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Logging in...';
                submitButton.disabled = true;
            });
            
            // Email format suggestions for common domains
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim().toLowerCase();
                const commonDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com'];
                
                if (email && email.includes('@')) {
                    const [localPart, domain] = email.split('@');
                    
                    // Suggest corrections for common typos
                    const suggestions = {
                        'gmai.com': 'gmail.com',
                        'gmial.com': 'gmail.com',
                        'gamail.com': 'gmail.com',
                        'yahooo.com': 'yahoo.com',
                        'yaho.com': 'yahoo.com',
                        'hotmial.com': 'hotmail.com',
                        'hotmai.com': 'hotmail.com',
                        'outlok.com': 'outlook.com',
                        'outloo.com': 'outlook.com'
                    };
                    
                    if (suggestions[domain]) {
                        const suggestedEmail = localPart + '@' + suggestions[domain];
                        if (confirm(`Did you mean: ${suggestedEmail}?`)) {
                            this.value = suggestedEmail;
                            this.dispatchEvent(new Event('input'));
                        }
                    }
                }
            });
        });
    </script>
    
</body>
</html>
