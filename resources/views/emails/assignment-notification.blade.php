@extends('emails.layout')

@section('title', 'New Assignment')

@section('content')
<div style="text-align: center; margin-bottom: 25px;">
    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 28px; font-weight: bold; color: white;">+</div>
    <h2 style="color: #1e293b; margin: 10px 0;">New Assignment</h2>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">Hi {{ $contractor->name }},</p>

<p style="font-size: 16px; margin-bottom: 25px;">
    Great news! You've been assigned to a new booking. Please review the details below.
</p>

<!-- Assignment Details Card -->
<div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 20px 0; color: #1e40af; font-size: 18px; border-bottom: 2px solid #bfdbfe; padding-bottom: 10px;">
        Assignment Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; color: #64748b; width: 40%;">Service Type:</td>
            <td style="padding: 10px 0; font-weight: 600; color: #1e40af;">{{ $booking->service_type }}</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Service Date:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ \Carbon\Carbon::parse($booking->service_date)->format('l, F j, Y') }}</td>
        </tr>
        @if($booking->start_time)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Start Time:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ \Carbon\Carbon::parse($booking->start_time)->format('g:i A') }}</td>
        </tr>
        @endif
        @if($booking->hours_per_day)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Hours per Day:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $booking->hours_per_day }} hours</td>
        </tr>
        @endif
        @if($booking->duration_days)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Duration:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $booking->duration_days }} day(s)</td>
        </tr>
        @endif
        @if($booking->duty_type)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Duty Type:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $booking->duty_type }}</td>
        </tr>
        @endif
    </table>
</div>

<!-- Client Info Card -->
@if($client)
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 18px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        Client Information
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; color: #64748b; width: 40%;">Client Name:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $client->name }}</td>
        </tr>
        @if($booking->address || $booking->city)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Location:</td>
            <td style="padding: 10px 0; font-weight: 500;">
                {{ $booking->address ?? '' }}
                @if($booking->city), {{ $booking->city }}@endif
                @if($booking->state) {{ $booking->state }}@endif
                @if($booking->zip_code) {{ $booking->zip_code }}@endif
            </td>
        </tr>
        @endif
        @if($client->phone)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Phone:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $client->phone }}</td>
        </tr>
        @endif
    </table>
</div>
@endif

<!-- Earnings Card -->
<div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #86efac; border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center;">
    <p style="margin: 0 0 5px 0; color: #166534; font-size: 14px;">Your Rate</p>
    <p style="margin: 0 0 10px 0; font-size: 28px; font-weight: 700; color: #15803d;">${{ number_format($hourlyRate, 2) }}/hour</p>
    @if($estimatedEarnings > 0)
    <p style="margin: 0; color: #166534; font-size: 14px;">Estimated Earnings: <strong>${{ number_format($estimatedEarnings, 2) }}</strong></p>
    @endif
</div>

<!-- Special Instructions -->
@if($booking->special_instructions || $booking->care_notes)
<div style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
    <h4 style="margin: 0 0 10px 0; color: #92400e; font-size: 14px;">Special Instructions</h4>
    <p style="margin: 0; color: #78350f; font-size: 14px;">
        {{ $booking->special_instructions ?? $booking->care_notes ?? 'No special instructions provided.' }}
    </p>
</div>
@endif

<!-- Action Button -->
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/login') }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">View Assignment Details</a>
</div>

<p style="font-size: 14px; color: #64748b; margin-top: 20px;">
    If you have any questions or need to make changes, please contact us at 
    <a href="mailto:support@casprivatecare.com" style="color: #2563eb;">support@casprivatecare.com</a>.
</p>

<p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection
