<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <title>Reset Password - CAS Private Care LLC</title>
    <meta name="description" content="Reset your CAS Private Care LLC account password.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url('/reset-password') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .auth-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            z-index: 0;
        }

        .auth-bg-slice {
            flex: 1;
            background-size: cover;
            background-position: center;
            transform: skewX(-5deg);
            margin: 0 -2%;
        }

        .auth-bg-slice:nth-child(1) {
            background-image: url('https://images.unsplash.com/photo-1609220136736-443140cffec6?w=800');
        }

        .auth-bg-slice:nth-child(2) {
            background-image: url('https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=800');
        }

        .auth-bg-slice:nth-child(3) {
            background-image: url('https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800');
        }

        .auth-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(30, 64, 175, 0.9) 0%, rgba(59, 130, 246, 0.85) 50%, rgba(96, 165, 250, 0.8) 100%);
            z-index: 1;
        }

        .auth-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem;
            width: 90%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .auth-logo img {
            height: 130px;
            width: auto;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: #64748b;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
            font-size: 0.95rem;
        }

        .auth-footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            z-index: 3;
        }

        .back-home a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            transition: all 0.3s;
        }

        .back-home a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateX(-5px);
        }

        .message {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .message.success {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .message.error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .message.info {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }

        @media (max-width: 768px) {
            .auth-container {
                padding: 2rem;
                margin: 1rem;
            }

            .back-home {
                top: 1rem;
                left: 1rem;
            }
        }

        /* Accessibility - Focus Visible Styles */
        .form-input:focus-visible,
        .btn-submit:focus-visible,
        .back-home a:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
        }

        .password-toggle:focus-visible {
            outline: 2px solid #3b82f6;
            border-radius: 4px;
        }

        .password-strength {
            margin-top: 0.375rem;
        }

        .password-strength-bar {
            width: 100%;
            height: 4px;
            background-color: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 0.375rem;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-fill.weak {
            width: 33%;
            background-color: #ef4444;
        }

        .password-strength-fill.fair {
            width: 66%;
            background-color: #f59e0b;
        }

        .password-strength-fill.good {
            width: 85%;
            background-color: #3b82f6;
        }

        .password-strength-fill.strong {
            width: 100%;
            background-color: #10b981;
        }

        .password-strength-text {
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .password-strength-text.weak {
            color: #ef4444;
        }

        .password-strength-text.fair {
            color: #f59e0b;
        }

        .password-strength-text.good {
            color: #3b82f6;
        }

        .password-strength-text.strong {
            color: #10b981;
        }

        .password-requirements {
            margin-top: 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            transition: all 0.2s ease;
        }

        .requirement-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: bold;
            color: #dc2626;
            background-color: #fee2e2;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .requirement-item.valid .requirement-icon {
            color: #059669;
            background-color: #d1fae5;
        }

        .requirement-item.valid .requirement-icon::before {
            content: '✓';
        }

        .requirement-item:not(.valid) .requirement-icon::before {
            content: '✗';
        }

        .requirement-text {
            color: #64748b;
        }

        .requirement-item.valid .requirement-text {
            color: #059669;
        }

        .password-match {
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .password-match.match {
            color: #10b981;
        }

        .password-match.no-match {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="auth-bg">
        <div class="auth-bg-slice"></div>
        <div class="auth-bg-slice"></div>
        <div class="auth-bg-slice"></div>
    </div>

    <div class="back-home">
        <a href="{{ url('/login') }}">
            <i class="bi bi-arrow-left"></i>
            Back to Login
        </a>
    </div>

    <div class="auth-container">
        <div class="auth-logo">
            <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo" width="130" height="130" fetchpriority="high">
        </div>

        <div class="auth-header">
            <h1>Reset Password</h1>
            <p>Enter your new password below</p>
        </div>

        @if(session('success'))
            <div class="message success">
                <i class="bi bi-check-circle" style="margin-right: 0.5rem;"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="message error">
                <i class="bi bi-x-circle" style="margin-right: 0.5rem;"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="message error">
                <i class="bi bi-x-circle" style="margin-right: 0.5rem;"></i>
                {{ $errors->first() }}
            </div>
        @endif

        @if(isset($valid) && $valid)
            <form method="POST" action="{{ url('/reset-password') }}" id="resetPasswordForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-input" placeholder="Enter your new password" required autocomplete="new-password" minlength="8" aria-required="true">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon')">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <div id="password-strength" class="password-strength">
                        <div class="password-strength-bar">
                            <div id="password-strength-fill" class="password-strength-fill"></div>
                        </div>
                        <div id="password-strength-text" class="password-strength-text"></div>
                    </div>
                    <div id="password-requirements" class="password-requirements">
                        <div class="requirement-item" id="req-length">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">At least 8 characters</span>
                        </div>
                        <div class="requirement-item" id="req-uppercase">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">One capital letter</span>
                        </div>
                        <div class="requirement-item" id="req-digit">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">One digit</span>
                        </div>
                        <div class="requirement-item" id="req-special">
                            <span class="requirement-icon"></span>
                            <span class="requirement-text">One special character</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Confirm your new password" required autocomplete="new-password" minlength="8" aria-required="true">
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'toggleIconConfirm')">
                            <i class="bi bi-eye" id="toggleIconConfirm"></i>
                        </button>
                    </div>
                    <div id="password-match" class="password-match"></div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">Reset Password</button>
            </form>
        @else
            <div class="message error">
                <i class="bi bi-x-circle" style="margin-right: 0.5rem;"></i>
                This password reset link is invalid or has expired. Please request a new one.
            </div>
            <div class="auth-footer">
                <a href="{{ url('/login') }}">Back to Login</a> | 
                <a href="#" onclick="requestNewReset(); return false;">Request New Reset Link</a>
            </div>
        @endif

        <div class="auth-footer" style="margin-top: 1.5rem;">
            Remember your password? <a href="{{ url('/login') }}">Login</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        function requestNewReset() {
            const email = '{{ $email ?? "" }}';
            if (email) {
                window.location.href = '/login';
            } else {
                window.location.href = '/login';
            }
        }

        // Form validation and password strength
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetPasswordForm');
            const passwordInput = document.getElementById('password');
            const passwordStrengthFill = document.getElementById('password-strength-fill');
            const passwordStrengthText = document.getElementById('password-strength-text');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const passwordMatchDiv = document.getElementById('password-match');

            // Password strength indicator
            if (passwordInput && passwordStrengthFill && passwordStrengthText) {
                passwordInput.addEventListener('input', function() {
                    checkPasswordStrength(this.value);
                    checkPasswordMatch();
                });
            }

            // Password confirmation match indicator
            if (passwordConfirmInput && passwordMatchDiv) {
                passwordConfirmInput.addEventListener('input', function() {
                    checkPasswordMatch();
                });
            }

            function checkPasswordStrength(password) {
                if (!password) {
                    passwordStrengthFill.style.width = '0%';
                    passwordStrengthFill.className = 'password-strength-fill';
                    passwordStrengthText.textContent = '';
                    passwordStrengthText.className = 'password-strength-text';
                    // Reset all requirements
                    updatePasswordRequirements(password);
                    return;
                }

                // Update individual requirements first
                updatePasswordRequirements(password);

                // Check if all requirements are met
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasDigit = /[0-9]/.test(password);
                const hasSpecial = /[^a-zA-Z0-9]/.test(password);

                const allRequirementsMet = hasLength && hasUppercase && hasDigit && hasSpecial;

                let strength = 0;
                let strengthText = '';
                let strengthClass = '';

                // If all requirements are met, it's a strong password
                if (allRequirementsMet) {
                    strengthText = 'Strong password';
                    strengthClass = 'strong';
                } else {
                    // Length check
                    if (password.length >= 8) strength++;
                    if (password.length >= 12) strength++;

                    // Character variety checks
                    if (/[a-z]/.test(password)) strength++; // lowercase
                    if (/[A-Z]/.test(password)) strength++; // uppercase
                    if (/[0-9]/.test(password)) strength++; // numbers
                    if (/[^a-zA-Z0-9]/.test(password)) strength++; // special characters

                    // Determine strength level
                    if (strength <= 2) {
                        strengthText = 'Weak password';
                        strengthClass = 'weak';
                    } else if (strength <= 4) {
                        strengthText = 'Fair password';
                        strengthClass = 'fair';
                    } else if (strength <= 5) {
                        strengthText = 'Good password';
                        strengthClass = 'good';
                    } else {
                        strengthText = 'Strong password';
                        strengthClass = 'strong';
                    }
                }

                // Update UI - ensure width is set based on strength class
                passwordStrengthFill.className = `password-strength-fill ${strengthClass}`;
                
                // Explicitly set width based on strength class
                if (strengthClass === 'weak') {
                    passwordStrengthFill.style.width = '33%';
                } else if (strengthClass === 'fair') {
                    passwordStrengthFill.style.width = '66%';
                } else if (strengthClass === 'good') {
                    passwordStrengthFill.style.width = '85%';
                } else if (strengthClass === 'strong') {
                    passwordStrengthFill.style.width = '100%';
                } else {
                    passwordStrengthFill.style.width = '0%';
                }
                
                passwordStrengthText.textContent = strengthText;
                passwordStrengthText.className = `password-strength-text ${strengthClass}`;
            }

            function updatePasswordRequirements(password) {
                // Check length (at least 8 characters)
                const reqLength = document.getElementById('req-length');
                if (reqLength) {
                    if (password.length >= 8) {
                        reqLength.classList.add('valid');
                    } else {
                        reqLength.classList.remove('valid');
                    }
                }

                // Check uppercase letter
                const reqUppercase = document.getElementById('req-uppercase');
                if (reqUppercase) {
                    if (/[A-Z]/.test(password)) {
                        reqUppercase.classList.add('valid');
                    } else {
                        reqUppercase.classList.remove('valid');
                    }
                }

                // Check digit
                const reqDigit = document.getElementById('req-digit');
                if (reqDigit) {
                    if (/[0-9]/.test(password)) {
                        reqDigit.classList.add('valid');
                    } else {
                        reqDigit.classList.remove('valid');
                    }
                }

                // Check special character
                const reqSpecial = document.getElementById('req-special');
                if (reqSpecial) {
                    if (/[^a-zA-Z0-9]/.test(password)) {
                        reqSpecial.classList.add('valid');
                    } else {
                        reqSpecial.classList.remove('valid');
                    }
                }
            }

            function checkPasswordMatch() {
                if (!passwordInput || !passwordConfirmInput || !passwordMatchDiv) return;

                const password = passwordInput.value;
                const confirmPassword = passwordConfirmInput.value;

                if (!confirmPassword) {
                    passwordMatchDiv.textContent = '';
                    passwordMatchDiv.className = 'password-match';
                    return;
                }

                if (password === confirmPassword) {
                    passwordMatchDiv.textContent = '✓ Passwords match';
                    passwordMatchDiv.className = 'password-match match';
                } else {
                    passwordMatchDiv.textContent = '✗ Passwords do not match';
                    passwordMatchDiv.className = 'password-match no-match';
                }
            }

            // Form submission validation
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = passwordInput.value;
                    const passwordConfirmation = passwordConfirmInput.value;
                    const submitBtn = document.getElementById('submitBtn');

                    if (password !== passwordConfirmation) {
                        e.preventDefault();
                        alert('Passwords do not match. Please try again.');
                        return false;
                    }

                    if (password.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long.');
                        return false;
                    }

                    // Check if all requirements are met
                    const hasLength = password.length >= 8;
                    const hasUppercase = /[A-Z]/.test(password);
                    const hasDigit = /[0-9]/.test(password);
                    const hasSpecial = /[^a-zA-Z0-9]/.test(password);

                    if (!hasLength || !hasUppercase || !hasDigit || !hasSpecial) {
                        e.preventDefault();
                        alert('Please ensure your password meets all requirements.');
                        return false;
                    }

                    // Disable button to prevent double submission
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Resetting...';
                });
            }
        });
    </script>
    @include('partials.cookie-consent')
</body>
</html>

