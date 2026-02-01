@extends('emails.layout')

@section('title', 'Application Approved')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 32px; font-weight: bold; color: white;">&#10003;</div>
        <h2 style="color: #10b981; margin: 10px 0;">Application Approved!</h2>
    </div>
    
    <p style="font-size: 16px;">Hello {{ $user->name }},</p>
    
    <p style="font-size: 16px;">Congratulations! Your application to become a contractor with CAS Private Care has been approved.</p>
    
    <div style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border: 1px solid #6ee7b7; border-radius: 12px; padding: 20px; margin: 25px 0; text-align: center;">
        <strong style="color: #065f46; font-size: 16px;">Your account is now active!</strong><br>
        <span style="color: #047857; font-size: 14px;">You can now log in to your dashboard and start using the platform.</span>
    </div>
    
    <p style="font-size: 16px; font-weight: 600; color: #1e293b;">Next steps:</p>
    <ul style="font-size: 15px; line-height: 1.8;">
        <li>Log in to your account using your registered email and password</li>
        <li>Complete your profile to increase your visibility</li>
        <li>Start receiving assignments and bookings</li>
    </ul>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/login') }}" class="button" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none !important; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">Log In to Dashboard</a>
    </div>
    
    <p style="font-size: 14px; color: #64748b;">If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
    
    <p style="font-size: 16px; margin-top: 25px;">Welcome aboard!<br><strong>The CAS Private Care Team</strong></p>
@endsection






