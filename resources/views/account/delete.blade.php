<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.favicon')
    <title>Delete Account - CAS Private Care LLC</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #1e293b;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 2rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #1e40af;
        }

        .delete-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .delete-header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .delete-icon {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .delete-icon i {
            font-size: 2rem;
        }

        .delete-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .delete-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .delete-body {
            padding: 2rem;
        }

        .warning-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .warning-box h3 {
            color: #92400e;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .warning-box ul {
            color: #78350f;
            font-size: 0.9rem;
            padding-left: 1.5rem;
            margin: 0;
        }

        .warning-box li {
            margin-bottom: 0.5rem;
        }

        .warning-box li:last-child {
            margin-bottom: 0;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .info-box h3 {
            color: #1e40af;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .info-box p {
            color: #1e3a8a;
            font-size: 0.9rem;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        textarea.form-input {
            min-height: 100px;
            resize: vertical;
        }

        .form-hint {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.375rem;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .checkbox-group input {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            accent-color: #dc2626;
            cursor: pointer;
        }

        .checkbox-group label {
            font-size: 0.9rem;
            color: #475569;
            cursor: pointer;
        }

        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .error-message i {
            flex-shrink: 0;
            margin-top: 2px;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 2px solid transparent;
        }

        .btn:focus-visible {
            outline: 3px solid #3b82f6;
            outline-offset: 2px;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
            border-color: #e2e8f0;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
        }

        .btn-delete:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
        }

        .btn-delete:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .export-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }

        .export-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
        }

        .export-section p {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 1rem;
        }

        .btn-export {
            background: white;
            color: #3b82f6;
            border: 2px solid #3b82f6;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-export:hover {
            background: #eff6ff;
        }

        @media (max-width: 640px) {
            .container {
                padding: 1rem;
            }

            .delete-header {
                padding: 1.5rem;
            }

            .delete-body {
                padding: 1.5rem;
            }

            .btn-group {
                flex-direction: column;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .btn {
                transition: none;
            }
        }

        @media (prefers-contrast: high) {
            .form-input {
                border-width: 3px;
            }
            .btn {
                border-width: 3px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/profile') }}" class="back-link">
            <i class="bi bi-arrow-left"></i>
            Back to Profile
        </a>

        <div class="delete-card">
            <div class="delete-header">
                <div class="delete-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <h1>Delete Your Account</h1>
                <p>This action cannot be undone</p>
            </div>

            <div class="delete-body">
                @if($errors->any())
                    <div class="error-message" role="alert">
                        <i class="bi bi-exclamation-circle"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <p style="margin: 0;">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="warning-box">
                    <h3>
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        What happens when you delete your account:
                    </h3>
                    <ul>
                        <li>Your personal information will be permanently erased</li>
                        <li>Your booking history will be anonymized</li>
                        <li>You will lose access to all services</li>
                        <li>Any pending payments or refunds will still be processed</li>
                        <li>This action is <strong>irreversible</strong></li>
                    </ul>
                </div>

                <div class="info-box">
                    <h3>
                        <i class="bi bi-download"></i>
                        Download Your Data First
                    </h3>
                    <p>
                        Before deleting your account, you can download a copy of your data. 
                        This includes your profile information, booking history, and payment records.
                    </p>
                </div>

                <form action="{{ url('/account/export-data') }}" method="GET" style="margin-bottom: 1.5rem;">
                    @csrf
                    <button type="submit" class="btn-export">
                        <i class="bi bi-download"></i>
                        Download My Data
                    </button>
                </form>

                <form method="POST" action="{{ url('/account/delete') }}" id="deleteForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="password">Enter your password to confirm <span aria-hidden="true">*</span></label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               required
                               autocomplete="current-password"
                               aria-required="true">
                    </div>

                    <div class="form-group">
                        <label for="reason">Why are you leaving? (Optional)</label>
                        <textarea id="reason" 
                                  name="reason" 
                                  class="form-input" 
                                  placeholder="Your feedback helps us improve our services..."
                                  maxlength="1000"></textarea>
                        <p class="form-hint">This information is optional and will be kept confidential.</p>
                    </div>

                    <div class="form-group">
                        <label for="confirmation">Type <strong>DELETE</strong> to confirm <span aria-hidden="true">*</span></label>
                        <input type="text" 
                               id="confirmation" 
                               name="confirmation" 
                               class="form-input" 
                               required
                               autocomplete="off"
                               placeholder="Type DELETE here"
                               aria-required="true"
                               aria-describedby="confirmation-hint">
                        <p id="confirmation-hint" class="form-hint">This ensures you want to proceed with account deletion.</p>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" id="understand" name="understand" required aria-required="true">
                        <label for="understand">
                            I understand that deleting my account is permanent and all my data will be erased.
                        </label>
                    </div>

                    <div class="btn-group">
                        <a href="{{ url('/profile') }}" class="btn btn-cancel">
                            <i class="bi bi-x-lg"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-delete" id="deleteBtn" disabled>
                            <i class="bi bi-trash3"></i>
                            Delete My Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('deleteForm');
            const deleteBtn = document.getElementById('deleteBtn');
            const confirmInput = document.getElementById('confirmation');
            const understandCheckbox = document.getElementById('understand');
            const passwordInput = document.getElementById('password');

            function updateButtonState() {
                const isConfirmationValid = confirmInput.value.trim().toUpperCase() === 'DELETE';
                const isUnderstood = understandCheckbox.checked;
                const hasPassword = passwordInput.value.length > 0;

                deleteBtn.disabled = !(isConfirmationValid && isUnderstood && hasPassword);
            }

            confirmInput.addEventListener('input', updateButtonState);
            understandCheckbox.addEventListener('change', updateButtonState);
            passwordInput.addEventListener('input', updateButtonState);

            form.addEventListener('submit', function(e) {
                if (confirmInput.value.trim().toUpperCase() !== 'DELETE') {
                    e.preventDefault();
                    alert('Please type DELETE to confirm account deletion.');
                    return false;
                }

                if (!understandCheckbox.checked) {
                    e.preventDefault();
                    alert('Please confirm that you understand the consequences.');
                    return false;
                }

                return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');
            });
        });
    </script>
</body>
</html>
