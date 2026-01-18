@extends('emails.layout')

@section('title', 'Payment Issue')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 15px; line-height: 56px; font-size: 36px; font-weight: bold;">!</div>
        <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Payment Issue</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Action required to receive your payment</p>
    </div>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">Hi {{ $user->name }},</p>

<p style="font-size: 16px; margin-bottom: 25px;">
    We were unable to process your payment of <strong style="color: #ef4444; font-size: 20px;">${{ number_format($amount, 2) }}</strong>. 
    Don't worry – your earnings are safe and will be paid once the issue is resolved.
</p>

<!-- Issue Details Card -->
<div style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 15px 0; color: #991b1b; font-size: 16px;">
        What Happened
    </h3>
    <p style="margin: 0; color: #7f1d1d; font-size: 14px;">
        {{ $reason }}
    </p>
</div>

<!-- Action Required Card -->
<div style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 15px 0; color: #92400e; font-size: 16px;">
        What You Need To Do
    </h3>
    <p style="margin: 0; color: #78350f; font-size: 14px;">
        {{ $actionRequired }}
    </p>
</div>

<!-- Common Solutions -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 15px 0; color: #1e293b; font-size: 16px;">
        Common Solutions
    </h3>
    <ul style="margin: 0; padding-left: 20px; color: #475569; font-size: 14px;">
        <li style="margin-bottom: 8px;">Verify your bank account number and routing number are correct</li>
        <li style="margin-bottom: 8px;">Ensure your bank account is active and can receive deposits</li>
        <li style="margin-bottom: 8px;">Check that your name matches exactly with your bank records</li>
        <li style="margin-bottom: 8px;">Contact your bank to ensure there are no restrictions on your account</li>
    </ul>
</div>

<!-- Dashboard Button -->
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/login') }}" class="button" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none !important; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">
        Update Payment Details
    </a>
</div>

<p style="font-size: 14px; color: #64748b; margin-top: 20px;">
    Need help? Contact our support team at 
    <a href="mailto:support@casprivatecare.com" style="color: #2563eb;">support@casprivatecare.com</a> 
    or call us at <a href="tel:+16462828282" style="color: #2563eb;">(646) 282-8282</a>.
</p>

<p style="font-size: 16px; margin-top: 25px;">
    We're here to help you get paid!
</p>
@endsection
