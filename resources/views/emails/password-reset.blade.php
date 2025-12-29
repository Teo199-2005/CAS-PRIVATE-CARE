@extends('emails.layout')

@section('title', 'Reset Your Password')

@section('content')
    <h2>Reset Your Password</h2>
    
    <p>Hello {{ $userName }},</p>
    
    <p>We received a request to reset your password for your CAS Private Care account.</p>
    
    <p>Click the button below to reset your password:</p>
    
    <div style="text-align: center;">
        <a href="{{ $resetUrl }}" class="button">Reset Password</a>
    </div>
    
    <p>Or copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #2563eb;">{{ $resetUrl }}</p>
    
    <div class="highlight">
        <strong>Important:</strong> This link will expire in 60 minutes. If you did not request a password reset, please ignore this email and your password will remain unchanged.
    </div>
    
    <p>If you continue to have problems, please contact our support team.</p>
    
    <p>Best regards,<br>The CAS Private Care Team</p>
@endsection


