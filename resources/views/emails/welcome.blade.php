@extends('emails.layout')

@section('title', 'Welcome to CAS Private Care')

@section('content')
    <h2 style="color: #1e293b; margin-bottom: 20px;">Welcome to CAS Private Care</h2>
    
    <p style="font-size: 16px;">Hello {{ $user->name }},</p>
    
    <p style="font-size: 16px;">Thank you for joining CAS Private Care. We're excited to have you as part of our community!</p>
    
    @if($requiresApproval)
        <div class="highlight">
            <strong>Application Pending Approval</strong><br>
            Your account has been created successfully. Your application is currently pending approval by our administration team. You will receive an email notification once your application has been reviewed and approved.
        </div>
        
        <p style="font-size: 16px;">Once approved, you'll be able to:</p>
        <ul style="font-size: 15px; line-height: 1.8;">
            <li>Access your contractor dashboard</li>
            <li>Receive assignments and bookings</li>
            <li>Manage your profile and availability</li>
        </ul>
    @else
        <p style="font-size: 16px;">Your account has been successfully created! You can now:</p>
        <ul style="font-size: 15px; line-height: 1.8;">
            <li>Book caregiving services</li>
            <li>Browse available caregivers</li>
            <li>Manage your bookings and appointments</li>
        </ul>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/login') }}" class="button" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none !important; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">Log In to Your Dashboard</a>
        </div>
    @endif
    
    <p style="font-size: 14px; color: #64748b;">If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
    
    <p style="font-size: 16px; margin-top: 25px;">Welcome aboard!<br><strong>The CAS Private Care Team</strong></p>
@endsection




