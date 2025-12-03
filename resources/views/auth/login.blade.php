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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            overflow: hidden;
        }
        
        body {
            background: linear-gradient(135deg, #FFB6A3 0%, #F7E4DF 50%, #FFD6CC 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Animated background particles */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            top: 0;
            left: 0;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 111, 97, 0.15);
            border-radius: 50%;
            animation: float 20s infinite;
        }
        
        .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 100px; height: 100px; left: 60%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 40px; height: 40px; left: 80%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 70px; height: 70px; left: 40%; animation-delay: 8s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.3; }
            50% { transform: translateY(-100vh) rotate(360deg); opacity: 0.6; }
        }
        
        .login-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 950px;
            padding: 0 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            min-height: 550px;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #FF6F61 0%, #ff3c2a 100%);
            padding: 3rem 2.5rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }
        
        .login-left::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -50px;
            left: -50px;
        }
        
        .bakehub-logo {
            width: 140px;
            height: auto;
            margin-bottom: 2rem;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
            position: relative;
            z-index: 1;
        }
        
        .welcome-text {
            position: relative;
            z-index: 1;
        }
        
        .welcome-text h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome-text p {
            font-size: 1.1rem;
            opacity: 0.95;
            line-height: 1.6;
        }
        
        .login-right {
            flex: 1;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-header h2 {
            color: #333;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 12px 0 0 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #FF6F61;
            box-shadow: none;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #FF6F61;
            background: #fff5f4;
        }
        
        .input-group:focus-within .form-control {
            border-color: #FF6F61;
        }
        
        .form-control.is-valid {
            border-color: #28a745;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
        }
        
        .input-group:has(.form-control.is-valid) .input-group-text {
            border-color: #28a745;
            background: #f0fff4;
            color: #28a745;
        }
        
        .input-group:has(.form-control.is-invalid) .input-group-text {
            border-color: #dc3545;
            background: #fff5f5;
            color: #dc3545;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        .custom-validation-message {
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .forgot-link {
            color: #FF6F61;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .forgot-link:hover {
            color: #ff3c2a;
            text-decoration: underline;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #FF6F61 0%, #ff3c2a 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 111, 97, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 111, 97, 0.4);
            color: white;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-height: 90vh;
                overflow-y: auto;
            }
            
            .login-left {
                padding: 2rem 1.5rem;
            }
            
            .welcome-text h1 {
                font-size: 1.8rem;
            }
            
            .welcome-text p {
                font-size: 0.95rem;
            }
            
            .login-right {
                padding: 2rem 1.5rem;
            }
            
            .bakehub-logo {
                width: 100px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Left Side - Welcome Section -->
            <div class="login-left">
                <img src="{{ asset('images/bakehub-logo.png') }}" alt="BakeHub Logo" class="bakehub-logo">
                <div class="welcome-text">
                    <h1>Welcome to BakeHub</h1>
                    <p>Your trusted partner for delicious cakes and sweet moments. Login to manage your bakery business efficiently.</p>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="login-right">
                <div class="login-header">
                    <h2>Admin Login</h2>
                    <p>Enter your credentials to access your account</p>
                </div>
                
                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <!-- Email Input -->
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <input id="email" 
                               type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autocomplete="email" 
                               autofocus 
                               placeholder="Enter your email address" 
                               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"
                               maxlength="255">
                    </div>
                    @error('email')
                        <span class="error-message" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-1"></i><strong>{{ $message }}</strong>
                        </span>
                    @enderror

                    <!-- Password Input -->
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input id="password" 
                               type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               name="password" 
                               required 
                               autocomplete="current-password" 
                               placeholder="Enter your password">
                    </div>
                    @error('password')
                        <span class="error-message" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-1"></i><strong>{{ $message }}</strong>
                        </span>
                    @enderror

                    <!-- Forgot Password Link -->
                    <div class="text-end mb-4">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                <i class="bi bi-key-fill me-1"></i>Forgot Password?
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-login" id="loginBtn">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Dashboard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const form = document.getElementById('loginForm');
            const submitButton = document.getElementById('loginBtn');
            
            // Strict email validation regex - requires complete email with proper domain
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            
            // Function to validate email completely
            function isValidEmail(email) {
                if (!emailRegex.test(email)) {
                    return false;
                }
                
                // Check if email has @ symbol
                if (!email.includes('@')) {
                    return false;
                }
                
                const parts = email.split('@');
                if (parts.length !== 2) {
                    return false;
                }
                
                const [localPart, domainPart] = parts;
                
                // Check local part (before @)
                if (localPart.length === 0 || localPart.length > 64) {
                    return false;
                }
                
                // Check domain part (after @)
                if (domainPart.length === 0) {
                    return false;
                }
                
                // Must have at least one dot in domain
                if (!domainPart.includes('.')) {
                    return false;
                }
                
                // Check for valid domain extension
                const domainParts = domainPart.split('.');
                if (domainParts.length < 2) {
                    return false;
                }
                
                // Check last part (extension) is at least 2 characters
                const extension = domainParts[domainParts.length - 1];
                if (extension.length < 2) {
                    return false;
                }
                
                // Check no consecutive dots
                if (email.includes('..')) {
                    return false;
                }
                
                return true;
            }
            
            // Create custom validation message
            function createValidationMessage(input, message, type = 'error') {
                const existingMessage = input.parentNode.nextElementSibling;
                if (existingMessage && existingMessage.classList.contains('custom-validation-message')) {
                    existingMessage.remove();
                }
                
                if (message) {
                    const messageElement = document.createElement('div');
                    messageElement.className = `custom-validation-message ${type === 'error' ? 'text-danger' : 'text-success'}`;
                    messageElement.innerHTML = `<i class="bi bi-${type === 'error' ? 'exclamation-circle-fill' : 'check-circle-fill'} me-1"></i>${message}`;
                    input.parentNode.insertAdjacentElement('afterend', messageElement);
                }
            }
            
            // Real-time email validation
            emailInput.addEventListener('input', function() {
                const email = this.value.trim();
                
                if (email === '') {
                    this.classList.remove('is-invalid', 'is-valid');
                    const existingMessage = this.parentNode.nextElementSibling;
                    if (existingMessage && existingMessage.classList.contains('custom-validation-message')) {
                        existingMessage.remove();
                    }
                    return;
                }
                
                if (!isValidEmail(email)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    
                    // Provide specific error messages
                    let errorMsg = 'Please enter a valid email address';
                    if (!email.includes('@')) {
                        errorMsg = 'Email must contain @ symbol';
                    } else if (!email.split('@')[1] || !email.split('@')[1].includes('.')) {
                        errorMsg = 'Email must have a valid domain (e.g., @gmail.com)';
                    } else if (email.includes('..')) {
                        errorMsg = 'Email cannot have consecutive dots';
                    }
                    
                    createValidationMessage(this, errorMsg);
                } else if (email.length > 255) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                    createValidationMessage(this, 'Email address is too long');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                    createValidationMessage(this, 'Valid email address', 'success');
                }
            });
            
            // Password validation
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
                if (password === '') {
                    this.classList.remove('is-invalid', 'is-valid');
                    return;
                }
                
                if (password.length < 6) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                const password = passwordInput.value;
                
                // Validate email
                if (email === '' || !isValidEmail(email)) {
                    e.preventDefault();
                    emailInput.classList.add('is-invalid');
                    
                    let errorMsg = 'Please enter a valid email address';
                    if (!email.includes('@')) {
                        errorMsg = 'Email must contain @ symbol';
                    } else if (!email.split('@')[1] || !email.split('@')[1].includes('.')) {
                        errorMsg = 'Email must have a valid domain (e.g., @gmail.com)';
                    }
                    
                    createValidationMessage(emailInput, errorMsg);
                    emailInput.focus();
                    return false;
                }
                
                // Validate password
                if (password === '') {
                    e.preventDefault();
                    passwordInput.classList.add('is-invalid');
                    passwordInput.focus();
                    return false;
                }
                
                // Show loading state
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Logging in...';
                submitButton.disabled = true;
            });
            
            // Email suggestions for common typos
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim().toLowerCase();
                
                if (email && email.includes('@')) {
                    const [localPart, domain] = email.split('@');
                    
                    const suggestions = {
                        'gmai.com': 'gmail.com',
                        'gmial.com': 'gmail.com',
                        'gamail.com': 'gmail.com',
                        'yahooo.com': 'yahoo.com',
                        'yaho.com': 'yahoo.com',
                        'hotmial.com': 'hotmail.com',
                        'hotmai.com': 'hotmail.com',
                        'outlok.com': 'outlook.com'
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
