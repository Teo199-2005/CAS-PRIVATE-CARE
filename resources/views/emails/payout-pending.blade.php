@extends('emails.layout')

@section('title', 'Upcoming Payout')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 15px; line-height: 56px; font-size: 28px; font-weight: bold; border: 3px solid rgba(255,255,255,0.4);">&#9201;</div>
        <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Payout Coming Soon!</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Your earnings are ready to be paid</p>
    </div>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">Hi {{ $user->name }},</p>

<p style="font-size: 16px; margin-bottom: 25px;">
    Good news! You have <strong style="color: #f59e0b; font-size: 20px;">${{ number_format($amount, 2) }}</strong> in pending earnings ready for payout.
</p>

<!-- Earnings Summary Card -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 18px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        Earnings Summary
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; color: #64748b; width: 40%;">Pending Amount:</td>
            <td style="padding: 10px 0; font-weight: 600; color: #f59e0b; font-size: 18px;">${{ number_format($amount, 2) }}</td>
        </tr>
        @if($hoursWorked)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Total Hours:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ number_format($hoursWorked, 1) }} hours</td>
        </tr>
        @endif
        @if($pendingCount > 1)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Sessions:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $pendingCount }} work sessions</td>
        </tr>
        @endif
        @if($periodStart && $periodEnd)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Period:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ \Carbon\Carbon::parse($periodStart)->format('M j') }} - {{ \Carbon\Carbon::parse($periodEnd)->format('M j, Y') }}</td>
        </tr>
        @endif
    </table>
</div>

<!-- Scheduled Date -->
@if($scheduledDate)
<div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; margin-bottom: 25px; text-align: center;">
    <p style="margin: 0 0 5px 0; color: #3b82f6; font-weight: 600;">
        Scheduled Payout Date
    </p>
    <p style="margin: 0; font-size: 20px; font-weight: 700; color: #1e40af;">
        {{ \Carbon\Carbon::parse($scheduledDate)->format('l, F j, Y') }}
    </p>
</div>
@endif

<!-- Ensure Bank Account -->
<div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
    <p style="margin: 0; font-size: 13px; color: #166534;">
        <strong>Reminder:</strong> Make sure your bank account is connected and verified to receive your payout on time.
    </p>
</div>

<!-- Dashboard Button -->
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/login') }}" class="button" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none !important; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">
        View Your Earnings
    </a>
</div>

<p style="font-size: 14px; color: #64748b; margin-top: 20px;">
    Questions about your earnings? Contact us at 
    <a href="mailto:support@casprivatecare.com" style="color: #2563eb;">support@casprivatecare.com</a>.
</p>

<p style="font-size: 16px; margin-top: 25px;">
    Keep up the great work!
</p>
@endsection
