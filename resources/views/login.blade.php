<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <title>Login - CAS Private Care LLC</title>
    <meta name="description" content="Login to your CAS Private Care LLC account to manage caregiving services, bookings, and more.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url('/login') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            font-family: 'Inter', sans-serif;
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
        }

        .remember-me input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #3b82f6;
        }

        .forgot-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
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

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 1rem;
        }

        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-login:has(> :only-child) {
            grid-template-columns: 1fr;
        }

        .social-btn {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
        }

        .social-btn:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
        }

        .social-btn i {
            font-size: 1.2rem;
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

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 400px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-body p {
            color: #64748b;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .message {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
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

        @media (max-width: 768px) {
            .auth-container {
                padding: 2rem;
                margin: 1rem;
            }

            .social-login {
                grid-template-columns: 1fr;
            }

            .back-home {
                top: 1rem;
                left: 1rem;
            }

            .modal-content {
                margin: 1rem;
            }

            .modal-body {
                padding: 1.5rem;
            }

            .demo-credentials-panel {
                display: none;
            }
        }

        /* Accessibility - Focus Visible Styles */
        .form-input:focus-visible,
        .btn-submit:focus-visible,
        .social-btn:focus-visible,
        .forgot-link:focus-visible,
        .back-home a:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
        }

        .password-toggle:focus-visible {
            outline: 2px solid #3b82f6;
            border-radius: 4px;
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
        <a href="{{ url('/') }}">
            <i class="bi bi-arrow-left"></i>
            Back to Home
        </a>
    </div>

    {{-- Demo credentials panel - only visible in local/development environment --}}
    @if(config('app.env') === 'local' || config('app.debug') === true)
    <div class="demo-credentials-panel" style="position: fixed; top: 2rem; right: 2rem; z-index: 3; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); padding: 1rem; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.3); min-width: 220px;">
        <h4 style="margin: 0 0 0.75rem 0; color: #1e40af; font-size: 0.9rem; font-weight: 600;">🔧 Demo Credentials (Dev Only)</h4>
        <div style="margin-bottom: 0.75rem;">
            <p style="margin: 0 0 0.25rem 0; font-size: 0.8rem; color: #64748b; font-weight: 500;">Client Account:</p>
            <p style="margin: 0; font-size: 0.75rem; color: #1e40af; font-family: monospace;">client@demo.com</p>
            <p style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #1e40af; font-family: monospace;">password123</p>
            <button onclick="fillDemo('client')" style="width: 100%; padding: 0.4rem; background: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.3s; margin-bottom: 0.5rem;">Fill Client Login</button>
        </div>
        <div style="margin-bottom: 0.5rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0 0 0.25rem 0; font-size: 0.8rem; color: #64748b; font-weight: 500;">Caregiver Account:</p>
            <p style="margin: 0; font-size: 0.75rem; color: #10b981; font-family: monospace;">caregiver@demo.com</p>
            <p style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #10b981; font-family: monospace;">password123</p>
            <button onclick="fillDemo('caregiver')" style="width: 100%; padding: 0.4rem; background: #10b981; color: white; border: none; border-radius: 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.3s; margin-bottom: 0.5rem;">Fill Caregiver Login</button>
        </div>
        <div style="margin-bottom: 0.5rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0 0 0.25rem 0; font-size: 0.8rem; color: #64748b; font-weight: 500;">Admin Account:</p>
            <p style="margin: 0; font-size: 0.75rem; color: #dc2626; font-family: monospace;">admin@demo.com</p>
            <p style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #dc2626; font-family: monospace;">password123</p>
            <button onclick="fillDemo('admin')" style="width: 100%; padding: 0.4rem; background: #dc2626; color: white; border: none; border-radius: 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.3s; margin-bottom: 0.5rem;">Fill Admin Login</button>
        </div>
        <div style="margin-bottom: 0.5rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0 0 0.25rem 0; font-size: 0.8rem; color: #64748b; font-weight: 500;">Marketing Staff:</p>
            <p style="margin: 0; font-size: 0.75rem; color: #616161; font-family: monospace;">marketing@demo.com</p>
            <p style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #616161; font-family: monospace;">password123</p>
            <button onclick="fillDemo('marketing')" style="width: 100%; padding: 0.4rem; background: #616161; color: white; border: none; border-radius: 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.3s; margin-bottom: 0.5rem;">Fill Marketing Login</button>
        </div>
        <div style="padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0 0 0.25rem 0; font-size: 0.8rem; color: #64748b; font-weight: 500;">Training Center:</p>
            <p style="margin: 0; font-size: 0.75rem; color: #616161; font-family: monospace;">training@demo.com</p>
            <p style="margin: 0 0 0.5rem 0; font-size: 0.75rem; color: #616161; font-family: monospace;">password123</p>
            <button onclick="fillDemo('training')" style="width: 100%; padding: 0.4rem; background: #616161; color: white; border: none; border-radius: 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.3s;">Fill Training Login</button>
        </div>
    </div>
    @endif

    <div class="auth-container">
        <div class="auth-logo">
            <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo">
        </div>

        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Login to continue to CAS Private Care LLC</p>
            @if(session('success'))
                <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.9rem; font-weight: 500;">
                    <i class="bi bi-check-circle" style="margin-right: 0.5rem;"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div style="background: #dbeafe; border: 1px solid #93c5fd; color: #1e40af; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.9rem; font-weight: 500;">
                    <i class="bi bi-info-circle" style="margin-right: 0.5rem;"></i>
                    {{ session('info') }}
                </div>
            @endif
        </div>

        <!-- Client-side popup banner (used for showing messages after actions like sending reset link) -->
        <div id="globalBanner" style="display:none; margin-bottom:1rem;
            padding:0.75rem; border-radius:8px; font-weight:600; font-size:0.95rem;">
            <i id="globalBannerIcon" class="bi bi-check-circle" style="margin-right:0.5rem;"></i>
            <span id="globalBannerMessage"></span>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            @if($errors->any())
            <div style="background: #fee; border: 1px solid #fcc; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; color: #c00;">
                {{ $errors->first() }}
            </div>
            @endif
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="your@email.com" required autocomplete="email" aria-required="true">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required autocomplete="current-password" aria-required="true">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>
                <a href="#" class="forgot-link" onclick="openForgotPasswordModal(event)">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-submit">Login</button>
        </form>

        <div class="divider">
            <span>or continue with</span>
        </div>

        <div class="social-login">
            @php
                $googleConfig = config('services.google');
                $facebookConfig = config('services.facebook');
                $googleEnabled = !empty($googleConfig['client_id']) && 
                                 $googleConfig['client_id'] !== 'your_google_client_id' &&
                                 !empty($googleConfig['client_secret']) &&
                                 $googleConfig['client_secret'] !== 'your_google_client_secret';
                $facebookEnabled = !empty($facebookConfig['client_id']) && 
                                   $facebookConfig['client_id'] !== 'your_facebook_app_id' &&
                                   is_numeric($facebookConfig['client_id']) &&
                                   !empty($facebookConfig['client_secret']) &&
                                   $facebookConfig['client_secret'] !== 'your_facebook_app_secret';
            @endphp
            
            @if($googleEnabled)
            <a href="{{ url('/auth/google') }}" class="social-btn">
                <i class="bi bi-google"></i>
                Google
            </a>
            @endif
            
            @if($facebookEnabled)
            <a href="{{ url('/auth/facebook') }}" class="social-btn">
                <i class="bi bi-facebook"></i>
                Facebook
            </a>
            @endif
        </div>

        <div class="auth-footer">
            Don't have an account? <a href="{{ url('/register') }}">Sign up</a>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reset Password</h2>
                <button type="button" class="modal-close" onclick="closeForgotPasswordModal()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body">
                <p>Enter your email address and we'll send you a link to reset your password.</p>
                <div id="messageContainer" style="display: none; margin-bottom: 1rem;"></div>
                <form id="forgotPasswordForm" onsubmit="submitForgotPassword(event)">
                    <div class="form-group">
                        <label for="resetEmail">Email Address</label>
                        <input type="email" id="resetEmail" class="form-input" placeholder="your@email.com" required>
                    </div>
                    <button type="submit" class="btn-submit">Send Reset Link</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
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
        
        function fillDemo(type) {
            if (type === 'client') {
                document.getElementById('email').value = 'client@demo.com';
            } else if (type === 'caregiver') {
                document.getElementById('email').value = 'caregiver@demo.com';
            } else if (type === 'admin') {
                document.getElementById('email').value = 'admin@demo.com';
            } else if (type === 'marketing') {
                document.getElementById('email').value = 'marketing@demo.com';
            } else if (type === 'training') {
                document.getElementById('email').value = 'training@demo.com';
            }
            document.getElementById('password').value = 'password123';
        }
        
        function openForgotPasswordModal(event) {
            event.preventDefault();
            document.getElementById('forgotPasswordModal').style.display = 'flex';
            // Pre-fill with current email if available
            const currentEmail = document.getElementById('email').value;
            if (currentEmail) {
                document.getElementById('resetEmail').value = currentEmail;
            }
        }

        function closeForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').style.display = 'none';
            document.getElementById('messageContainer').style.display = 'none';
        }

        function showMessage(message, type) {
            const container = document.getElementById('messageContainer');
            container.innerHTML = `<div class="message ${type}">${message}</div>`;
            container.style.display = 'block';
        }

        function showBanner(message, type = 'success', duration = 5000) {
            const banner = document.getElementById('globalBanner');
            const icon = document.getElementById('globalBannerIcon');
            const msg = document.getElementById('globalBannerMessage');
            banner.style.display = 'flex';
            banner.style.alignItems = 'center';
            banner.style.justifyContent = 'center';
            msg.textContent = message;
            if (type === 'success') {
                banner.style.background = '#dcfce7';
                banner.style.border = '1px solid #bbf7d0';
                banner.style.color = '#166534';
                icon.className = 'bi bi-check-circle';
            } else if (type === 'error') {
                banner.style.background = '#fee2e2';
                banner.style.border = '1px solid #fecaca';
                banner.style.color = '#dc2626';
                icon.className = 'bi bi-x-circle';
            } else {
                banner.style.background = '#dbeafe';
                banner.style.border = '1px solid #93c5fd';
                banner.style.color = '#1e40af';
                icon.className = 'bi bi-info-circle';
            }
            setTimeout(() => {
                banner.style.display = 'none';
            }, duration);
        }

        function submitForgotPassword(event) {
            event.preventDefault();
            const email = document.getElementById('resetEmail').value;
            
            // Create form data
            const formData = new FormData();
            formData.append('email', email);
            formData.append('_token', '{{ csrf_token() }}');
            
            // Submit to password reset endpoint
            fetch('/password/email', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                } else {
                    return response.json().then(err => Promise.reject(err));
                }
            })
            .then(data => {
                closeForgotPasswordModal();
                // show a top banner confirming the reset link was sent
                showBanner('Password reset link sent. Please check your email.', 'success');
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage(error.message || 'Email not found in our system.', 'error');
            });
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('forgotPasswordModal');
            if (event.target === modal) {
                closeForgotPasswordModal();
            }
        });

        // Remember me functionality
        document.addEventListener('DOMContentLoaded', function() {
            const rememberCheckbox = document.querySelector('input[name="remember"]');
            const emailInput = document.getElementById('email');
            const form = document.querySelector('form');
            
            // Load saved email if remember me was checked
            const savedEmail = localStorage.getItem('rememberedEmail');
            if (savedEmail) {
                emailInput.value = savedEmail;
                rememberCheckbox.checked = true;
            }
            
            // Handle form submission
            form.addEventListener('submit', function(e) {
                if (rememberCheckbox.checked) {
                    localStorage.setItem('rememberedEmail', emailInput.value);
                } else {
                    localStorage.removeItem('rememberedEmail');
                }
            });
        });
    </script>
</body>
</html>
