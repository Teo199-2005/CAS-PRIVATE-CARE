@extends('emails.layout')

@section('title', 'Test Email')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 32px; font-weight: bold; color: white;">&#10003;</div>
        <h2 style="color: #1e293b; margin: 10px 0;">Test Email Successful!</h2>
    </div>
    
    <p style="font-size: 16px;">Hello,</p>

    <p style="font-size: 16px;">This is a test email from your CAS Private Care application.</p>

    <div style="background-color:#fff7ed;padding:20px;border-radius:8px;margin:20px 0;border-left:4px solid #f97316;">
        <div style="font-weight:700;margin-bottom:8px;"><span style="color:#1e293b;">Email Configuration Status:</span> <span style="font-weight:600;color:#22c55e;">Working correctly!</span></div>
        <div style="margin-bottom:6px;"><strong style="color:#475569;">Brevo SMTP:</strong> <span style="color:#1f2937;">Connected successfully</span></div>
        <div><strong style="color:#475569;">Timestamp:</strong> <span style="color:#1f2937;">{{ now()->format('F d, Y h:i A') }}</span></div>
    </div>

    <p style="font-size: 16px;">If you received this email, your email system is properly configured and ready to send:</p>
    <ul style="margin:15px 0;padding-left:20px;color:#333;line-height:1.8;">
        <li>Welcome emails</li>
        <li>Email verification</li>
        <li>Password reset emails</li>
        <li>Booking approval notifications</li>
        <li>Contractor approval notifications</li>
        <li>Payout confirmations</li>
        <li>Announcements</li>
    </ul>

    <p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection






