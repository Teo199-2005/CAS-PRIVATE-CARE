<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#1e40af">
    @include('partials.favicon')
    <title>Login - CAS Private Care LLC</title>
    <meta name="description" content="Login to your CAS Private Care LLC account to manage caregiving services, bookings, and more.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="canonical" href="{{ url('/login') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Preload critical resources for performance -->
    <link rel="preload" href="{{ asset('logo.png') }}" as="image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    
    <!-- DNS prefetch for external resources -->
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!--
        Prevent aggressive caching of the login page.
        This helps when logging out and logging in as a different account in the same browser session.
    -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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

        .auth-footer p {
            margin: 0;
        }

        .auth-footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Message/Alert Styles */
        .message {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
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

        /* Form input error state */
        .form-input.error {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
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

        /* Skip Link for Accessibility */
        .skip-link {
            position: absolute;
            top: -100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1e40af;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0 0 8px 8px;
            z-index: 9999;
            font-weight: 600;
            text-decoration: none;
            transition: top 0.3s;
        }

        .skip-link:focus {
            top: 0;
        }

        /* Screen reader only class */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Reduced motion preference */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .form-input {
                border-width: 3px;
            }
            .btn-submit {
                border: 2px solid #fff;
            }
        }

        /* Additional responsive breakpoints */
        @media (max-width: 480px) {
            .auth-container {
                padding: 1.5rem;
                margin: 0.5rem;
                border-radius: 20px;
            }

            .auth-logo img {
                height: 100px;
            }

            .auth-header h1 {
                font-size: 1.5rem;
            }

            .form-input {
                padding: 0.75rem;
                font-size: 16px; /* Prevents zoom on iOS */
            }

            .btn-submit {
                padding: 0.875rem;
                font-size: 1rem;
            }

            .back-home {
                top: 0.5rem;
                left: 0.5rem;
            }

            .back-home a {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .auth-container {
                max-width: 420px;
            }
        }

        @media (min-width: 1025px) {
            .auth-container {
                max-width: 450px;
            }
        }

        /* Print styles */
        @media print {
            .auth-bg,
            .back-home,
            .social-login,
            .divider {
                display: none !important;
            }
            .auth-container {
                box-shadow: none;
                border: 1px solid #ccc;
            }
        }
    </style>
</head>
<body>
    <!-- Skip Link for Accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <div class="auth-bg">
        <div class="auth-bg-slice"></div>
        <div class="auth-bg-slice"></div>
        <div class="auth-bg-slice"></div>
    </div>

    <div class="back-home">
        <a href="{{ url('/') }}" aria-label="Go back to home page">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <span>Back to Home</span>
        </a>
    </div>

    <main class="auth-container" id="main-content" role="main">
        <div class="auth-logo">
            <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC - Professional Home Care Services Logo" width="130" height="130" fetchpriority="high">
        </div>

        <header class="auth-header">
            <h1>Welcome Back</h1>
            <p>Login to continue to CAS Private Care LLC</p>
            @if(session('success'))
                <div style="background: #dcfce7; border: 1px solid #bbf7d0; color: #166534; padding: 0.75rem; border-radius: 8px; margin-top: 1rem; font-size: 0.9rem; font-weight: 500;">
                    <i class="bi bi-check-circle" style="margin-right: 0.5rem;"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('info'))
                <div class="message info" role="alert" aria-live="polite">
                    <i class="bi bi-info-circle" aria-hidden="true" style="margin-right: 0.5rem;"></i>
                    {{ session('info') }}
                </div>
            @endif
        </header>

        <!-- Client-side popup banner (used for showing messages after actions like sending reset link) -->
        <div id="globalBanner" role="alert" aria-live="assertive" aria-atomic="true" style="display:none; margin-bottom:1rem;
            padding:0.75rem; border-radius:8px; font-weight:600; font-size:0.95rem;">
            <i id="globalBannerIcon" class="bi bi-check-circle" aria-hidden="true" style="margin-right:0.5rem;"></i>
            <span id="globalBannerMessage"></span>
        </div>

        <form method="POST" action="{{ route('login') }}" novalidate aria-label="Login form">
            @csrf
            @if(request()->has('redirect'))
            <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif
            @if($errors->any())
            <div class="message error" role="alert" aria-live="assertive">
                <i class="bi bi-exclamation-circle" aria-hidden="true" style="margin-right: 0.5rem;"></i>
                {{ $errors->first() }}
            </div>
            @endif
            <div class="form-group">
                <label for="email" id="email-label">Email Address <span class="sr-only">(required)</span></label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-input" 
                       placeholder="your@email.com" 
                       required 
                       autocomplete="email" 
                       aria-required="true"
                       aria-labelledby="email-label"
                       aria-describedby="email-hint"
                       inputmode="email">
                <span id="email-hint" class="sr-only">Enter your registered email address</span>
            </div>

            <div class="form-group">
                <label for="password" id="password-label">Password <span class="sr-only">(required)</span></label>
                <div class="password-wrapper">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           placeholder="Enter your password" 
                           required 
                           autocomplete="current-password" 
                           aria-required="true"
                           aria-labelledby="password-label"
                           aria-describedby="password-toggle-hint">
                    <button type="button" 
                            class="password-toggle" 
                            onclick="togglePassword()"
                            aria-label="Show password"
                            aria-pressed="false"
                            aria-controls="password">
                        <i class="bi bi-eye" id="toggleIcon" aria-hidden="true"></i>
                    </button>
                    <span id="password-toggle-hint" class="sr-only">Click the eye icon to show or hide password</span>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember" id="remember" aria-describedby="remember-hint">
                    <span>Remember me</span>
                    <span id="remember-hint" class="sr-only">Keep me logged in on this device</span>
                </label>
                <a href="#" class="forgot-link" onclick="openForgotPasswordModal(event)" role="button">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-submit" aria-label="Login to your account">
                <span>Login</span>
            </button>
        </form>

        <div class="divider" role="separator" aria-hidden="true">
            <span>or continue with</span>
        </div>

        <nav class="social-login" aria-label="Social login options">
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
            <a href="{{ url('/auth/google') }}" class="social-btn" aria-label="Continue with Google account">
                <i class="bi bi-google" aria-hidden="true"></i>
                <span>Google</span>
            </a>
            @endif
            
            @if($facebookEnabled)
            <a href="{{ url('/auth/facebook') }}" class="social-btn" aria-label="Continue with Facebook account">
                <i class="bi bi-facebook" aria-hidden="true"></i>
                <span>Facebook</span>
            </a>
            @endif
        </nav>

        <footer class="auth-footer">
            <p>Don't have an account? <a href="{{ url('/register') }}">Sign up</a></p>
        </footer>
    </main>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" 
         class="modal-overlay" 
         style="display: none;"
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-title"
         aria-describedby="modal-description">
        <div class="modal-content" role="document">
            <div class="modal-header">
                <h2 id="modal-title">Reset Password</h2>
                <button type="button" 
                        class="modal-close" 
                        onclick="closeForgotPasswordModal()"
                        aria-label="Close password reset dialog">
                    <i class="bi bi-x" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="modal-description">Enter your email address and we'll send you a link to reset your password.</p>
                <div id="messageContainer" role="alert" aria-live="polite" style="display: none; margin-bottom: 1rem;"></div>
                <form id="forgotPasswordForm" onsubmit="submitForgotPassword(event)" aria-label="Password reset form">
                    <div class="form-group">
                        <label for="resetEmail" id="reset-email-label">Email Address <span class="sr-only">(required)</span></label>
                        <input type="email" 
                               id="resetEmail" 
                               class="form-input" 
                               placeholder="your@email.com" 
                               required
                               aria-required="true"
                               aria-labelledby="reset-email-label"
                               inputmode="email">
                    </div>
                    <button type="submit" class="btn-submit" aria-label="Send password reset link">
                        <span>Send Reset Link</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password visibility toggle with accessibility support
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            const toggleButton = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
                toggleButton.setAttribute('aria-label', 'Hide password');
                toggleButton.setAttribute('aria-pressed', 'true');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
                toggleButton.setAttribute('aria-label', 'Show password');
                toggleButton.setAttribute('aria-pressed', 'false');
            }
        }

        // Modal management with focus trap
        let lastFocusedElement = null;
        const focusableSelectors = 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';
        
        function openForgotPasswordModal(event) {
            event.preventDefault();
            lastFocusedElement = document.activeElement;
            const modal = document.getElementById('forgotPasswordModal');
            modal.style.display = 'flex';
            
            // Pre-fill with current email if available
            const currentEmail = document.getElementById('email').value;
            if (currentEmail) {
                document.getElementById('resetEmail').value = currentEmail;
            }
            
            // Focus the first focusable element in modal
            const firstFocusable = modal.querySelector(focusableSelectors);
            if (firstFocusable) {
                setTimeout(() => firstFocusable.focus(), 100);
            }
            
            // Add event listeners for focus trap and escape key
            document.addEventListener('keydown', handleModalKeydown);
        }

        function closeForgotPasswordModal() {
            const modal = document.getElementById('forgotPasswordModal');
            modal.style.display = 'none';
            document.getElementById('messageContainer').style.display = 'none';
            document.getElementById('forgotPasswordForm').reset();
            
            // Remove event listener
            document.removeEventListener('keydown', handleModalKeydown);
            
            // Restore focus to trigger element
            if (lastFocusedElement) {
                lastFocusedElement.focus();
            }
        }

        function handleModalKeydown(event) {
            const modal = document.getElementById('forgotPasswordModal');
            
            // Close on Escape
            if (event.key === 'Escape') {
                closeForgotPasswordModal();
                return;
            }
            
            // Focus trap
            if (event.key === 'Tab') {
                const focusableElements = modal.querySelectorAll(focusableSelectors);
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];
                
                if (event.shiftKey) {
                    if (document.activeElement === firstElement) {
                        event.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        event.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        }

        function showMessage(message, type) {
            const container = document.getElementById('messageContainer');
            const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
            container.innerHTML = `<div class="message ${type}" role="alert"><i class="bi ${icon}" aria-hidden="true" style="margin-right: 0.5rem;"></i>${message}</div>`;
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

        function getCsrfToken() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') || '' : '';
        }
        async function refreshCsrfToken() {
            try {
                const r = await fetch('/csrf-token', { credentials: 'include' });
                if (!r.ok) return '';
                const data = await r.json();
                const token = data && data.token;
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (token && meta) {
                    meta.setAttribute('content', token);
                    return token;
                }
            } catch (_) {}
            return '';
        }

        async function submitForgotPassword(event) {
            event.preventDefault();
            const email = document.getElementById('resetEmail').value;
            const submitButton = event.target.querySelector('button[type="submit"]');
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<span>Sending...</span>';
            
            // Refresh CSRF token so it matches current session (avoids mismatch after long-lived tabs)
            const token = await refreshCsrfToken() || getCsrfToken();
            if (!token) {
                showMessage('Security token missing. Please refresh the page and try again.', 'error');
                submitButton.disabled = false;
                submitButton.innerHTML = '<span>Send Reset Link</span>';
                return;
            }
            
            // Send both header and body so Laravel accepts the token
            const formData = new FormData();
            formData.append('email', email);
            formData.append('_token', token);
            
            try {
                const response = await fetch('/password/email', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'include',
                    body: formData
                });
                
                const data = await response.json().catch(() => ({}));
                
                if (response.ok) {
                    closeForgotPasswordModal();
                    showBanner('Password reset link sent. Please check your email.', 'success');
                } else if (response.status === 419) {
                    showMessage('Session expired. Please refresh the page and try again.', 'error');
                } else {
                    showMessage(data.message || 'Email not found in our system.', 'error');
                }
            } catch (err) {
                showMessage('An error occurred. Please try again.', 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = '<span>Send Reset Link</span>';
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('forgotPasswordModal');
            if (event.target === modal) {
                closeForgotPasswordModal();
            }
        });

        // Remember me functionality and initialization
        document.addEventListener('DOMContentLoaded', function() {
            const rememberCheckbox = document.querySelector('input[name="remember"]');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const form = document.querySelector('form[action*="login"]');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Load saved email if remember me was checked
            const savedEmail = localStorage.getItem('rememberedEmail');
            if (savedEmail) {
                emailInput.value = savedEmail;
                rememberCheckbox.checked = true;
            }
            
            // Refresh CSRF token on load so it matches current session
            refreshCsrfToken();

            // Handle form submission with AJAX for better session handling
            form.addEventListener('submit', async function doLogin(eOrRetry) {
                const isRetry = eOrRetry === true;
                if (!isRetry && eOrRetry && eOrRetry.preventDefault) eOrRetry.preventDefault();
                
                // Save or remove email based on remember me
                if (rememberCheckbox.checked) {
                    localStorage.setItem('rememberedEmail', emailInput.value);
                } else {
                    localStorage.removeItem('rememberedEmail');
                }
                
                // Show loading state
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Logging in...</span>';
                
                // Clear any existing error messages
                const existingError = document.querySelector('.message.error');
                if (existingError) existingError.remove();
                
                const csrfToken = getCsrfToken();
                
                try {
                    const response = await fetch('/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'include',
                        body: JSON.stringify({
                            email: emailInput.value,
                            password: passwordInput.value,
                            remember: rememberCheckbox.checked
                        })
                    });

                    // On CSRF mismatch, refresh token and retry once
                    if (response.status === 419 && !isRetry) {
                        const newToken = await refreshCsrfToken();
                        if (newToken) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            return doLogin(true);
                        }
                    }
                    
                    const contentType = response.headers.get('content-type');
                    const isJson = contentType && contentType.includes('application/json');
                    const data = isJson ? await response.json() : {};
                    
                    if (response.ok && data.success) {
                        showBanner('Login successful! Redirecting...', 'success');
                        await new Promise(resolve => setTimeout(resolve, 500));
                        try {
                            await fetch('/api/profile', {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'include'
                            });
                        } catch (_) {}
                        window.location.href = data.redirect || '/dashboard';
                    } else {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'message error';
                        errorDiv.setAttribute('role', 'alert');
                        const msg = response.status === 419 ? 'Session expired. Please try again.' : (data.message || 'Invalid credentials');
                        errorDiv.innerHTML = `<i class="bi bi-exclamation-circle" style="margin-right: 0.5rem;"></i>${msg}`;
                        form.insertBefore(errorDiv, form.firstChild);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    form.removeEventListener('submit', doLogin);
                    form.submit();
                }
            });

            // Login page auto-refresh / session-sync helpers
            // Force a one-time reload when returning to /login via back button (bfcache)
            try {
                window.addEventListener('pageshow', function (event) {
                    if (event.persisted) {
                        window.location.reload();
                    }
                });
            } catch (_) {}

            // Periodic refresh to avoid CSRF/session edge cases (extended to 5 minutes)
            const AUTO_REFRESH_MINUTES = 5;
            setInterval(() => {
                const active = document.activeElement;
                const isTyping = active && (active.id === 'email' || active.id === 'password' || active.id === 'resetEmail');
                if (!isTyping) {
                    window.location.reload();
                }
            }, AUTO_REFRESH_MINUTES * 60 * 1000);

            // Enhanced form validation with accessibility announcements
            const inputs = document.querySelectorAll('.form-input[required]');
            inputs.forEach(input => {
                input.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    this.classList.add('error');
                    // Announce error to screen readers
                    const label = document.querySelector(`label[for="${this.id}"]`);
                    if (label) {
                        const errorMsg = `${label.textContent.trim()} is required`;
                        announceToScreenReader(errorMsg);
                    }
                });
                
                input.addEventListener('input', function() {
                    this.classList.remove('error');
                });
            });
        });

        // Screen reader announcement utility
        function announceToScreenReader(message) {
            const announcement = document.createElement('div');
            announcement.setAttribute('role', 'alert');
            announcement.setAttribute('aria-live', 'assertive');
            announcement.setAttribute('aria-atomic', 'true');
            announcement.className = 'sr-only';
            announcement.textContent = message;
            document.body.appendChild(announcement);
            setTimeout(() => announcement.remove(), 1000);
        }
    </script>
    @include('partials.cookie-consent')
</body>
</html>
