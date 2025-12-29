@extends('emails.layout')

@section('title', 'Booking Approved')

@section('content')
    <h2>Booking Approved!</h2>
    
    <p>Hello {{ $clientName }},</p>
    
    <p>Great news! Your booking has been approved.</p>
    
    <div style="background-color: #f9fafb; padding: 20px; border-radius: 6px; margin: 20px 0;">
        <h3 style="margin-top: 0;">Booking Details:</h3>
        <p><strong>Service Type:</strong> {{ $booking->service_type }}</p>
        <p><strong>Service Date:</strong> {{ \Carbon\Carbon::parse($booking->service_date)->format('F d, Y') }}</p>
        @if($booking->duty_type)
            <p><strong>Duty Type:</strong> {{ $booking->duty_type }}</p>
        @endif
        @if($booking->duration_days)
            <p><strong>Duration:</strong> {{ $booking->duration_days }} day(s)</p>
        @endif
        @if($booking->hourly_rate)
            <p><strong>Rate:</strong> ${{ number_format($booking->hourly_rate, 2) }}/hour</p>
        @endif
        @if($booking->city || $booking->county)
            <p><strong>Location:</strong> {{ $booking->city ?? '' }}{{ $booking->city && $booking->county ? ', ' : '' }}{{ $booking->county ?? '' }}</p>
        @endif
    </div>
    
    <p>Your booking is now active. We will assign caregivers to your booking soon, and you will be notified once they are assigned.</p>
    
    <p>You can view your booking details and status by logging into your dashboard.</p>
    
    <p>If you have any questions or need to make changes, please contact our support team.</p>
    
    <p>Best regards,<br>The CAS Private Care Team</p>
@endsection


