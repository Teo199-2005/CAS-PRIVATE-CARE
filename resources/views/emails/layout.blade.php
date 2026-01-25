<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CAS Private Care')</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f4f0;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            padding: 30px 40px;
            text-align: center;
        }
        .logo-container {
            margin-bottom: 10px;
        }
        .logo-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: white;
            padding: 5px;
        }
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin: 10px 0 0 0;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .logo-tagline {
            font-size: 12px;
            color: rgba(255,255,255,0.9);
            margin: 5px 0 0 0;
            letter-spacing: 1px;
        }
        .content {
            padding: 40px;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #f97316;
            color: #ffffff !important;
            text-decoration: none !important;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            border: none;
            box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);
            transition: all 0.2s ease;
        }
        .button:hover {
            background-color: #ea580c;
            box-shadow: 0 4px 6px rgba(249, 115, 22, 0.4);
        }
        .button:visited {
            color: #ffffff !important;
        }
        a.button {
            color: #ffffff !important;
        }
        .footer {
            background-color: #1e293b;
            padding: 30px 40px;
            text-align: center;
            color: #94a3b8;
        }
        .footer p {
            margin: 5px 0;
            font-size: 12px;
        }
        .footer a {
            color: #f97316;
            text-decoration: none;
        }
        .footer-logo {
            margin-bottom: 15px;
        }
        .footer-logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #94a3b8;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 25px 0;
        }
        .highlight {
            background-color: #fff7ed;
            padding: 15px 20px;
            border-left: 4px solid #f97316;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .contact-info {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 15px 20px;
            margin-top: 25px;
            text-align: center;
        }
        .contact-info p {
            margin: 5px 0;
            font-size: 13px;
            color: #64748b;
        }
        .contact-info a {
            color: #f97316;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header with Logo -->
        <div class="header">
            <div class="logo-container">
                <img src="https://casprivatecare.online/logo%20flower.png" alt="CAS Private Care" class="logo-img" style="width: 60px; height: 60px; border-radius: 50%; background-color: white; padding: 5px;">
            </div>
            <div class="logo-text">CAS Private Care</div>
            <div class="logo-tagline">COMPASSIONATE CARE, TRUSTED SERVICE</div>
        </div>
        
        <!-- Content -->
        <div class="content">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-logo">
                <img src="https://casprivatecare.online/logo%20flower.png" alt="CAS" style="width: 40px; height: 40px; border-radius: 50%;">
            </div>
            <p style="font-size: 14px; color: #ffffff; margin-bottom: 10px;"><strong>CAS Private Care LLC</strong></p>
            <p>New York, NY</p>
            <p>
                <a href="tel:+16462828282">(646) 282-8282</a> | 
                <a href="mailto:casprivatecare@casprivatecare.com">casprivatecare@casprivatecare.com</a>
            </p>
            <div class="divider" style="background-color: #334155; margin: 20px 0;"></div>
            <p style="font-size: 11px;">
                This email was sent by CAS Private Care LLC.<br>
                If you did not expect this email, please ignore it or <a href="mailto:support@casprivatecare.com">contact support</a>.
            </p>
            <p style="font-size: 11px; margin-top: 15px;">
                &copy; {{ date('Y') }} CAS Private Care LLC. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>




