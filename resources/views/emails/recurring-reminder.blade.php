<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contract Renewal Reminder</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: #ffffff; border-radius: 8px; padding: 40px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 24px; font-weight: bold; color: #2563eb; margin-bottom: 10px;">CAS Private Care</div>
        </div>
        
        <!-- Content -->
        <div style="margin-bottom: 30px;">
            <h2 style="color: #2563eb;">🔔 Contract Renewal Reminder</h2>
            
            <div style="background-color: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; border-radius: 6px; margin: 20px 0;">
                <p style="margin: 0; font-size: 18px; font-weight: 600; color: #1e40af;">
                    ⏰ {{ $days_until_renewal }} Day{{ $days_until_renewal > 1 ? 's' : '' }} Until Renewal
                </p>
            </div>
            
            <p>Hello {{ $client_name }},</p>
            
            <p>This is a friendly reminder that your recurring care service contract will automatically renew 
                <strong>{{ $days_until_renewal }} day{{ $days_until_renewal > 1 ? 's' : '' }} from now</strong> 
                on <strong>{{ $renewal_date }}</strong>.
            </p>

            <div style="background-color: #f9fafb; padding: 20px; border-radius: 6px; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #2563eb;">📋 Contract Details</h3>
                <p style="margin: 8px 0;"><strong>Booking ID:</strong> #{{ $booking_id }}</p>
                <p style="margin: 8px 0;"><strong>Service Type:</strong> {{ $service_type }}</p>
                <p style="margin: 8px 0;"><strong>Hours per Day:</strong> {{ $hours_per_day }} hours</p>
                <p style="margin: 8px 0;"><strong>Duration:</strong> {{ $duration_days }} days</p>
                <p style="margin: 8px 0;"><strong>Renewal Date:</strong> {{ $renewal_date }}</p>
                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 15px 0;">
                <p style="margin: 8px 0; font-size: 20px;"><strong>Amount to be Charged:</strong> <span style="color: #2563eb; font-weight: bold;">${{ $amount }}</span></p>
            </div>

            <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0;">
                <p style="margin: 0;"><strong style="color: #92400e;">⚠️ Auto-Renewal Enabled</strong></p>
                <p style="margin: 10px 0 0 0; color: #92400e;">
                    Your saved payment method will be automatically charged on {{ $renewal_date }}.
                    A new contract with the same schedule will begin automatically.
                </p>
            </div>

            <h3 style="color: #2563eb;">📅 What Happens Next?</h3>
            <ul style="line-height: 1.8;">
                <li>On <strong>{{ $renewal_date }}</strong>, your saved card will be charged <strong>${{ $amount }}</strong></li>
                <li>A new {{ $duration_days }}-day contract will begin with the same caregiver and schedule</li>
                <li>You'll receive a receipt confirmation email after the charge is processed</li>
                <li>Your care service continues without interruption</li>
            </ul>

            <div style="text-align: center; margin: 30px 0;">
                <p style="margin-bottom: 15px;">Need to make changes or cancel auto-renewal?</p>
                <a href="https://casprivatecare.online/login" style="display: inline-block; padding: 12px 30px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 6px; margin: 20px 0; font-weight: 600;">Manage Your Contract</a>
                <p style="font-size: 14px; color: #6b7280; margin-top: 15px;">
                    Go to Payment Information → Recurring Contracts to disable auto-renewal
                </p>
            </div>

            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

            <p>Best regards,<br>The CAS Private Care Team</p>
        </div>
        
        <!-- Footer -->
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-align: center;">
            <p>This email was sent by CAS Private Care LLC.</p>
            <p>If you did not expect this email, please ignore it or contact our support team.</p>
            <p>&copy; {{ date('Y') }} CAS Private Care. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
