@extends('emails.layout')

@section('title', 'Verify Your Email')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 28px; font-weight: bold; color: white;">@</div>
        <h2 style="color: #1e293b; margin: 10px 0;">Verify Your Email Address</h2>
    </div>
    
    <p style="font-size: 16px;">Hello {{ $user->name }},</p>
    
    <p style="font-size: 16px;">Thank you for registering with CAS Private Care! Please verify your email address by clicking the button below:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $verificationUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">Verify Email Address</a>
    </div>
    
    <p style="font-size: 14px; color: #64748b;">Or copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #f97316; font-size: 13px; background: #fff7ed; padding: 12px; border-radius: 8px;">{{ $verificationUrl }}</p>
    
    <div class="highlight">
        <strong>Note:</strong> This link will expire in 24 hours. If you did not create an account with CAS Private Care, please ignore this email.
    </div>
    
    <p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection


