@extends('emails.layout')

@section('title', 'Contract Renewal Reminder')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 56px; font-size: 28px; font-weight: bold; color: white; border: 3px solid rgba(255,255,255,0.4);">&#9201;</div>
        <h2 style="color: #1e293b; margin: 10px 0;">Contract Renewal Reminder</h2>
    </div>
    
    <div style="background-color: #fff7ed; border-left: 4px solid #f97316; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0; font-size: 18px; font-weight: 600; color: #c2410c;">
            {{ $days_until_renewal }} Day{{ $days_until_renewal > 1 ? 's' : '' }} Until Renewal
        </p>
    </div>
    
    <p style="font-size: 16px;">Hello {{ $client_name }},</p>
    
    <p style="font-size: 16px;">This is a friendly reminder that your recurring care service contract will automatically renew 
        <strong>{{ $days_until_renewal }} day{{ $days_until_renewal > 1 ? 's' : '' }} from now</strong> 
        on <strong>{{ $renewal_date }}</strong>.
    </p>

    <div style="background-color: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e2e8f0;">
        <h3 style="margin-top: 0; color: #f97316;">Contract Details</h3>
        <p style="margin: 8px 0;"><strong>Booking ID:</strong> #{{ $booking_id }}</p>
        <p style="margin: 8px 0;"><strong>Service Type:</strong> {{ $service_type }}</p>
        <p style="margin: 8px 0;"><strong>Hours per Day:</strong> {{ $hours_per_day }} hours</p>
        <p style="margin: 8px 0;"><strong>Duration:</strong> {{ $duration_days }} days</p>
        <p style="margin: 8px 0;"><strong>Renewal Date:</strong> {{ $renewal_date }}</p>
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 15px 0;">
        <p style="margin: 8px 0; font-size: 20px;"><strong>Amount to be Charged:</strong> <span style="color: #f97316; font-weight: bold;">${{ $amount }}</span></p>
    </div>

    <div style="background-color: #fffbeb; padding: 15px; border-left: 4px solid #f59e0b; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0;"><strong style="color: #92400e;">Auto-Renewal Enabled</strong></p>
        <p style="margin: 10px 0 0 0; color: #92400e;">
            Your saved payment method will be automatically charged on {{ $renewal_date }}.
            A new contract with the same schedule will begin automatically.
        </p>
    </div>

    <h3 style="color: #f97316;">What Happens Next?</h3>
    <ul style="line-height: 1.8; font-size: 16px;">
        <li>On <strong>{{ $renewal_date }}</strong>, your saved card will be charged <strong>${{ $amount }}</strong></li>
        <li>A new {{ $duration_days }}-day contract will begin with the same caregiver and schedule</li>
        <li>You'll receive a receipt confirmation email after the charge is processed</li>
        <li>Your care service continues without interruption</li>
    </ul>

    <div style="text-align: center; margin: 30px 0;">
        <p style="margin-bottom: 15px; font-size: 16px;">Need to make changes or cancel auto-renewal?</p>
        <a href="{{ url('/login') }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">Manage Your Contract</a>
        <p style="font-size: 14px; color: #64748b; margin-top: 15px;">
            Go to Payment Information → Recurring Contracts to disable auto-renewal
        </p>
    </div>

    <p style="font-size: 16px;">If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

    <p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection
