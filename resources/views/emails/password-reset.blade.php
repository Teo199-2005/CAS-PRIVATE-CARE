@extends('emails.layout')

@section('title', 'Reset Your Password')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 28px; font-weight: bold; color: white;">&#128274;</div>
        <h2 style="color: #1e293b; margin: 10px 0;">Reset Your Password</h2>
    </div>
    
    <p style="font-size: 16px;">Hello {{ $userName }},</p>
    
    <p style="font-size: 16px;">We received a request to reset your password for your CAS Private Care account.</p>
    
    <p style="font-size: 16px;">Click the button below to reset your password:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">Reset Password</a>
    </div>
    
    <p style="font-size: 14px; color: #64748b;">Or copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #f97316; font-size: 13px; background: #fff7ed; padding: 12px; border-radius: 8px;">{{ $resetUrl }}</p>
    
    <div class="highlight">
        <strong>Important:</strong> This link will expire in 60 minutes. If you did not request a password reset, please ignore this email and your password will remain unchanged.
    </div>
    
    <p style="font-size: 14px; color: #64748b;">If you continue to have problems, please contact our support team.</p>
    
    <p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection




