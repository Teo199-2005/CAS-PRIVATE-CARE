<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <title>Server Error - CAS Private Care LLC</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            color: #0B4FA2;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        .error-logo {
            width: 120px;
            height: auto;
            margin-bottom: 2rem;
        }
        .error-icon {
            font-size: 5rem;
            color: #dc2626;
            margin-bottom: 1rem;
        }
        .error-code {
            font-size: 4rem;
            font-weight: 700;
            color: #dc2626;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1e293b;
        }
        .error-message {
            font-size: 1.1rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 1.75rem;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0B4FA2 0%, #1e40af 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(11, 79, 162, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(11, 79, 162, 0.4);
        }
        .btn-secondary {
            background: white;
            color: #0B4FA2;
            border: 2px solid #e2e8f0;
        }
        .btn-secondary:hover {
            border-color: #0B4FA2;
            background: #f8fafc;
        }
        .support-info {
            margin-top: 3rem;
            padding: 1.5rem;
            background: white;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .support-info h3 {
            font-size: 1rem;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }
        .support-info p {
            color: #64748b;
            font-size: 0.9rem;
        }
        .support-info a {
            color: #0B4FA2;
            font-weight: 600;
        }
        @media (max-width: 480px) {
            .error-code {
                font-size: 3rem;
            }
            .error-icon {
                font-size: 3.5rem;
            }
            .error-title {
                font-size: 1.5rem;
            }
            .error-actions {
                flex-direction: column;
            }
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="{{ asset('logo flower.png') }}" alt="CAS Private Care" class="error-logo">
        <i class="bi bi-exclamation-triangle-fill error-icon"></i>
        <div class="error-code">500</div>
        <h1 class="error-title">Server Error</h1>
        <p class="error-message">
            We're sorry, something went wrong on our end. Our team has been notified 
            and is working to fix the issue. Please try again in a few moments.
        </p>
        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="bi bi-house-fill"></i>
                Go Home
            </a>
            <a href="javascript:location.reload()" class="btn btn-secondary">
                <i class="bi bi-arrow-clockwise"></i>
                Try Again
            </a>
        </div>
        <div class="support-info">
            <h3><i class="bi bi-headset"></i> Need Immediate Assistance?</h3>
            <p>Call us at <a href="tel:+16462828282">+1 (646) 282-8282</a> or email <a href="mailto:support@casprivatecare.com">support@casprivatecare.com</a></p>
        </div>
    </div>
</body>
</html>
