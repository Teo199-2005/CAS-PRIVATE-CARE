<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <title>Register - CAS Private Care LLC</title>
    <meta name="description" content="Create your CAS Private Care LLC account to connect with verified caregivers and access professional care services.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/register') }}">
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
            overflow-x: hidden;
            padding: 2rem 0;
        }

        .auth-bg {
            position: fixed;
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
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.95) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 36px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25), 
                        0 10px 30px rgba(0, 0, 0, 0.15),
                        0 0 0 1px rgba(255, 255, 255, 0.5),
                        inset 0 1px 0 rgba(255, 255, 255, 0.9);
            padding: 1.75rem 2rem;
            width: 92%;
            max-width: 680px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin: 1.5rem auto;
            overflow: hidden;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
            z-index: 1;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            position: relative;
        }

        .auth-logo::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            border-radius: 2px;
        }

        .auth-logo img {
            height: 75px;
            width: auto;
            filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.1));
            transition: transform 0.3s ease;
        }

        .auth-logo img:hover {
            transform: scale(1.02);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }

        .auth-header h1 {
            font-size: 1.625rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .auth-header p {
            color: #64748b;
            font-size: 0.8125rem;
            font-weight: 500;
            line-height: 1.3;
            margin: 0;
        }

        .auth-header a {
            margin-top: 0.5rem;
            font-size: 0.85rem;
        }

        .auth-header a {
            margin-top: 1rem;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .auth-header a:hover {
            transform: translateY(-1px);
        }



        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 0.75rem;
        }

        .form-group label {
            display: block;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 0.375rem;
            font-size: 0.875rem;
            letter-spacing: 0.01em;
            transition: color 0.2s ease;
        }

        .form-input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #1e293b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .form-input:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12), 0 4px 12px rgba(59, 130, 246, 0.15);
            background-color: #ffffff;
            transform: translateY(-1px);
        }

        .form-input::placeholder {
            color: #94a3b8;
            opacity: 0.8;
            font-weight: 400;
        }

        .form-input:disabled,
        .form-input[readonly] {
            background-color: #f8fafc;
            color: #64748b;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-input {
            padding-right: 3rem;
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
            font-size: 1.25rem;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
        }

        .password-toggle:hover {
            background-color: #f1f5f9;
            color: #475569;
        }

        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
        }

        .terms-checkbox {
            display: flex;
            align-items: start;
            gap: 0.625rem;
            margin-bottom: 0.75rem;
            font-size: 0.8rem;
            color: #475569;
            line-height: 1.4;
            padding: 0.5rem 0.75rem;
            background-color: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .terms-checkbox:hover {
            background-color: #f1f5f9;
            border-color: #cbd5e1;
        }

        .terms-checkbox input {
            width: 20px;
            height: 20px;
            cursor: pointer;
            margin-top: 0.15rem;
            accent-color: #3b82f6;
            flex-shrink: 0;
        }

        .terms-checkbox label {
            margin: 0;
            font-weight: 400;
            color: #475569;
        }

        .terms-checkbox a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .terms-checkbox a:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35), 0 2px 4px rgba(59, 130, 246, 0.2);
            letter-spacing: 0.02em;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.45), 0 4px 8px rgba(59, 130, 246, 0.3);
            background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
        }

        .btn-submit:hover::before {
            left: 100%;
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 0.75rem 0;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        .divider span {
            padding: 0 1.25rem;
            background-color: rgba(255, 255, 255, 0.98);
        }

        .social-login {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .social-login:has(> :only-child) {
            grid-template-columns: 1fr;
        }

        .social-btn {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #475569;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            font-size: 0.9rem;
        }

        .social-btn:hover {
            border-color: #3b82f6;
            background: #f0f9ff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
            transform: translateY(-1px);
        }

        .social-btn:active {
            transform: translateY(0);
        }

        .social-btn i {
            font-size: 1.2rem;
        }

        .auth-footer {
            text-align: center;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(226, 232, 240, 0.6);
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .auth-footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s ease;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
        }

        .auth-footer a:hover {
            background-color: #eff6ff;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background-color: #fef2f2;
            border-left: 3px solid #dc2626;
            border-radius: 6px;
        }

        .error-message::before {
            content: '⚠';
            font-size: 1rem;
        }

        .zip-location-display {
            margin-top: -8px;
            font-size: 0.75rem;
            color: #000000;
            font-weight: 600;
            line-height: 1.2;
            display: block;
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
            transition: color 0.2s ease;
        }

        .requirement-item.valid .requirement-text {
            color: #059669;
        }

        .password-match {
            margin-top: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            padding: 0.375rem 0.625rem;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-match.match {
            color: #059669;
            background-color: #d1fae5;
        }

        .password-match.no-match {
            color: #dc2626;
            background-color: #fee2e2;
        }

        .back-home {
            position: fixed;
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

        @media (min-width: 1024px) {
            .auth-container {
                max-width: 680px;
                padding: 1.75rem 2.25rem;
            }
        }

        /* Mobile Responsive - Small devices (≤480px) - Facebook-style */
        @media (max-width: 480px) {
            body {
                padding: 0;
                background: #f0f2f5;
            }

            .auth-bg,
            .auth-bg-slice {
                display: none;
            }

            .auth-container {
                padding: 1rem;
                margin: 0;
                border-radius: 0;
                max-width: 100%;
                width: 100%;
                box-shadow: none;
                background: #ffffff;
                border: none;
                backdrop-filter: none;
            }

            .auth-container::before {
                display: none;
            }

            .auth-logo {
                margin-bottom: 0.5rem;
                padding-bottom: 0;
            }

            .auth-logo::after {
                display: none;
            }

            .auth-logo img {
                height: 50px;
            }

            .auth-header {
                margin-bottom: 0.875rem;
                padding-bottom: 0;
                border-bottom: none;
            }

            .auth-header h1 {
                font-size: 1.5rem;
                line-height: 1.2;
                margin-bottom: 0.25rem;
            }

            .auth-header p {
                font-size: 0.875rem;
                color: #606770;
                margin: 0;
            }

            .form-group {
                margin-bottom: 0.625rem;
            }

            .form-group label {
                font-size: 0.875rem;
                margin-bottom: 0.375rem;
                font-weight: 500;
                color: #1c1e21;
            }

            .form-input {
                padding: 0.75rem;
                font-size: 0.9375rem;
                border-radius: 6px;
                min-height: 40px;
                border: 1px solid #ccd0d5;
                background: #f5f6f7;
                box-shadow: none;
            }

            .form-input:focus {
                border-color: #1877f2;
                background: #ffffff;
                box-shadow: 0 0 0 2px #e7f3ff;
                transform: none;
            }

            .form-input:hover {
                border-color: #ccd0d5;
                box-shadow: none;
            }

            /* Keep name fields side by side on mobile */
            .form-row {
                grid-template-columns: 1fr 1fr !important;
                gap: 0.5rem !important;
            }

            .password-wrapper .form-input {
                padding-right: 2.5rem;
            }

            .password-toggle {
                width: 2rem;
                height: 2rem;
                font-size: 1rem;
                right: 0.5rem;
                color: #606770;
            }

            .btn-submit {
                padding: 0.625rem;
                font-size: 1rem;
                border-radius: 6px;
                min-height: 44px;
                font-weight: 600;
                letter-spacing: 0;
                box-shadow: none;
                background: #1877f2;
                margin-top: 0.5rem;
            }

            .btn-submit:hover {
                background: #166fe5;
                transform: none;
                box-shadow: none;
            }

            .terms-checkbox {
                font-size: 0.6875rem;
                gap: 0.5rem;
                padding: 0.5rem 0;
                background: transparent;
                border: none;
                margin-bottom: 0.5rem;
                line-height: 1.3;
            }

            .terms-checkbox:hover {
                background: transparent;
                border: none;
            }

            .terms-checkbox input {
                width: 14px;
                height: 14px;
                margin-top: 0;
                flex-shrink: 0;
            }

            .terms-checkbox label {
                line-height: 1.3;
                color: #606770;
            }

            .divider {
                margin: 0.75rem 0;
                font-size: 0.75rem;
            }

            .divider::before,
            .divider::after {
                background: #dadde1;
            }

            .divider span {
                padding: 0 0.75rem;
                color: #606770;
            }

            .social-login {
                grid-template-columns: 1fr;
                gap: 0.625rem;
                margin-bottom: 0.75rem;
            }

            .social-btn {
                padding: 0.625rem;
                font-size: 0.9375rem;
                min-height: 44px;
                border: 1px solid #ccd0d5;
                border-radius: 6px;
                background: #ffffff;
                color: #1c1e21;
                font-weight: 600;
                box-shadow: none;
            }

            .social-btn:hover {
                background: #f5f6f7;
                transform: none;
                box-shadow: none;
                border-color: #ccd0d5;
            }

            .social-btn i {
                font-size: 1.125rem;
            }

            .auth-footer {
                font-size: 0.875rem;
                margin-top: 0.75rem;
                padding-top: 0.75rem;
                border-top: 1px solid #dadde1;
                color: #1c1e21;
            }

            .auth-footer a {
                color: #1877f2;
                font-weight: 600;
                padding: 0;
            }

            .auth-footer a:hover {
                background: transparent;
                text-decoration: underline;
                transform: none;
            }

            .back-home {
                top: 0.5rem;
                left: 0.5rem;
            }

            .back-home a {
                padding: 0.5rem 0.75rem;
                font-size: 0.8125rem;
                background: rgba(255, 255, 255, 0.9);
                color: #1c1e21;
            }

            .back-home a:hover {
                background: rgba(255, 255, 255, 1);
            }

            /* Password strength indicator */
            .password-strength-bar {
                height: 3px;
                margin-top: 0.25rem;
            }

            .password-strength-text {
                font-size: 0.6875rem;
                margin-top: 0.25rem;
                color: #606770;
            }

            .password-requirements {
                margin-top: 0.375rem;
                gap: 0.25rem;
            }

            .requirement-item {
                font-size: 0.75rem;
                gap: 0.375rem;
            }

            .requirement-icon {
                width: 1.125rem;
                height: 1.125rem;
                font-size: 0.65rem;
            }

            .password-match {
                font-size: 0.6875rem;
                margin-top: 0.25rem;
                padding: 0.25rem 0.5rem;
            }

            /* ZIP code location display */
            .zip-location-display {
                margin-top: -8px;
                font-size: 0.75rem;
                color: #000000;
                font-weight: 600;
                line-height: 1.2;
                display: block;
            }

            /* Error messages */
        .error-message {
            font-size: 0.75rem;
            padding: 0.375rem 0.625rem;
            margin-top: 0.25rem;
            background: #fff4e6;
            border-left: 3px solid #ff6b35;
            border-radius: 4px;
        }

        /* Email warning message */
        .email-warning-message {
            font-size: 0.875rem;
            padding: 0.625rem 0.875rem;
            margin-top: 0.5rem;
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-left: 3px solid #f59e0b;
            border-radius: 6px;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .email-warning-message i {
            font-size: 1rem;
            color: #f59e0b;
        }

            /* Email warning message */
            .email-warning-message {
                font-size: 0.875rem;
                padding: 0.625rem 0.875rem;
                margin-top: 0.5rem;
                background: #fef3c7;
                border: 1px solid #fbbf24;
                border-left: 3px solid #f59e0b;
                border-radius: 6px;
                color: #92400e;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-weight: 500;
            }

            .email-warning-message i {
                font-size: 1rem;
                color: #f59e0b;
            }

            .error-message::before {
                font-size: 0.8125rem;
            }

            /* Remove decorative elements on mobile */
            .auth-container.partner-registration::before {
                display: none;
            }

            /* Make change selection link smaller */
            .auth-header a {
                font-size: 0.8125rem;
                margin-top: 0.375rem;
            }
        }

        /* Mobile Responsive - Medium devices (481px-768px) - Facebook-style */
        @media (min-width: 481px) and (max-width: 768px) {
            body {
                background: #f0f2f5;
            }

            .auth-container {
                padding: 1.25rem;
                margin: 0.75rem auto;
                border-radius: 8px;
                max-width: 432px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), 0 8px 16px rgba(0, 0, 0, 0.1);
                background: #ffffff;
            }

            .auth-container::before {
                display: none;
            }

            .auth-logo {
                margin-bottom: 0.5rem;
            }

            .auth-logo img {
                height: 60px;
            }

            .auth-header {
                margin-bottom: 1rem;
                padding-bottom: 0;
                border-bottom: none;
            }

            .auth-header h1 {
                font-size: 1.75rem;
            }

            .auth-header p {
                font-size: 0.875rem;
                color: #606770;
            }

            .form-group {
                margin-bottom: 0.75rem;
            }

            .form-group label {
                font-size: 0.875rem;
                margin-bottom: 0.375rem;
            }

            .form-input {
                padding: 0.75rem;
                font-size: 0.9375rem;
                min-height: 40px;
                border: 1px solid #ccd0d5;
                background: #f5f6f7;
                border-radius: 6px;
            }

            .form-input:focus {
                border-color: #1877f2;
                background: #ffffff;
                box-shadow: 0 0 0 2px #e7f3ff;
            }

            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 0.625rem;
            }

            .btn-submit {
                padding: 0.625rem;
                font-size: 1rem;
                min-height: 44px;
                border-radius: 6px;
                background: #1877f2;
                box-shadow: none;
            }

            .btn-submit:hover {
                background: #166fe5;
            }

            .social-btn {
                min-height: 44px;
                border-radius: 6px;
            }

            .terms-checkbox {
                font-size: 0.6875rem;
                padding: 0.5rem 0;
                margin-bottom: 0.5rem;
            }

            .divider {
                margin: 0.75rem 0;
            }
        }

        /* Mobile Responsive - General (≤768px) - Facebook-style */
        @media (max-width: 768px) {
            body {
                padding: 0;
                background: #f0f2f5;
            }

            .auth-container {
                padding: 1.5rem;
                margin: 0;
                border-radius: 0;
                width: 100%;
                max-width: 100%;
            }

            .auth-header {
                margin-bottom: 1.5rem;
                padding-bottom: 0;
                border-bottom: none;
            }

            .auth-header h1 {
                font-size: 1.875rem;
            }

            .auth-logo img {
                height: 70px;
            }

            /* Keep name fields side by side on mobile */
            .form-row {
                grid-template-columns: 1fr 1fr !important;
                gap: 0.5rem !important;
            }

            .form-group {
                margin-bottom: 0.625rem;
            }

            .auth-container {
                padding: 1rem;
            }

            .auth-header {
                margin-bottom: 1rem;
            }

            .auth-header h1 {
                font-size: 1.625rem;
            }

            .auth-logo img {
                height: 55px;
            }

            .form-input {
                padding: 0.75rem;
                font-size: 0.9375rem;
                min-height: 40px;
            }

            .btn-submit {
                padding: 0.625rem;
                font-size: 1rem;
                min-height: 44px;
            }

            .terms-checkbox {
                font-size: 0.6875rem;
                padding: 0.5rem 0;
                margin-bottom: 0.5rem;
            }

            .divider {
                margin: 0.75rem 0;
            }

            .social-login {
                grid-template-columns: 1fr;
                margin-bottom: 0.75rem;
            }

            .social-btn {
                min-height: 44px;
            }

            .auth-footer {
                margin-top: 0.75rem;
                padding-top: 0.75rem;
            }

            .back-home {
                top: 0.5rem;
                left: 0.5rem;
            }

            /* Ensure all interactive elements are touch-friendly */
            .form-input,
            .btn-submit,
            .social-btn,
            .password-toggle {
                min-height: 40px;
            }

            /* Facebook-style clean design on mobile */
            .auth-container::before {
                display: none;
            }
        }

        /* Accessibility - Focus Visible Styles */
        .form-input:focus-visible,
        .btn-submit:focus-visible,
        .social-btn:focus-visible,
        .back-home a:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
        }

        .password-toggle:focus-visible {
            outline: 2px solid #3b82f6;
            border-radius: 4px;
        }

        .terms-checkbox a:focus-visible {
            outline: 2px solid #3b82f6;
            outline-offset: 1px;
        }

        /* Registration Type Modal */
        .registration-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 2rem;
        }

        .registration-modal.hidden {
            display: none;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 4rem;
            max-width: 1100px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 
                        0 0 0 1px rgba(59, 130, 246, 0.1),
                        inset 0 1px 0 rgba(255, 255, 255, 0.9);
            animation: modalSlideIn 0.3s ease-out;
            border: 2px solid rgba(59, 130, 246, 0.15);
            border-top: 4px solid #3b82f6;
            position: relative;
            overflow: hidden;
        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6 0%, #1e40af 50%, #3b82f6 100%);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
            z-index: 1;
        }

        .modal-content::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 24px;
            padding: 2px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(30, 64, 175, 0.05));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        @keyframes shimmer {
            0%, 100% {
                background-position: 0% 0%;
            }
            50% {
                background-position: 100% 0%;
            }
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .modal-logo {
            margin-bottom: 2rem;
        }

        .modal-logo img {
            height: 130px;
            width: auto;
            max-width: 250px;
        }

        .modal-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        .modal-header p {
            color: #64748b;
            font-size: 1.15rem;
        }

        .registration-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 1.5rem;
            align-items: stretch;
        }

        .registration-option {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            padding: 2rem 2.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .registration-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #1e40af);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .registration-option:hover::before,
        .registration-option.selected::before {
            opacity: 1;
        }

        .registration-option:hover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.2);
        }

        .registration-option.selected {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
        }

        .registration-option.partner-option:hover {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.2);
        }

        .registration-option.partner-option.selected {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.25);
        }

        .option-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 20px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            transition: all 0.3s ease;
        }

        .registration-option:hover .option-icon {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .option-icon.client {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }

        .option-icon.partner {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        }

        .option-icon i {
            font-size: 3rem;
            color: #3b82f6;
        }

        .option-icon.partner i {
            color: #f59e0b;
        }

        .option-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
            line-height: 1.3;
        }

        .option-description {
            font-size: 1rem;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 1.5rem;
            flex-grow: 0;
        }

        .option-button {
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
            letter-spacing: 0.01em;
            margin-top: 0;
        }

        .option-button:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35);
        }

        .option-button:active {
            transform: translateY(0);
        }

        .option-button.partner {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        }

        .option-button.partner:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
        }

        .auth-container.hidden {
            display: none;
        }

        /* Partner Registration Orange Theme */
        .auth-container.partner-registration {
            border: 1px solid rgba(245, 158, 11, 0.25);
            box-shadow: 0 25px 80px rgba(245, 158, 11, 0.25), 
                        0 10px 30px rgba(245, 158, 11, 0.15),
                        0 0 0 1px rgba(245, 158, 11, 0.2),
                        inset 0 1px 0 rgba(255, 255, 255, 0.9);
            position: relative;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 251, 235, 0.95) 100%);
        }

        .auth-container.partner-registration::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b 0%, #d97706 50%, #f59e0b 100%);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
            border-radius: 36px 36px 0 0;
            z-index: 1;
        }

        .auth-container.partner-registration .auth-header {
            border-bottom-color: rgba(251, 230, 138, 0.4);
        }

        .auth-container.partner-registration .auth-footer {
            border-top-color: rgba(251, 230, 138, 0.4);
        }

        .auth-container.partner-registration .auth-logo::after {
            background: linear-gradient(90deg, transparent, rgba(245, 158, 11, 0.2), transparent);
        }

        .auth-container.partner-registration .auth-header h1 {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .auth-container.partner-registration .form-group label {
            color: #b45309;
        }

        .auth-container.partner-registration .form-input {
            border-color: #e2e8f0;
        }

        .auth-container.partner-registration .form-input:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .auth-container.partner-registration .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12), 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .auth-container.partner-registration .btn-submit {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35), 0 2px 4px rgba(245, 158, 11, 0.2);
        }

        .auth-container.partner-registration .btn-submit:hover {
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.45), 0 4px 8px rgba(245, 158, 11, 0.3);
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        }

        .auth-container.partner-registration .terms-checkbox a {
            color: #d97706;
        }

        .auth-container.partner-registration .terms-checkbox a:hover {
            color: #b45309;
        }

        .auth-container.partner-registration .social-btn:hover {
            border-color: #f59e0b;
            background: #fffbeb;
        }

        .auth-container.partner-registration .auth-footer a {
            color: #d97706;
        }

        .auth-container.partner-registration .auth-footer a:hover {
            color: #b45309;
        }

        .auth-container.partner-registration .password-toggle {
            color: #d97706;
        }

        .auth-container.partner-registration .divider span {
            color: #d97706;
        }

        .auth-container.partner-registration .divider::before,
        .auth-container.partner-registration .divider::after {
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        /* Partner Registration Orange Theme - Focus Visible Styles */
        .auth-container.partner-registration .form-input:focus-visible {
            outline: 3px solid #3b82f6;
        }
        
        .auth-container.partner-registration .btn-submit:focus-visible,
        .auth-container.partner-registration .social-btn:focus-visible {
            outline: 3px solid #f59e0b;
        }

        .auth-container.partner-registration .password-toggle:focus-visible {
            outline: 2px solid #f59e0b;
        }

        .auth-container.partner-registration .terms-checkbox a:focus-visible {
            outline: 2px solid #f59e0b;
        }

        .auth-container.partner-registration a[style*="color: #3b82f6"] {
            color: #d97706 !important;
        }

        .auth-container.partner-registration .zip-location-display {
            color: #000000;
        }

        .auth-container.partner-registration .password-strength-fill.good {
            background-color: #d97706;
        }

        .auth-container.partner-registration .password-strength-text.good {
            color: #d97706;
        }

        /* Legal Modals (Terms & Privacy) */
        .legal-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 2rem;
            overflow-y: auto;
        }

        .legal-modal.hidden {
            display: none;
        }

        .legal-modal-content {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 800px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        .legal-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            border-bottom: 2px solid #e2e8f0;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            border-radius: 16px 16px 0 0;
        }

        .legal-modal-header h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .legal-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.25rem;
            transition: background 0.2s;
        }

        .legal-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .legal-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        .legal-content {
            font-size: 0.95rem;
            line-height: 1.8;
            color: #475569;
        }

        .legal-content h3 {
            font-size: 1.25rem;
            color: #1e293b;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
        }

        .legal-content h4 {
            font-size: 1.1rem;
            color: #334155;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .legal-content p {
            margin-bottom: 1rem;
        }

        .legal-content ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }

        .legal-content li {
            margin-bottom: 0.5rem;
        }

        .legal-content strong {
            color: #1e293b;
            font-weight: 600;
        }

        .legal-content a {
            color: #3b82f6;
            text-decoration: underline;
        }

        .legal-modal-footer {
            padding: 1.5rem 2rem;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
        }

        .legal-modal-footer .btn-submit {
            padding: 0.75rem 2rem;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .legal-modal {
                padding: 1rem;
            }

            .legal-modal-content {
                max-height: 95vh;
            }

            .legal-modal-header,
            .legal-modal-body,
            .legal-modal-footer {
                padding: 1rem 1.5rem;
            }

            .legal-modal-header h2 {
                font-size: 1.25rem;
            }
        }

        /* Partner Type Selection Modal */
        .partner-type-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001;
            padding: 2rem;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .partner-type-modal.hidden {
            display: none;
        }

        .partner-type-content {
            background: white;
            border-radius: 24px;
            padding: 4rem;
            max-width: 1100px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 
                        0 0 0 1px rgba(245, 158, 11, 0.1),
                        inset 0 1px 0 rgba(255, 255, 255, 0.9);
            animation: modalSlideIn 0.3s ease-out;
            border: 2px solid rgba(245, 158, 11, 0.15);
            border-top: 4px solid #f59e0b;
            position: relative;
            overflow: hidden;
        }

        .partner-type-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b 0%, #d97706 50%, #f59e0b 100%);
            background-size: 200% 100%;
            animation: shimmer 3s ease-in-out infinite;
            z-index: 1;
        }

        .partner-type-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .partner-type-header h2 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }

        .partner-type-header p {
            color: #64748b;
            font-size: 1.15rem;
        }

        .partner-type-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .partner-type-option {
            background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
            border: 2px solid #fde68a;
            border-radius: 16px;
            padding: 2rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
            align-items: stretch;
        }

        .partner-type-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f59e0b, #d97706);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .partner-type-option:hover::before,
        .partner-type-option.selected::before {
            opacity: 1;
        }

        .partner-type-option:hover {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(245, 158, 11, 0.2);
        }

        .partner-type-option.selected {
            border-color: #f59e0b;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.25);
        }

        .partner-type-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.15);
            transition: all 0.3s ease;
        }

        .partner-type-option:hover .partner-type-icon {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.2);
        }

        .partner-type-icon i {
            font-size: 2.5rem;
            color: #f59e0b;
            display: inline-block;
        }

        .partner-type-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: auto;
            letter-spacing: -0.01em;
            flex-grow: 1;
        }

        .partner-type-button {
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
            margin-top: auto;
        }

        .partner-type-button:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
        }

        .back-button {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            background: rgba(245, 158, 11, 0.1);
            border: 2px solid rgba(245, 158, 11, 0.3);
            color: #d97706;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 10;
        }

        .back-button:hover {
            background: rgba(245, 158, 11, 0.2);
            border-color: #f59e0b;
        }

        @media (max-width: 768px) {
            .partner-type-content {
                padding: 3rem 2rem 2rem;
            }

            .back-button {
                top: 1rem;
                left: 1rem;
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (min-width: 1200px) {
            .partner-type-options {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        @media (max-width: 1199px) and (min-width: 768px) {
            .partner-type-options {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 767px) {
            .partner-type-options {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Mobile Responsive Styles for Partner Type Modal */
        @media (max-width: 480px) {
            .partner-type-modal {
                padding: 0;
                align-items: flex-start;
                padding-top: 0;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                height: 100vh;
            }

            .partner-type-content {
                padding: 3rem 1.25rem 2rem;
                border-radius: 0;
                max-width: 100%;
                width: 100%;
                min-height: 100vh;
                height: auto;
                overflow: visible !important;
                position: relative;
                margin: 0;
            }

            .partner-type-header {
                margin-bottom: 2rem;
            }

            .partner-type-header .modal-logo {
                margin-bottom: 1.25rem;
            }

            .partner-type-header .modal-logo img {
                height: 70px;
                max-width: 140px;
            }

            .partner-type-header h2 {
                font-size: 1.75rem;
                margin-bottom: 0.5rem;
            }

            .partner-type-header p {
                font-size: 1rem;
            }

            .partner-type-options {
                grid-template-columns: 1fr;
                gap: 1rem;
                margin-bottom: 1.5rem;
            }

            .partner-type-option {
                padding: 1.5rem 1rem;
                border-radius: 12px;
            }

            .partner-type-icon {
                width: 60px;
                height: 60px;
                margin: 0 auto 1rem;
                border-radius: 12px;
            }

            .partner-type-icon i {
                font-size: 2rem;
            }

            .partner-type-title {
                font-size: 1.1rem;
                margin-bottom: 1rem;
            }

            .partner-type-button {
                padding: 0.875rem 1.25rem;
                font-size: 0.95rem;
                border-radius: 8px;
            }

            .back-button {
                top: 1rem;
                left: 1rem;
                padding: 0.5rem 0.875rem;
                font-size: 0.875rem;
                border-radius: 6px;
            }
        }

        @media (min-width: 481px) and (max-width: 767px) {
            .partner-type-content {
                padding: 2.5rem 1.75rem 2rem;
                border-radius: 20px;
            }

            .partner-type-header h2 {
                font-size: 2rem;
            }

            .partner-type-options {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }

            .partner-type-option {
                padding: 1.75rem 1.25rem;
            }

            .partner-type-icon {
                width: 70px;
                height: 70px;
            }

            .partner-type-icon i {
                font-size: 2.25rem;
            }
        }

        /* Mobile Responsive Styles for Registration Modal */
        @media (max-width: 480px) {
            .registration-modal {
                padding: 1rem;
                align-items: flex-start;
                padding-top: 2rem;
            }

            .modal-content {
                padding: 1.5rem 1.25rem;
                border-radius: 20px;
                max-width: 100%;
                margin: 0;
                max-height: calc(100vh - 2rem);
                overflow-y: auto;
            }

            .modal-header {
                margin-bottom: 2rem;
            }

            .modal-logo {
                margin-bottom: 1.5rem;
            }

            .modal-logo img {
                height: 90px;
                max-width: 180px;
            }

            .modal-header h2 {
                font-size: 1.5rem;
                line-height: 1.3;
                margin-bottom: 0.5rem;
            }

            .modal-header p {
                font-size: 0.95rem;
            }

            .registration-options {
                grid-template-columns: 1fr;
                gap: 1.25rem;
                margin-bottom: 1rem;
            }

            .registration-option {
                padding: 1.5rem 1.25rem;
                border-radius: 16px;
            }

            .option-icon {
                width: 70px;
                height: 70px;
                margin: 0 auto 1rem;
                border-radius: 16px;
            }

            .option-icon i {
                font-size: 2.25rem;
            }

            .option-title {
                font-size: 1.15rem;
                margin-bottom: 0.5rem;
                line-height: 1.3;
            }

            .option-description {
                font-size: 0.9rem;
                line-height: 1.4;
                margin-bottom: 1.25rem;
            }

            .option-button {
                padding: 0.875rem 1.25rem;
                font-size: 0.95rem;
                border-radius: 10px;
            }
        }

        @media (min-width: 481px) and (max-width: 768px) {
            .registration-modal {
                padding: 1.5rem;
            }

            .modal-content {
                padding: 2.5rem 2rem;
                border-radius: 22px;
            }

            .modal-logo img {
                height: 110px;
                max-width: 220px;
            }

            .modal-header h2 {
                font-size: 2rem;
            }

            .modal-header p {
                font-size: 1.05rem;
            }

            .registration-options {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .registration-option {
                padding: 2rem 1.75rem;
            }

            .option-icon {
                width: 85px;
                height: 85px;
                margin: 0 auto 1.25rem;
            }

            .option-icon i {
                font-size: 2.75rem;
            }

            .option-title {
                font-size: 1.35rem;
                margin-bottom: 0.625rem;
            }

            .option-description {
                font-size: 0.975rem;
                margin-bottom: 1.25rem;
            }

            .option-button {
                padding: 0.95rem 1.35rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 768px) {
            .registration-options {
                grid-template-columns: 1fr;
            }

            .modal-content {
                padding: 2rem 1.5rem;
            }

            .modal-header h2 {
                font-size: 1.75rem;
            }

            /* Prevent horizontal scroll on mobile */
            .registration-modal {
                overflow-x: hidden;
            }

            .modal-content {
                width: calc(100% - 2rem);
                max-width: 100%;
            }
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

    <!-- Registration Type Selection Modal -->
    <div class="registration-modal" id="registrationModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-logo">
                    <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo">
                </div>
                <h2>Let's get started. Choose an option:</h2>
            </div>
            <div class="registration-options">
                <div class="registration-option" data-type="client" onclick="selectRegistrationType('client')">
                    <div class="option-icon client">
                        <i class="bi bi-person-heart"></i>
                    </div>
                    <div class="option-title">I need a caregiver, housekeeping, or personal assistant</div>
                    <div class="option-description">Start your free search for care in your area.</div>
                    <button class="option-button" onclick="event.stopPropagation(); selectRegistrationType('client')">Find care</button>
                </div>
                <div class="registration-option partner-option" data-type="caregiver" onclick="showPartnerTypeModal()">
                    <div class="option-icon partner">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="option-title">I want to be a partner</div>
                    <div class="option-description">Join our network of independent caregivers, housekeeping, personal assistants, marketing partners, and training centers.</div>
                    <button class="option-button partner" onclick="event.stopPropagation(); showPartnerTypeModal()">Become a partner</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Partner Type Selection Modal -->
    <div class="partner-type-modal hidden" id="partnerTypeModal">
        <div class="partner-type-content">
            <button class="back-button" onclick="hidePartnerTypeModal()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <div class="partner-type-header">
                <div class="modal-logo">
                    <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo">
                </div>
                <h2>What type of partner are you?</h2>
                <p>Select your partner type to get started</p>
            </div>
            <div class="partner-type-options">
                <div class="partner-type-option" data-partner-type="caregiver" onclick="selectPartnerType('caregiver')">
                    <div class="partner-type-icon">
                        <i class="bi bi-person-heart"></i>
                    </div>
                    <div class="partner-type-title">Caregiver</div>
                    <button class="partner-type-button" onclick="event.stopPropagation(); selectPartnerType('caregiver')">Select</button>
                </div>
                <div class="partner-type-option" data-partner-type="housekeeping" onclick="selectPartnerType('housekeeping')">
                    <div class="partner-type-icon">
                        <i class="bi bi-house-door"></i>
                    </div>
                    <div class="partner-type-title">Housekeeping</div>
                    <button class="partner-type-button" onclick="event.stopPropagation(); selectPartnerType('housekeeping')">Select</button>
                </div>
                <div class="partner-type-option" data-partner-type="personal_assistant" onclick="selectPartnerType('personal_assistant')">
                    <div class="partner-type-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="partner-type-title">Personal Assistant</div>
                    <button class="partner-type-button" onclick="event.stopPropagation(); selectPartnerType('personal_assistant')">Select</button>
                </div>
                <div class="partner-type-option" data-partner-type="marketing_partner" onclick="selectPartnerType('marketing_partner')">
                    <div class="partner-type-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="partner-type-title">Marketing Partner</div>
                    <button class="partner-type-button" onclick="event.stopPropagation(); selectPartnerType('marketing_partner')">Select</button>
                </div>
                <div class="partner-type-option" data-partner-type="training_center" onclick="selectPartnerType('training_center')">
                    <div class="partner-type-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="partner-type-title">Training Center</div>
                    <button class="partner-type-button" onclick="event.stopPropagation(); selectPartnerType('training_center')">Select</button>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-container hidden" id="authContainer">
        <div class="auth-logo">
            <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care LLC Logo">
        </div>

        <div class="auth-header">
            <h1 id="registrationHeader">Create Account</h1>
            <p id="registrationSubtitle">Join CAS Private Care LLC and start your journey</p>
            <a href="#" onclick="event.preventDefault(); showRegistrationModal();" style="color: #3b82f6; text-decoration: none; font-size: 0.9rem; margin-top: 0.5rem; display: inline-block;">Change selection</a>
            @if(session('oauth_success'))
                <div style="background: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 8px; margin-top: 1rem;">
                    {{ session('oauth_success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="error-message" style="background: #fee2e2; color: #dc2626; padding: 0.75rem; border-radius: 8px; margin-top: 1rem;">
                    {{ session('error') }}
                </div>
            @endif
        </div>



        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf
            <input type="hidden" name="user_type" id="userTypeInput" value="">
            <input type="hidden" name="partner_type" id="partnerTypeInput" value="">
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="form-input" placeholder="Enter your first name" value="{{ session('oauth_user.name') ? explode(' ', session('oauth_user.name'))[0] : old('first_name') }}" required autocomplete="given-name" aria-required="true" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                    @error('first_name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-input" placeholder="Enter your last name" value="{{ session('oauth_user.name') ? (explode(' ', session('oauth_user.name'), 2)[1] ?? '') : old('last_name') }}" required autocomplete="family-name" aria-required="true" pattern="[A-Za-z\s]+" title="Only letters and spaces are allowed" oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')">
                    @error('last_name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="your@email.com" value="{{ session('oauth_user.email') ?? old('email') }}" {{ session('oauth_user.email') ? 'readonly' : '' }} required autocomplete="email" aria-required="true">
                <div id="email-exists-warning" class="email-warning-message" style="display: none;">
                    <i class="bi bi-exclamation-triangle"></i>
                    This email address is already registered. Please use a different email or try logging in.
                </div>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="(646) 282-8282" value="{{ old('phone') }}" required autocomplete="tel" aria-required="true" maxlength="14">
                @error('phone')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="zip_code">ZIP Code</label>
                <input type="text" id="zip_code" name="zip_code" class="form-input" placeholder="Enter ZIP code" value="{{ old('zip_code') }}" required autocomplete="postal-code" aria-required="true" pattern="[0-9]{5}" maxlength="5" inputmode="numeric">
                <div id="zip-location-display" class="zip-location-display"></div>
                @error('zip_code')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            @if(!session('oauth_user'))
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-input" placeholder="Create a strong password" required autocomplete="new-password" aria-required="true">
                    <button type="button" class="password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                        <i class="bi bi-eye" id="toggleIcon1"></i>
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
                        <span class="requirement-icon">✗</span>
                        <span class="requirement-text">At least 8 characters</span>
                    </div>
                    <div class="requirement-item" id="req-uppercase">
                        <span class="requirement-icon">✗</span>
                        <span class="requirement-text">One capital letter</span>
                    </div>
                    <div class="requirement-item" id="req-digit">
                        <span class="requirement-icon">✗</span>
                        <span class="requirement-text">One digit</span>
                    </div>
                    <div class="requirement-item" id="req-special">
                        <span class="requirement-icon">✗</span>
                        <span class="requirement-text">One special character</span>
                    </div>
                </div>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Re-enter your password" required autocomplete="new-password" aria-required="true">
                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                        <i class="bi bi-eye" id="toggleIcon2"></i>
                    </button>
                </div>
                <div id="password-match" class="password-match"></div>
            </div>
            @endif

            <div class="terms-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    I agree to the <a href="#" onclick="event.preventDefault(); openTermsModal(); return false;">Terms of Service</a> and <a href="#" onclick="event.preventDefault(); openPrivacyModal(); return false;">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn-submit" onclick="return validateUserType()">Create Account</button>
        </form>

        <div class="divider">
            <span>or sign up with</span>
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
            
            @php
                $currentPartner = request()->query('partner');
                $oauthUrlParams = $currentPartner ? '?partner=' . $currentPartner : '';
            @endphp
            
            @if($googleEnabled)
            <a href="{{ url('/auth/google' . $oauthUrlParams) }}" class="social-btn" id="googleOAuthBtn">
                <i class="bi bi-google"></i>
                Google
            </a>
            @endif
            
            @if($facebookEnabled)
            <a href="{{ url('/auth/facebook' . $oauthUrlParams) }}" class="social-btn" id="facebookOAuthBtn">
                <i class="bi bi-facebook"></i>
                Facebook
            </a>
            @endif
        </div>

        <div class="auth-footer">
            Already have an account? <a href="{{ url('/login') }}">Login</a>
        </div>
    </div>

    <!-- Terms of Service Modal -->
    <div class="legal-modal hidden" id="termsModal">
        <div class="legal-modal-content">
            <div class="legal-modal-header">
                <h2>Terms of Service</h2>
                <button class="legal-modal-close" onclick="closeTermsModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="legal-modal-body">
                <div class="legal-content">
                    <p><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>
                    
                    <p>Welcome to CAS Private Care LLC ("we," "our," or "us"). These Terms of Service ("Terms") govern your access to and use of our website, mobile application, and services (collectively, the "Service") provided by CAS Private Care LLC, a platform connecting families and individuals with professional caregivers, housekeeping services, personal assistants, marketing partners, and training centers.</p>

                    <p>By accessing or using our Service, you agree to be bound by these Terms. If you disagree with any part of these Terms, you may not access the Service.</p>

                    <h3>1. Acceptance of Terms</h3>
                    <p>By creating an account, accessing, or using our Service, you acknowledge that you have read, understood, and agree to be bound by these Terms and our Privacy Policy.</p>

                    <h3>2. Description of Service</h3>
                    <p>CAS Private Care LLC operates an online marketplace platform that connects:</p>
                    <ul>
                        <li><strong>Clients:</strong> Individuals and families seeking caregiving, housekeeping, personal assistant, and related services</li>
                        <li><strong>Caregivers:</strong> Professional caregivers providing care services</li>
                        <li><strong>Housekeeping Providers:</strong> Professionals offering housekeeping and cleaning services</li>
                        <li><strong>Personal Assistants:</strong> Individuals providing personal assistance services</li>
                        <li><strong>Marketing Partners:</strong> Partners assisting with marketing and business development</li>
                        <li><strong>Training Centers:</strong> Organizations providing training and certification services</li>
                    </ul>

                    <h3>3. User Accounts and Registration</h3>
                    <h4>3.1 Account Creation</h4>
                    <p>To use certain features of our Service, you must register for an account. You agree to provide accurate, current, and complete information during registration and maintain the security of your account credentials.</p>

                    <h4>3.2 Account Approval (Service Providers)</h4>
                    <p>Service providers (caregivers, housekeeping providers, personal assistants, etc.) must undergo an approval process. We reserve the right to approve or reject any application at our sole discretion. Service providers will not be able to access the platform until their application has been approved by an administrator.</p>

                    <h3>4. User Responsibilities</h3>
                    <h4>4.1 Client Responsibilities</h4>
                    <ul>
                        <li>Provide accurate information about service needs and requirements</li>
                        <li>Communicate clearly and respectfully with service providers</li>
                        <li>Make timely payments for services rendered</li>
                        <li>Provide a safe working environment for service providers</li>
                    </ul>

                    <h4>4.2 Service Provider Responsibilities</h4>
                    <ul>
                        <li>Provide accurate profile information, qualifications, and credentials</li>
                        <li>Maintain professional standards and conduct</li>
                        <li>Complete services as agreed upon with clients</li>
                        <li>Comply with all applicable laws, regulations, and professional standards</li>
                        <li>Obtain and maintain all necessary licenses, certifications, and insurance</li>
                    </ul>

                    <h3>5. Service Provider Classifications</h3>
                    <p>Service providers on our platform are classified as independent contractors (1099 contractors), not employees of CAS Private Care LLC. Service providers are responsible for their own taxes, insurance, and compliance with applicable laws.</p>

                    <h3>6. Payments and Fees</h3>
                    <p>Payments for services are processed through our secure payment system. Clients agree to pay all fees associated with booked services. Service providers will receive payouts according to our payment schedule and terms.</p>

                    <h3>7. Intellectual Property</h3>
                    <p>The Service and its original content, features, and functionality are owned by CAS Private Care LLC and are protected by international copyright, trademark, patent, trade secret, and other intellectual property laws.</p>

                    <h3>8. Disclaimers and Limitation of Liability</h3>
                    <p>THE SERVICE IS PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND. TO THE MAXIMUM EXTENT PERMITTED BY LAW, CAS PRIVATE CARE LLC SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES.</p>

                    <h3>9. Termination</h3>
                    <p>We may suspend or terminate your account and access to the Service immediately, without prior notice, for any reason, including if you breach these Terms or engage in fraudulent or illegal activity.</p>

                    <h3>10. Governing Law</h3>
                    <p>These Terms shall be governed by and construed in accordance with the laws of the State of New York, United States.</p>

                    <h3>11. Contact Information</h3>
                    <p>If you have any questions about these Terms of Service, please contact us:</p>
                    <p><strong>CAS Private Care LLC</strong><br>
                    Email: <a href="mailto:contact@casprivatecare.online">contact@casprivatecare.online</a><br>
                    Phone: <a href="tel:+16462828282">+1 (646) 282-8282</a><br>
                    Address: New York, USA</p>
                </div>
            </div>
            <div class="legal-modal-footer">
                <button class="btn-submit" onclick="closeTermsModal()">I Understand</button>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div class="legal-modal hidden" id="privacyModal">
        <div class="legal-modal-content">
            <div class="legal-modal-header">
                <h2>Privacy Policy</h2>
                <button class="legal-modal-close" onclick="closePrivacyModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="legal-modal-body">
                <div class="legal-content">
                    <p><strong>Last Updated:</strong> {{ date('F j, Y') }}</p>
                    
                    <p>At CAS Private Care LLC ("we," "our," or "us"), we are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website, mobile application, and services.</p>

                    <h3>1. Information We Collect</h3>
                    <h4>1.1 Information You Provide to Us</h4>
                    <ul>
                        <li><strong>Account Information:</strong> Name, email address, phone number, password, ZIP code, date of birth, address, and other profile information</li>
                        <li><strong>Profile Information:</strong> Emergency contacts, medical conditions (for clients), qualifications and certifications (for service providers), training certificates, bio, years of experience</li>
                        <li><strong>Booking Information:</strong> Service preferences, scheduling information, special requirements, and payment information</li>
                        <li><strong>Documents:</strong> Training certificates, identification documents, background check documents</li>
                    </ul>

                    <h4>1.2 Information We Collect Automatically</h4>
                    <ul>
                        <li>Usage data, device information, IP address, browser type, location data, and log data</li>
                        <li>Information collected through cookies and tracking technologies</li>
                    </ul>

                    <h3>2. How We Use Your Information</h3>
                    <ul>
                        <li>Creating and managing your account</li>
                        <li>Facilitating connections between clients and service providers</li>
                        <li>Processing bookings and payments</li>
                        <li>Providing customer support</li>
                        <li>Improving and optimizing our Service</li>
                        <li>Sending service-related notifications and updates</li>
                        <li>Ensuring safety and security</li>
                        <li>Complying with legal obligations</li>
                    </ul>

                    <h3>3. How We Share Your Information</h3>
                    <h4>3.1 With Other Users</h4>
                    <p>To facilitate service connections, we share certain information between clients and service providers, such as names, service requirements, qualifications, and booking information.</p>

                    <h4>3.2 With Service Providers</h4>
                    <p>We may share information with third-party service providers who perform services on our behalf, including payment processing, cloud storage, email services, and analytics services.</p>

                    <h4>3.3 For Legal Reasons</h4>
                    <p>We may disclose your information if required by law, to enforce our Terms of Service, or to protect our rights and safety.</p>

                    <h3>4. Cookies and Tracking Technologies</h3>
                    <p>We use cookies and similar tracking technologies to collect and store information about your preferences and activity on our Service. You can control cookies through your browser settings.</p>

                    <h3>5. Data Security</h3>
                    <p>We implement appropriate technical and organizational measures to protect your personal information, including encryption, secure servers, access controls, and regular security assessments. However, no method of transmission over the Internet is 100% secure.</p>

                    <h3>6. Data Retention</h3>
                    <p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer retention period is required by law.</p>

                    <h3>7. Your Privacy Rights</h3>
                    <p>Depending on your location, you may have rights to access, correct, delete, or restrict processing of your personal information. You may also have the right to object to processing and to withdraw consent where applicable.</p>

                    <h3>8. Children's Privacy</h3>
                    <p>Our Service is not intended for individuals under the age of 18. We do not knowingly collect personal information from children.</p>

                    <h3>9. Changes to This Privacy Policy</h3>
                    <p>We may update this Privacy Policy from time to time. We will notify you of material changes by posting the updated policy and updating the "Last Updated" date.</p>

                    <h3>10. Contact Us</h3>
                    <p>If you have any questions about this Privacy Policy, please contact us:</p>
                    <p><strong>CAS Private Care LLC</strong><br>
                    Email: <a href="mailto:hello@casprivatecare.com">hello@casprivatecare.com</a><br>
                    Phone: <a href="tel:+16462828282">+1 (646) 282-8282</a><br>
                    Address: New York, USA</p>
                </div>
            </div>
            <div class="legal-modal-footer">
                <button class="btn-submit" onclick="closePrivacyModal()">I Understand</button>
            </div>
        </div>
    </div>

    <script>
        // Check if user type is already set (e.g., from URL parameter or session)
        const urlParams = new URLSearchParams(window.location.search);
        const presetType = urlParams.get('type') || '{{ old("user_type", "") }}';
        const presetPartner = urlParams.get('partner') || '';
        const showPartnerTypes = urlParams.get('show_partner_types') === 'true';
        
        // Check if OAuth user exists (means returning from OAuth)
        const hasOAuthUser = {{ session('oauth_user') ? 'true' : 'false' }};
        console.log('Page load - hasOAuthUser:', hasOAuthUser, 'presetPartner:', presetPartner, 'showPartnerTypes:', showPartnerTypes);
        
        // Map partner types to display names
        const partnerTypeNames = {
            'caregiver': 'Caregiver',
            'housekeeping': 'Housekeeping',
            'personal_assistant': 'Personal Assistant',
            'marketing_partner': 'Marketing Partner',
            'training_center': 'Training Center'
        };
        
        // Valid partner types
        const validPartnerTypes = ['caregiver', 'housekeeping', 'personal_assistant', 'marketing_partner', 'training_center'];
        
        // Function to show partner registration form
        function showPartnerRegistrationForm(partnerType) {
            document.getElementById('registrationModal').classList.add('hidden');
            document.getElementById('partnerTypeModal').classList.add('hidden');
            const authContainer = document.getElementById('authContainer');
            authContainer.classList.remove('hidden');
            authContainer.classList.add('partner-registration');
            
            // Set form values
            document.getElementById('userTypeInput').value = 'caregiver';
            document.getElementById('partnerTypeInput').value = partnerType;
            
            // Update URL to include partner type
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('partner', partnerType);
            window.history.pushState({}, '', newUrl);
            
            // Update header text
            const header = document.getElementById('registrationHeader');
            const subtitle = document.getElementById('registrationSubtitle');
            header.textContent = `Become a ${partnerTypeNames[partnerType]}`;
            subtitle.textContent = 'Join our network of independent care partners';
            
            // Update OAuth button URLs
            updateOAuthButtonUrls(partnerType);
        }
        
        function updateOAuthButtonUrls(partnerType) {
            const googleBtn = document.getElementById('googleOAuthBtn') || document.querySelector('a[href*="/auth/google"]');
            const facebookBtn = document.getElementById('facebookOAuthBtn') || document.querySelector('a[href*="/auth/facebook"]');
            
            if (googleBtn) {
                try {
                    const url = new URL(googleBtn.href);
                    url.searchParams.set('partner', partnerType);
                    googleBtn.href = url.toString();
                    console.log('Updated Google OAuth URL:', googleBtn.href);
                } catch (e) {
                    const baseUrl = googleBtn.href.split('?')[0];
                    googleBtn.href = baseUrl + '?partner=' + encodeURIComponent(partnerType);
                }
            }
            
            if (facebookBtn) {
                try {
                    const url = new URL(facebookBtn.href);
                    url.searchParams.set('partner', partnerType);
                    facebookBtn.href = url.toString();
                    console.log('Updated Facebook OAuth URL:', facebookBtn.href);
                } catch (e) {
                    const baseUrl = facebookBtn.href.split('?')[0];
                    facebookBtn.href = baseUrl + '?partner=' + encodeURIComponent(partnerType);
                }
            }
        }
        
        // If partner type is specified in URL, skip modals and go directly to partner registration
        if (presetPartner && validPartnerTypes.includes(presetPartner)) {
            showPartnerRegistrationForm(presetPartner);
        }
        // If show_partner_types parameter is set, show partner type selection modal directly
        else if (showPartnerTypes) {
            console.log('show_partner_types parameter detected - showing partner type selection');
            document.getElementById('registrationModal').classList.add('hidden');
            document.getElementById('partnerTypeModal').classList.remove('hidden');
        }
        // If OAuth user exists but no partner in URL, try to get from session or show partner selection
        // (This handles the case where OAuth returns but partner type wasn't preserved in URL)
        else if (hasOAuthUser && !presetPartner) {
            console.log('OAuth user detected but no partner type in URL - showing partner type selection');
            // Show partner type modal so user can select their type
            // The form will be shown after they select
            document.getElementById('registrationModal').classList.add('hidden');
            document.getElementById('partnerTypeModal').classList.remove('hidden');
        }
        // If type is preset (client or generic caregiver), skip modal
        else if (presetType && (presetType === 'client' || presetType === 'caregiver')) {
            document.getElementById('registrationModal').classList.add('hidden');
            document.getElementById('authContainer').classList.remove('hidden');
            document.getElementById('userTypeInput').value = presetType;
            
            // Update header text based on preset type
            const header = document.getElementById('registrationHeader');
            const subtitle = document.getElementById('registrationSubtitle');
            if (presetType === 'client') {
                header.textContent = 'Create Client Account';
                subtitle.textContent = 'Join CAS Private Care LLC and find quality caregivers';
            } else {
                header.textContent = 'Become a Care Partner';
                subtitle.textContent = 'Join our network of independent care partners';
            }
        }

        function selectRegistrationType(type) {
            // Set the user type
            document.getElementById('userTypeInput').value = type;
            
            // Update selected state
            document.querySelectorAll('.registration-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`.registration-option[data-type="${type}"]`).classList.add('selected');
            
            // Hide modal and show form after a brief delay for animation
            setTimeout(() => {
                document.getElementById('registrationModal').classList.add('hidden');
                const authContainer = document.getElementById('authContainer');
                authContainer.classList.remove('hidden');
                // Ensure partner-registration class is removed for client registration
                authContainer.classList.remove('partner-registration');
                
                // Update header text based on selection
                const header = document.getElementById('registrationHeader');
                const subtitle = document.getElementById('registrationSubtitle');
                if (type === 'client') {
                    header.textContent = 'Create Client Account';
                    subtitle.textContent = 'Join CAS Private Care LLC and find quality caregivers';
                } else {
                    header.textContent = 'Become a Care Partner';
                    subtitle.textContent = 'Join our network of independent care partners';
                }
            }, 300);
        }

        function showPartnerTypeModal() {
            document.getElementById('registrationModal').classList.add('hidden');
            document.getElementById('partnerTypeModal').classList.remove('hidden');
        }

        function hidePartnerTypeModal() {
            document.getElementById('partnerTypeModal').classList.add('hidden');
            document.getElementById('registrationModal').classList.remove('hidden');
        }

        function selectPartnerType(partnerType) {
            // Set user type to caregiver (all partners are caregivers in the system)
            document.getElementById('userTypeInput').value = 'caregiver';
            document.getElementById('partnerTypeInput').value = partnerType;
            
            // Update URL to include partner type so OAuth can preserve it
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('partner', partnerType);
            window.history.pushState({}, '', newUrl);
            
            // Update selected state
            document.querySelectorAll('.partner-type-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`.partner-type-option[data-partner-type="${partnerType}"]`).classList.add('selected');
            
            // Hide partner type modal and show form after a brief delay
            setTimeout(() => {
                document.getElementById('partnerTypeModal').classList.add('hidden');
                const authContainer = document.getElementById('authContainer');
                authContainer.classList.remove('hidden');
                // Add partner-registration class for orange theme
                authContainer.classList.add('partner-registration');
                
                // Update header text
                const header = document.getElementById('registrationHeader');
                const subtitle = document.getElementById('registrationSubtitle');
                header.textContent = `Become a ${partnerTypeNames[partnerType]}`;
                subtitle.textContent = 'Join our network of independent care partners';
                
                // Update OAuth button URLs to include partner type
                updateOAuthButtonUrls(partnerType);
            }, 300);
        }
        
        function updateOAuthButtonUrls(partnerType) {
            const googleBtn = document.getElementById('googleOAuthBtn') || document.querySelector('a[href*="/auth/google"]');
            const facebookBtn = document.getElementById('facebookOAuthBtn') || document.querySelector('a[href*="/auth/facebook"]');
            
            if (googleBtn) {
                try {
                    const url = new URL(googleBtn.href);
                    url.searchParams.set('partner', partnerType);
                    googleBtn.href = url.toString();
                    console.log('Updated Google OAuth URL:', googleBtn.href);
                } catch (e) {
                    const baseUrl = googleBtn.href.split('?')[0];
                    googleBtn.href = baseUrl + '?partner=' + encodeURIComponent(partnerType);
                }
            }
            
            if (facebookBtn) {
                try {
                    const url = new URL(facebookBtn.href);
                    url.searchParams.set('partner', partnerType);
                    facebookBtn.href = url.toString();
                    console.log('Updated Facebook OAuth URL:', facebookBtn.href);
                } catch (e) {
                    const baseUrl = facebookBtn.href.split('?')[0];
                    facebookBtn.href = baseUrl + '?partner=' + encodeURIComponent(partnerType);
                }
            }
        }
        
        // Add click handlers to OAuth buttons to ensure partner type is captured
        document.addEventListener('DOMContentLoaded', function() {
            const googleBtn = document.getElementById('googleOAuthBtn');
            const facebookBtn = document.getElementById('facebookOAuthBtn');
            
            function handleOAuthClick(e, provider) {
                const partnerType = document.getElementById('partnerTypeInput')?.value || new URLSearchParams(window.location.search).get('partner');
                if (partnerType) {
                    const url = new URL(e.currentTarget.href);
                    url.searchParams.set('partner', partnerType);
                    e.currentTarget.href = url.toString();
                    console.log(`${provider} OAuth click - partner type:`, partnerType);
                }
            }
            
            if (googleBtn) {
                googleBtn.addEventListener('click', (e) => handleOAuthClick(e, 'Google'));
            }
            
            if (facebookBtn) {
                facebookBtn.addEventListener('click', (e) => handleOAuthClick(e, 'Facebook'));
            }
        });

        function showRegistrationModal() {
            document.getElementById('registrationModal').classList.remove('hidden');
            document.getElementById('partnerTypeModal').classList.add('hidden');
            const authContainer = document.getElementById('authContainer');
            authContainer.classList.add('hidden');
            // Remove partner-registration class when going back
            authContainer.classList.remove('partner-registration');
            // Reset selection
            document.querySelectorAll('.registration-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelectorAll('.partner-type-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.getElementById('userTypeInput').value = '';
            document.getElementById('partnerTypeInput').value = '';
        }

        function validateUserType() {
            const userType = document.getElementById('userTypeInput').value;
            if (!userType) {
                alert('Please select whether you need a caregiver or want to be a care partner.');
                showRegistrationModal();
                return false;
            }
            // If user is a caregiver (partner), check if partner type is selected
            if (userType === 'caregiver') {
                const partnerType = document.getElementById('partnerTypeInput').value;
                if (!partnerType) {
                    alert('Please select your partner type.');
                    showPartnerTypeModal();
                    return false;
                }
            }
            return true;
        }

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

        // ZIP Code input restrictions - only allow numbers, max 5 digits, and lookup location
        document.addEventListener('DOMContentLoaded', function() {
            const zipCodeInput = document.getElementById('zip_code');
            const zipLocationDisplay = document.getElementById('zip-location-display');
            
            // ZIP code lookup function
            async function lookupZipCodeLocation(zip) {
                if (!zipLocationDisplay) return;
                
                if (zip && zip.length === 5 && /^\d{5}$/.test(zip)) {
                    try {
                        const response = await fetch(`/api/zipcode-lookup/${zip}`);
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.location) {
                                zipLocationDisplay.textContent = data.location;
                                zipLocationDisplay.style.display = 'block';
                                return;
                            }
                        }
                    } catch (error) {
                        console.error('ZIP code lookup failed:', error);
                    }
                    
                    // Hide display if lookup fails
                    zipLocationDisplay.style.display = 'none';
                    zipLocationDisplay.textContent = '';
                } else {
                    // Hide display if ZIP code is invalid
                    zipLocationDisplay.style.display = 'none';
                    zipLocationDisplay.textContent = '';
                }
            }
            
            if (zipCodeInput) {
                zipCodeInput.addEventListener('input', function(e) {
                    // Remove any non-digit characters
                    let value = this.value.replace(/\D/g, '');
                    // Limit to 5 digits
                    if (value.length > 5) {
                        value = value.slice(0, 5);
                    }
                    this.value = value;
                    
                    // Lookup ZIP code location when 5 digits are entered
                    if (value.length === 5) {
                        lookupZipCodeLocation(value);
                    } else {
                        if (zipLocationDisplay) {
                            zipLocationDisplay.style.display = 'none';
                            zipLocationDisplay.textContent = '';
                        }
                    }
                });

                zipCodeInput.addEventListener('blur', function(e) {
                    // Lookup ZIP code location on blur as well
                    if (this.value.length === 5) {
                        lookupZipCodeLocation(this.value);
                    }
                });

                zipCodeInput.addEventListener('keypress', function(e) {
                    // Only allow digits
                    if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                        e.preventDefault();
                    }
                });
                
                // Lookup ZIP code on page load if there's an old value
                if (zipCodeInput.value && zipCodeInput.value.length === 5) {
                    lookupZipCodeLocation(zipCodeInput.value);
                }
            }

            // Phone number formatting - US format (XXX) XXX-XXXX
            const phoneInput = document.getElementById('phone');
            if (phoneInput) {
                // Format phone number on input
                phoneInput.addEventListener('input', function(e) {
                    // Remove all non-numeric characters
                    let value = this.value.replace(/\D/g, '');
                    
                    // Limit to 10 digits
                    if (value.length > 10) {
                        value = value.slice(0, 10);
                    }
                    
                    // Format as (XXX) XXX-XXXX
                    if (value.length === 0) {
                        this.value = '';
                    } else if (value.length <= 3) {
                        this.value = `(${value}`;
                    } else if (value.length <= 6) {
                        this.value = `(${value.slice(0, 3)}) ${value.slice(3)}`;
                    } else {
                        this.value = `(${value.slice(0, 3)}) ${value.slice(3, 6)}-${value.slice(6, 10)}`;
                    }
                });

                // Only allow digits, backspace, delete, tab, arrow keys
                phoneInput.addEventListener('keypress', function(e) {
                    if (!/[0-9]/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                        e.preventDefault();
                    }
                });

                // Format existing value on page load
                if (phoneInput.value) {
                    phoneInput.dispatchEvent(new Event('input'));
                }
            }

            // Email existence check
            const emailInput = document.getElementById('email');
            const emailWarning = document.getElementById('email-exists-warning');
            let emailCheckTimeout = null;

            if (emailInput && emailWarning && !emailInput.readOnly) {
                emailInput.addEventListener('blur', function() {
                    checkEmailExists(this.value);
                });

                emailInput.addEventListener('input', function() {
                    // Clear warning when user starts typing
                    emailWarning.style.display = 'none';
                    emailInput.style.borderColor = '';

                    // Debounce the email check
                    clearTimeout(emailCheckTimeout);
                    const email = this.value.trim();
                    
                    if (email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        emailCheckTimeout = setTimeout(() => {
                            checkEmailExists(email);
                        }, 500); // Wait 500ms after user stops typing
                    }
                });
            }

            async function checkEmailExists(email) {
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    return;
                }

                try {
                    const response = await fetch(`/api/check-email-exists/${encodeURIComponent(email)}`);
                    if (response.ok) {
                        const data = await response.json();
                        if (data.exists) {
                            emailWarning.style.display = 'flex';
                            emailInput.style.borderColor = '#f59e0b';
                        } else {
                            emailWarning.style.display = 'none';
                            emailInput.style.borderColor = '';
                        }
                    }
                } catch (error) {
                    console.error('Email check failed:', error);
                }
            }

            // Password strength indicator
            const passwordInput = document.getElementById('password');
            const passwordStrengthFill = document.getElementById('password-strength-fill');
            const passwordStrengthText = document.getElementById('password-strength-text');
            const passwordConfirmInput = document.getElementById('password_confirmation');
            const passwordMatchDiv = document.getElementById('password-match');

            if (passwordInput && passwordStrengthFill && passwordStrengthText) {
                passwordInput.addEventListener('input', function() {
                    checkPasswordStrength(this.value);
                    checkPasswordMatch();
                });
            }

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
        });

        // Legal Modal Functions
        function openTermsModal() {
            document.getElementById('termsModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeTermsModal() {
            document.getElementById('termsModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openPrivacyModal() {
            document.getElementById('privacyModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePrivacyModal() {
            document.getElementById('privacyModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            const termsModal = document.getElementById('termsModal');
            const privacyModal = document.getElementById('privacyModal');
            
            if (event.target === termsModal) {
                closeTermsModal();
            }
            if (event.target === privacyModal) {
                closePrivacyModal();
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeTermsModal();
                closePrivacyModal();
            }
        });
    </script>
</body>
</html>
