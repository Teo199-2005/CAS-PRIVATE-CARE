<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1E3A5F">
    <title>Offline - CAS Private Care</title>
    <style>
        :root {
            --primary: #1E3A5F;
            --primary-light: #2C5282;
            --accent: #38A169;
            --text: #2D3748;
            --text-light: #718096;
            --bg: #F7FAFC;
            --white: #FFFFFF;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
        }
        
        .container {
            max-width: 500px;
            background: var(--white);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .icon svg {
            width: 40px;
            height: 40px;
            fill: var(--white);
        }
        
        h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
        }
        
        p {
            font-size: 16px;
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .btn {
            display: inline-block;
            background: var(--accent);
            color: var(--white);
            padding: 14px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            background: #2F855A;
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .status {
            margin-top: 24px;
            padding: 12px 16px;
            background: #FFF5F5;
            border-radius: 8px;
            color: #C53030;
            font-size: 14px;
        }
        
        .status.online {
            background: #F0FFF4;
            color: var(--accent);
        }
        
        .logo {
            width: 120px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 480px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 20px;
            }
            
            p {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.64 7c-.45-.34-4.93-4-11.64-4C5.28 3 .81 6.66.36 7l10.08 12.56c.8 1 2.32 1 3.12 0L23.64 7zM1.82 8.77l9.04 11.25c.63.79 1.65.79 2.28 0l9.04-11.25c-1.85-1.26-5.15-3.02-10.18-3.02s-8.33 1.76-10.18 3.02z"/>
                <path d="M12 6c4.03 0 7.34 1.48 9.04 2.61L12 19.5 2.96 8.61C4.66 7.48 7.97 6 12 6m0-2C5.28 4 .81 7.66.36 8l10.08 12.56c.4.5.92.69 1.44.69h.24c.52 0 1.04-.19 1.44-.69L23.64 8c-.45-.34-4.93-4-11.64-4z"/>
                <line x1="2" y1="2" x2="22" y2="22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        
        <h1>You're Offline</h1>
        
        <p>
            It looks like you've lost your internet connection. 
            Please check your connection and try again.
        </p>
        
        <button class="btn" onclick="location.reload()">
            Try Again
        </button>
        
        <div class="status" id="status">
            Checking connection...
        </div>
    </div>
    
    <script>
        function updateStatus() {
            const statusEl = document.getElementById('status');
            
            if (navigator.onLine) {
                statusEl.textContent = 'Connection restored! Click "Try Again" to continue.';
                statusEl.className = 'status online';
            } else {
                statusEl.textContent = 'No internet connection detected.';
                statusEl.className = 'status';
            }
        }
        
        updateStatus();
        
        window.addEventListener('online', () => {
            updateStatus();
            // Auto-reload after short delay when back online
            setTimeout(() => {
                location.reload();
            }, 1500);
        });
        
        window.addEventListener('offline', updateStatus);
        
        // Periodic check
        setInterval(updateStatus, 5000);
    </script>
</body>
</html>
