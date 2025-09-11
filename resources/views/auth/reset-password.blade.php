<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Set New Password</title>
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
        .reset-password-container {
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
        .reset-password-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .reset-password-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .reset-password-header p {
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
        .password-requirements {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
            padding-left: 1rem;
        }
        .password-requirements ul {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 0;
        }
        .password-requirements li {
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
        }
        .password-requirements li i {
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }
        .requirement-met {
            color: #198754;
        }
        .requirement-not-met {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="text-center">
            <img src="{{ asset('images/bakehub-logo.png') }}" alt="BakeHub Logo" class="bakehub-logo">
        </div>
        <div class="reset-password-header">
            <h2>Set New Password</h2>
            <p>Please create a strong password for your account</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" id="resetForm">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <input type="hidden" name="email" value="{{ $request->email }}">

            <div class="form-floating mb-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input id="password" type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           name="password" required autocomplete="new-password" 
                           placeholder="New Password">
                    <span class="input-group-text" style="border-left: none; cursor: pointer;" 
                          onclick="togglePassword('password', 'togglePassword')">
                        <i class="bi bi-eye" id="togglePassword"></i>
                    </span>
                </div>
                @error('password')
                    <span class="error-message">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
                <div class="password-requirements">
                    <ul>
                        <li id="length-requirement">
                            <i class="bi bi-circle"></i> At least 8 characters
                        </li>
                        <li id="uppercase-requirement">
                            <i class="bi bi-circle"></i> One uppercase letter
                        </li>
                        <li id="lowercase-requirement">
                            <i class="bi bi-circle"></i> One lowercase letter
                        </li>
                        <li id="number-requirement">
                            <i class="bi bi-circle"></i> One number
                        </li>
                        <li id="special-requirement">
                            <i class="bi bi-circle"></i> One special character
                        </li>
                    </ul>
                </div>
        </div>

            <div class="form-floating mb-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input id="password_confirmation" type="password" 
                           class="form-control" name="password_confirmation" 
                           required autocomplete="new-password" 
                           placeholder="Confirm Password">
                    <span class="input-group-text" style="border-left: none; cursor: pointer;" 
                          onclick="togglePassword('password_confirmation', 'toggleConfirmPassword')">
                        <i class="bi bi-eye" id="toggleConfirmPassword"></i>
                    </span>
                </div>
                <div class="invalid-feedback" id="confirmPasswordError"></div>
        </div>

            <button type="submit" class="btn btn-reset">
                <i class="bi bi-shield-lock me-2"></i>Reset Password
            </button>
    </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Password strength validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');
        const requirements = {
            length: { regex: /.{8,}/, element: document.getElementById('length-requirement') },
            uppercase: { regex: /[A-Z]/, element: document.getElementById('uppercase-requirement') },
            lowercase: { regex: /[a-z]/, element: document.getElementById('lowercase-requirement') },
            number: { regex: /[0-9]/, element: document.getElementById('number-requirement') },
            special: { regex: /[!@#$%^&*(),.?":{}|<>]/, element: document.getElementById('special-requirement') }
        };

        password.addEventListener('input', function() {
            const value = this.value;
            let allRequirementsMet = true;

            // Check each requirement
            for (const [key, requirement] of Object.entries(requirements)) {
                const icon = requirement.element.querySelector('i');
                if (requirement.regex.test(value)) {
                    icon.classList.remove('bi-circle');
                    icon.classList.add('bi-check-circle-fill');
                    requirement.element.classList.add('requirement-met');
                    requirement.element.classList.remove('requirement-not-met');
                } else {
                    icon.classList.remove('bi-check-circle-fill');
                    icon.classList.add('bi-circle');
                    requirement.element.classList.remove('requirement-met');
                    requirement.element.classList.add('requirement-not-met');
                    allRequirementsMet = false;
                }
            }
        });

        // Form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('password_confirmation');
            let isValid = true;

            // Check if all requirements are met
            for (const requirement of Object.values(requirements)) {
                if (!requirement.regex.test(password.value)) {
                    isValid = false;
                    break;
                }
            }

            // Check if passwords match
            if (password.value !== confirmPassword.value) {
                isValid = false;
                document.getElementById('confirmPasswordError').textContent = 'Passwords do not match';
                confirmPassword.classList.add('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                Swal.fire({
                    title: 'Validation Error',
                    text: 'Please ensure all password requirements are met and passwords match.',
                    icon: 'error',
                    confirmButtonColor: '#FF6F61'
                });
            }
        });

        // Show success message
        @if(session('status'))
            Swal.fire({
                title: 'Success!',
                text: "{{ session('status') }}",
                icon: 'success',
                confirmButtonColor: '#FF6F61'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('login') }}";
                }
            });
        @endif
    </script>
</body>
</html>
