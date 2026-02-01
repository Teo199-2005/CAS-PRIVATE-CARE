<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .tagline {
            font-size: 14px;
            color: #666;
            font-style: italic;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .otp-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 42px;
            font-weight: bold;
            text-align: center;
            padding: 30px;
            border-radius: 12px;
            letter-spacing: 8px;
            margin: 30px 0;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .warning-box {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">CAS PRIVATE CARE</div>
            <div class="tagline">Comfort & Support</div>
        </div>

        <h1>Verify Your Email Address</h1>
        
        <p>Hello {{ $user->name }},</p>
        
        <p>Thank you for joining CAS Private Care! To complete your registration and access your dashboard, please verify your email address using the code below:</p>
        
        <div class="otp-box">
            {{ $otp }}
        </div>
        
        <div class="info-box">
            <strong>📧 How to use this code:</strong>
            <ul style="margin: 10px 0 0 0;">
                <li>Enter this 6-digit code in the verification modal on your dashboard</li>
                <li>This code is valid for <strong>10 minutes</strong></li>
                <li>You can request a new code if this one expires</li>
            </ul>
        </div>
        
        <div class="warning-box">
            <strong>⚠️ Security Notice:</strong><br>
            If you didn't request this verification code, please ignore this email and contact our support team immediately at <a href="mailto:support@casprivatecare.com">support@casprivatecare.com</a>
        </div>
        
        <p style="margin-top: 30px;">
            If you have any questions or need assistance, our support team is here to help.
        </p>
        
        <p>Best regards,<br>
        <strong>The CAS Private Care Team</strong></p>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} CAS Private Care LLC. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 11px;">
                This is an automated email. Please do not reply to this message.<br>
                If you need assistance, contact us at support@casprivatecare.com
            </p>
        </div>
    </div>
</body>
</html>
