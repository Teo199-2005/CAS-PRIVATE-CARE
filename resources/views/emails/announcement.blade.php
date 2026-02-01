@extends('emails.layout')

@section('title', 'Announcement')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 28px; font-weight: bold; color: white;">!</div>
        <h2 style="color: #1e293b; margin: 10px 0;">{{ $title }}</h2>
    </div>
    
    <p style="font-size: 16px;">Hello,</p>
    
    <div style="background-color: @if($type === 'success') #f0fdf4 @elseif($type === 'warning') #fffbeb @elseif($type === 'error') #fef2f2 @else #fff7ed @endif; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid @if($type === 'success') #22c55e @elseif($type === 'warning') #f59e0b @elseif($type === 'error') #ef4444 @else #f97316 @endif;">
        <p style="margin: 0; font-size: 16px;">{!! nl2br(e($message)) !!}</p>
    </div>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/login') }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">Go to Dashboard</a>
    </div>
    
    <p style="font-size: 16px;">For more information, please log in to your dashboard or contact our support team.</p>
    
    <p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection






