@extends('emails.layout')

@section('title', 'Verify Your Email')

@section('content')
    <h2>Verify Your Email Address</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Thank you for registering with CAS Private Care! Please verify your email address by clicking the button below:</p>
    
    <div style="text-align: center;">
        <a href="{{ $verificationUrl }}" class="button">Verify Email Address</a>
    </div>
    
    <p>Or copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #2563eb;">{{ $verificationUrl }}</p>
    
    <p>This link will expire in 24 hours.</p>
    
    <p>If you did not create an account with CAS Private Care, please ignore this email.</p>
    
    <p>Best regards,<br>The CAS Private Care Team</p>
@endsection


