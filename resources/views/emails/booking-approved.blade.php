@extends('emails.layout')

@section('title', 'Booking Approved')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 32px; font-weight: bold; color: white;">&#10003;</div>
        <h2 style="color: #1e293b; margin: 10px 0;">Booking Approved!</h2>
    </div>
    
    <p style="font-size: 16px;">Hello {{ $clientName }},</p>
    
    <p style="font-size: 16px;">Great news! Your booking has been approved.</p>
    
    <div style="background-color: #f0fdf4; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #bbf7d0;">
        <h3 style="margin-top: 0; color: #166534;">Booking Details:</h3>
        <p style="margin: 8px 0;"><strong>Service Type:</strong> {{ $booking->service_type }}</p>
        <p style="margin: 8px 0;"><strong>Service Date:</strong> {{ \Carbon\Carbon::parse($booking->service_date)->format('F d, Y') }}</p>
        @if($booking->duty_type)
            <p style="margin: 8px 0;"><strong>Duty Type:</strong> {{ $booking->duty_type }}</p>
        @endif
        @if($booking->duration_days)
            <p style="margin: 8px 0;"><strong>Duration:</strong> {{ $booking->duration_days }} day(s)</p>
        @endif
        @if($booking->hourly_rate)
            <p style="margin: 8px 0;"><strong>Rate:</strong> ${{ number_format($booking->hourly_rate, 2) }}/hour</p>
        @endif
        @if($booking->city || $booking->county)
            <p style="margin: 8px 0;"><strong>Location:</strong> {{ $booking->city ?? '' }}{{ $booking->city && $booking->county ? ', ' : '' }}{{ $booking->county ?? '' }}</p>
        @endif
    </div>
    
    <p style="font-size: 16px;">Your booking is now active. We will assign caregivers to your booking soon, and you will be notified once they are assigned.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/login') }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">View Your Dashboard</a>
    </div>
    
    <p style="font-size: 16px;">If you have any questions or need to make changes, please contact our support team.</p>
    
    <p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection






