@extends('emails.layout')

@section('title', 'Payment Confirmation')

@section('content')
<div style="text-align: center; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 20px;">
        <div style="width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 32px; font-weight: bold;">&#10003;</div>
        <h1 style="margin: 0; font-size: 28px; font-weight: 700;">Payment Sent!</h1>
        <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Your payout has been processed successfully</p>
    </div>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">Hi {{ $user->name }},</p>

<p style="font-size: 16px; margin-bottom: 25px;">
    Great news! Your payment of <strong style="color: #10b981; font-size: 20px;">${{ number_format($amount, 2) }}</strong> has been sent to your bank account.
</p>

<!-- Payment Details Card -->
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 18px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        Payment Details
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 10px 0; color: #64748b; width: 40%;">Amount:</td>
            <td style="padding: 10px 0; font-weight: 600; color: #10b981; font-size: 18px;">${{ number_format($amount, 2) }}</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Payment Date:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ \Carbon\Carbon::parse($payoutDate)->format('F j, Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Payment Method:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ $payoutMethod }}</td>
        </tr>
        @if($periodStart && $periodEnd)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Pay Period:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ \Carbon\Carbon::parse($periodStart)->format('M j') }} - {{ \Carbon\Carbon::parse($periodEnd)->format('M j, Y') }}</td>
        </tr>
        @endif
        @if($hoursWorked)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Hours Worked:</td>
            <td style="padding: 10px 0; font-weight: 500;">{{ number_format($hoursWorked, 1) }} hours</td>
        </tr>
        @endif
        @if($transactionId)
        <tr>
            <td style="padding: 10px 0; color: #64748b;">Transaction ID:</td>
            <td style="padding: 10px 0; font-weight: 500; font-family: monospace; font-size: 13px;">{{ $transactionId }}</td>
        </tr>
        @endif
    </table>
</div>

<!-- Estimated Arrival -->
<div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 12px; padding: 20px; margin-bottom: 25px; text-align: center;">
    <p style="margin: 0 0 5px 0; color: #3b82f6; font-weight: 600;">
        🏦 Estimated Bank Arrival
    </p>
    <p style="margin: 0; font-size: 20px; font-weight: 700; color: #1e40af;">
        {{ $estimatedArrival }}
    </p>
    <p style="margin: 10px 0 0 0; color: #64748b; font-size: 13px;">
        Funds typically arrive within 2-3 business days
    </p>
</div>

<!-- Dashboard Button -->
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/login') }}" class="button" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none !important; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">
        View Your Dashboard
    </a>
</div>

<!-- Tax Reminder -->
<div style="background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
    <p style="margin: 0; font-size: 13px; color: #92400e;">
        <strong>Tax Reminder:</strong> As an independent contractor, remember to set aside approximately 25-30% of your earnings for taxes. You can view your tax estimates in your dashboard.
    </p>
</div>

<p style="font-size: 14px; color: #64748b; margin-top: 20px;">
    If you have any questions about this payment, please contact our support team at 
    <a href="mailto:support@casprivatecare.com" style="color: #2563eb;">support@casprivatecare.com</a> 
    or call us at <a href="tel:+16462828282" style="color: #2563eb;">(646) 282-8282</a>.
</p>

<p style="font-size: 16px; margin-top: 25px;">
    Thank you for being part of the CAS Private Care team!
</p>
@endsection
