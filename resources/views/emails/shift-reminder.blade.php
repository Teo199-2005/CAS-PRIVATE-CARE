@extends('emails.layout')

@section('title', 'Shift Reminder')

@section('content')
<div style="text-align: center; margin-bottom: 25px;">
    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 56px; font-size: 28px; font-weight: bold; color: white; border: 3px solid rgba(255,255,255,0.4);">&#9201;</div>
    <h2 style="color: #1e293b; margin: 10px 0;">Shift Reminder</h2>
</div>

<div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 1px solid #fbbf24; border-radius: 12px; padding: 20px; margin-bottom: 25px; text-align: center;">
    <p style="margin: 0; font-size: 18px; font-weight: 600; color: #92400e;">
        @if($hoursUntilShift <= 24)
            Your shift is tomorrow!
        @else
            Your shift is in {{ $hoursUntilShift }} hours
        @endif
    </p>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">Hi {{ $contractor->name }},</p>

<p style="font-size: 16px; margin-bottom: 25px;">
    This is a friendly reminder about your upcoming shift. Please review the details below.
</p>

<!-- Shift Details Card -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 18px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        Shift Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; color: #64748b; width: 40%;">Date:</td>
            <td style="padding: 10px 0; font-weight: 600; color: #1e293b;">{{ \Carbon\Carbon::parse($booking->service_date)->format('l, F j, Y') }}</td>
        </tr>
        @if($booking->start_time)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Start Time:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Service Type:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $booking->service_type }}</td>
        </tr>
        @if($booking->hours_per_day)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Duration:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $booking->hours_per_day }} hours</td>
        </tr>
        @endif
    </table>
</div>

<!-- Client Info -->
@if($client)
<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
    <h4 style="margin: 0 0 15px 0; color: #1e40af; font-size: 16px;">Client Information</h4>
    <p style="margin: 0 0 8px 0;"><strong>Name:</strong> {{ $client->name }}</p>
    @if($booking->address || $booking->city)
    <p style="margin: 0 0 8px 0;"><strong>Location:</strong> 
        {{ $booking->address ?? '' }}
        @if($booking->city), {{ $booking->city }}@endif
        @if($booking->state) {{ $booking->state }}@endif
    </p>
    @endif
    @if($client->phone)
    <p style="margin: 0;"><strong>Phone:</strong> {{ $client->phone }}</p>
    @endif
</div>
@endif

<!-- Reminder Tips -->
<div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
    <p style="margin: 0; font-size: 14px; color: #166534;">
        <strong>Reminder:</strong> Please arrive 5-10 minutes early and remember to clock in when you start your shift.
    </p>
</div>

<!-- Action Button -->
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/login') }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">View Shift Details</a>
</div>

<p style="font-size: 14px; color: #64748b; margin-top: 20px;">
    Need to make changes? Contact us at 
    <a href="mailto:support@casprivatecare.com" style="color: #2563eb;">support@casprivatecare.com</a>.
</p>

<p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection
