<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Renewal Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: #ffffff; border-radius: 8px; padding: 40px;">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 24px; font-weight: bold; color: #2563eb;">CAS Private Care</div>
        </div>
        <h2 style="color: #2563eb;">Contract Renewal Reminder</h2>
        <div style="background-color: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 18px; font-weight: 600; color: #1e40af;">{{ $days_until_renewal }} Day{{ $days_until_renewal > 1 ? "s" : "" }} Until Renewal</p>
        </div>
        <p>Hello {{ $client_name }},</p>
        <p>This is a friendly reminder that your recurring care service contract will automatically renew <strong>{{ $days_until_renewal }} day{{ $days_until_renewal > 1 ? "s" : "" }} from now</strong> on <strong>{{ $renewal_date }}</strong>.</p>
        <div style="background-color: #f9fafb; padding: 20px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #2563eb;">Contract Details</h3>
            <p><strong>Booking ID:</strong> #{{ $booking_id }}</p>
            <p><strong>Service Type:</strong> {{ $service_type }}</p>
            <p><strong>Hours per Day:</strong> {{ $hours_per_day }} hours</p>
            <p><strong>Duration:</strong> {{ $duration_days }} days</p>
            <p><strong>Renewal Date:</strong> {{ $renewal_date }}</p>
            <hr>
            <p style="font-size: 20px;"><strong>Amount to be Charged:</strong> <span style="color: #2563eb; font-weight: bold;">${{ $amount }}</span></p>
        </div>
        <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0;">
            <p style="margin: 0;"><strong style="color: #92400e;">Auto-Renewal Enabled</strong></p>
            <p style="margin: 10px 0 0 0; color: #92400e;">Your saved payment method will be automatically charged on {{ $renewal_date }}.</p>
        </div>
        <h3 style="color: #2563eb;">What Happens Next?</h3>
        <ul>
            <li>Your saved card will be charged <strong>${{ $amount }}</strong></li>
            <li>A new {{ $duration_days }}-day contract will begin</li>
            <li>You will receive a receipt confirmation email</li>
        </ul>
        <div style="text-align: center; margin: 30px 0;">
            <p>Need to make changes or cancel auto-renewal?</p>
            <a href="https://casprivatecare.online/login" style="display: inline-block; padding: 12px 30px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600;">Manage Your Contract</a>
        </div>
        <p>Best regards,<br>The CAS Private Care Team</p>
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-align: center;">
            <p>This email was sent by CAS Private Care LLC.</p>
            <p>2026 CAS Private Care. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
