<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('logo flower.png') }}">
    <title>Page Not Found - CAS Private Care LLC</title>
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
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
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
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: #0B4FA2;
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
        .helpful-links {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        .helpful-links h3 {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 1rem;
        }
        .helpful-links ul {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .helpful-links a {
            color: #0B4FA2;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .helpful-links a:hover {
            color: #fbbf24;
        }
        @media (max-width: 480px) {
            .error-code {
                font-size: 5rem;
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
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            Oops! The page you're looking for doesn't exist or has been moved. 
            Don't worry, our caring team is still here to help you.
        </p>
        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">
                <i class="bi bi-house-fill"></i>
                Go Home
            </a>
            <a href="{{ url('/contact') }}" class="btn btn-secondary">
                <i class="bi bi-chat-dots-fill"></i>
                Contact Us
            </a>
        </div>
        <div class="helpful-links">
            <h3>Helpful Links</h3>
            <ul>
                <li><a href="{{ url('/services') }}">Our Services</a></li>
                <li><a href="{{ url('/book') }}">Book a Service</a></li>
                <li><a href="{{ url('/blog') }}">Blog</a></li>
                <li><a href="{{ url('/faq') }}">FAQ</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
