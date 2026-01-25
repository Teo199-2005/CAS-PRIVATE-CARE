<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Verification - CAS Private Care</title>
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0B4FA2 0%, #1565C0 50%, #1e40af 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .verify-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-section img {
            width: 64px;
            height: 64px;
        }
        
        .logo-section h1 {
            font-size: 1.5rem;
            color: #0B4FA2;
            margin-top: 1rem;
            font-weight: 700;
        }
        
        .shield-icon {
            font-size: 3rem;
            color: #0B4FA2;
            margin-bottom: 1rem;
        }
        
        h2 {
            font-size: 1.25rem;
            color: #1e293b;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .subtitle {
            color: #64748b;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }
        
        .email-display {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: #334155;
        }
        
        .otp-input-group {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .otp-input {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            outline: none;
            transition: all 0.2s;
        }
        
        .otp-input:focus {
            border-color: #0B4FA2;
            box-shadow: 0 0 0 3px rgba(11, 79, 162, 0.1);
        }
        
        .otp-input.error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0B4FA2, #1565C0);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(11, 79, 162, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: transparent;
            color: #0B4FA2;
            margin-top: 1rem;
        }
        
        .btn-secondary:hover {
            background: #f1f5f9;
        }
        
        .message {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            display: none;
            align-items: center;
            gap: 0.5rem;
        }
        
        .message.error {
            background: #fef2f2;
            color: #dc2626;
            display: flex;
        }
        
        .message.success {
            background: #f0fdf4;
            color: #16a34a;
            display: flex;
        }
        
        .timer {
            text-align: center;
            color: #64748b;
            font-size: 0.875rem;
            margin-top: 1rem;
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff40;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 480px) {
            .verify-card {
                padding: 1.5rem;
            }
            
            .otp-input {
                width: 42px;
                height: 50px;
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="logo-section">
            <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care">
            <h1>Admin Verification</h1>
        </div>
        
        <div class="text-center">
            <i class="bi bi-shield-lock shield-icon"></i>
        </div>
        
        <h2>Two-Factor Authentication</h2>
        <p class="subtitle">Enter the 6-digit code sent to your email</p>
        
        <div class="email-display">
            <i class="bi bi-envelope me-2"></i>
            {{ $email }}
        </div>
        
        <div id="message" class="message"></div>
        
        <div class="otp-input-group">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]*">
        </div>
        
        <button type="button" id="verifyBtn" class="btn btn-primary" onclick="verifyCode()">
            <i class="bi bi-check-circle"></i>
            Verify Code
        </button>
        
        <button type="button" id="resendBtn" class="btn btn-secondary" onclick="sendOTP()">
            <i class="bi bi-arrow-clockwise"></i>
            <span id="resendText">Send Code</span>
        </button>
        
        <div class="timer" id="timer" style="display: none;">
            Code expires in <span id="countdown">10:00</span>
        </div>
    </div>
    
    <script>
        const inputs = document.querySelectorAll('.otp-input');
        const verifyBtn = document.getElementById('verifyBtn');
        const resendBtn = document.getElementById('resendBtn');
        const message = document.getElementById('message');
        const timer = document.getElementById('timer');
        const countdown = document.getElementById('countdown');
        
        let countdownInterval;
        let expiryTime;
        
        // Auto-focus and move to next input
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateButtonState();
            });
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });
            
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const digits = paste.replace(/\D/g, '').slice(0, 6);
                digits.split('').forEach((digit, i) => {
                    if (inputs[i]) {
                        inputs[i].value = digit;
                    }
                });
                updateButtonState();
            });
        });
        
        function updateButtonState() {
            const code = getCode();
            verifyBtn.disabled = code.length !== 6;
        }
        
        function getCode() {
            return Array.from(inputs).map(i => i.value).join('');
        }
        
        function showMessage(text, type) {
            message.className = 'message ' + type;
            message.innerHTML = `<i class="bi bi-${type === 'error' ? 'x-circle' : 'check-circle'}"></i>${text}`;
            message.style.display = 'flex';
        }
        
        function startCountdown(seconds) {
            timer.style.display = 'block';
            expiryTime = Date.now() + (seconds * 1000);
            
            clearInterval(countdownInterval);
            countdownInterval = setInterval(() => {
                const remaining = Math.max(0, Math.floor((expiryTime - Date.now()) / 1000));
                const mins = Math.floor(remaining / 60);
                const secs = remaining % 60;
                countdown.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
                
                if (remaining <= 0) {
                    clearInterval(countdownInterval);
                    timer.style.display = 'none';
                    showMessage('Code expired. Please request a new one.', 'error');
                }
            }, 1000);
        }
        
        async function sendOTP() {
            resendBtn.disabled = true;
            document.getElementById('resendText').textContent = 'Sending...';
            
            try {
                const response = await fetch('/api/admin/2fa/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Verification code sent to your email.', 'success');
                    startCountdown(600); // 10 minutes
                    inputs[0].focus();
                } else {
                    showMessage(data.message || 'Failed to send code.', 'error');
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
            } finally {
                resendBtn.disabled = false;
                document.getElementById('resendText').textContent = 'Resend Code';
            }
        }
        
        async function verifyCode() {
            const code = getCode();
            if (code.length !== 6) return;
            
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<div class="spinner"></div> Verifying...';
            
            try {
                const response = await fetch('/api/admin/2fa/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ code }),
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showMessage('Verification successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || '/admin/dashboard-vue';
                    }, 1000);
                } else {
                    showMessage(data.message || 'Invalid code.', 'error');
                    inputs.forEach(i => i.classList.add('error'));
                    setTimeout(() => inputs.forEach(i => i.classList.remove('error')), 2000);
                }
            } catch (error) {
                showMessage('Network error. Please try again.', 'error');
            } finally {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="bi bi-check-circle"></i> Verify Code';
            }
        }
        
        // Send OTP on page load
        document.addEventListener('DOMContentLoaded', () => {
            sendOTP();
        });
    </script>
</body>
</html>
