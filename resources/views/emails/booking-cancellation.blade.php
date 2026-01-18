@extends('emails.layout')

@section('title', 'Booking Cancellation Notice')

@section('content')
<div style="text-align: center; margin-bottom: 25px;">
    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 56px; font-size: 28px; font-weight: bold; color: white; border: 3px solid rgba(255,255,255,0.4);">X</div>
    <h2 style="color: #1e293b; margin: 10px 0;">Booking Cancellation Notice</h2>
</div>

<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 20px; margin-bottom: 25px; text-align: center;">
    <p style="margin: 0; font-size: 16px; color: #991b1b;">
        A booking you were assigned to has been cancelled.
    </p>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">Hi {{ $contractor->name }},</p>

<p style="font-size: 16px; margin-bottom: 25px;">
    We're writing to inform you that the following booking has been cancelled. We apologize for any inconvenience this may cause.
</p>

<!-- Cancelled Booking Details -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 18px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        Cancelled Booking Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; color: #64748b; width: 40%;">Booking ID:</td>
            <td style="padding: 10px 0; font-weight: 600; color: #1e293b;">#{{ $booking->id }}</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Original Date:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ \Carbon\Carbon::parse($booking->service_date)->format('l, F j, Y') }}</td>
        </tr>
        @if($booking->start_time)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Scheduled Time:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Service Type:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $booking->service_type }}</td>
        </tr>
        @if($booking->address || $booking->city)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Location:</td>
            <td style="padding: 10px 0; font-weight: 500;">
                {{ $booking->address ?? '' }}
                @if($booking->city), {{ $booking->city }}@endif
                @if($booking->state) {{ $booking->state }}@endif
            </td>
        </tr>
        @endif
    </table>
</div>

<!-- Cancellation Reason -->
@if($cancellationReason)
<div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
    <h4 style="margin: 0 0 10px 0; color: #92400e; font-size: 15px;">Cancellation Reason:</h4>
    <p style="margin: 0; color: #78350f; font-size: 14px;">{{ $cancellationReason }}</p>
</div>
@endif

<!-- What Happens Next -->
<div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
    <h4 style="margin: 0 0 15px 0; color: #166534; font-size: 16px;">What Happens Next?</h4>
    <ul style="margin: 0; padding-left: 20px; color: #166534; font-size: 14px;">
        <li style="margin-bottom: 8px;">This booking has been removed from your schedule.</li>
        <li style="margin-bottom: 8px;">You are now available for other assignments during this time slot.</li>
        <li style="margin-bottom: 0;">If applicable, any partial compensation will be processed according to our policy.</li>
    </ul>
</div>

<!-- Action Buttons -->
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/login') }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">View Your Schedule</a>
</div>

<p style="font-size: 14px; color: #64748b; margin-top: 20px;">
    If you have any questions about this cancellation, please contact us at 
    <a href="mailto:support@casprivatecare.com" style="color: #2563eb;">support@casprivatecare.com</a>.
</p>

<p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection
