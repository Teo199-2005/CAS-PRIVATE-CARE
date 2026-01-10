<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'CAS Private Care'); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #2563eb;
            color: #ffffff !important;
            text-decoration: none !important;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            border: none;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }
        .button:hover {
            background-color: #1d4ed8;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.4);
        }
        .button:visited {
            color: #ffffff !important;
        }
        a.button {
            color: #ffffff !important;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .highlight {
            background-color: #fef3c7;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">CAS Private Care</div>
        </div>
        
        <div class="content">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
        
        <div class="footer">
            <p>This email was sent by CAS Private Care LLC.</p>
            <p>If you did not expect this email, please ignore it or contact our support team.</p>
            <p>&copy; <?php echo e(date('Y')); ?> CAS Private Care. All rights reserved.</p>
        </div>
    </div>
</body>
</html>


<?php /**PATH C:\Users\Cocotantan\Downloads\--CAS WEBSITE-- - Copy (4)\resources\views/emails/layout.blade.php ENDPATH**/ ?>