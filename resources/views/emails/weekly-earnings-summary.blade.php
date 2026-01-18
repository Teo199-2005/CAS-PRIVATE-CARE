@extends('emails.layout')

@section('title', 'Weekly Earnings Summary')

@section('content')
<div style="text-align: center; margin-bottom: 25px;">
    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 56px; font-size: 28px; font-weight: bold; color: white; border: 3px solid rgba(255,255,255,0.4);">$</div>
    <h2 style="color: #1e293b; margin: 10px 0;">Weekly Earnings Summary</h2>
</div>

<p style="font-size: 16px; margin-bottom: 20px;">Hi {{ $contractor->name }},</p>

<p style="font-size: 16px; margin-bottom: 25px;">
    Here's your earnings summary for the week of <strong>{{ $weekStart->format('M j') }} - {{ $weekEnd->format('M j, Y') }}</strong>.
</p>

<!-- Earnings Overview Card -->
<div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #86efac; border-radius: 16px; padding: 30px; margin-bottom: 25px; text-align: center;">
    <p style="margin: 0 0 5px 0; color: #166534; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Total Earnings This Week</p>
    <p style="margin: 0; font-size: 42px; font-weight: 700; color: #059669;">${{ number_format($totalEarnings, 2) }}</p>
</div>

<!-- Stats Grid -->
<div style="margin-bottom: 25px;">
    <table style="width: 100%; border-collapse: separate; border-spacing: 10px;">
        <tr>
            <td style="background: #f8fafc; border-radius: 10px; padding: 20px; text-align: center; width: 50%;">
                <p style="margin: 0 0 5px 0; color: #64748b; font-size: 13px;">Hours Worked</p>
                <p style="margin: 0; font-size: 24px; font-weight: 700; color: #1e293b;">{{ number_format($hoursWorked, 1) }}</p>
            </td>
            <td style="background: #f8fafc; border-radius: 10px; padding: 20px; text-align: center; width: 50%;">
                <p style="margin: 0 0 5px 0; color: #64748b; font-size: 13px;">Shifts Completed</p>
                <p style="margin: 0; font-size: 24px; font-weight: 700; color: #1e293b;">{{ count($shifts) }}</p>
            </td>
        </tr>
    </table>
</div>

<!-- Shift Breakdown -->
@if(count($shifts) > 0)
<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
    <h3 style="margin: 0 0 20px 0; color: #1e293b; font-size: 18px; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
        Shift Breakdown
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f1f5f9;">
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 13px;">Date</th>
                <th style="padding: 12px; text-align: left; color: #64748b; font-weight: 600; font-size: 13px;">Hours</th>
                <th style="padding: 12px; text-align: right; color: #64748b; font-weight: 600; font-size: 13px;">Earnings</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shifts as $shift)
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 12px; color: #1e293b;">{{ \Carbon\Carbon::parse($shift['date'])->format('M j, Y') }}</td>
                <td style="padding: 12px; color: #64748b;">{{ $shift['hours'] }} hrs</td>
                <td style="padding: 12px; text-align: right; font-weight: 600; color: #059669;">${{ number_format($shift['earnings'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<!-- Pending Payouts -->
@if($pendingPayouts > 0)
<div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 12px; padding: 20px; margin-bottom: 25px;">
    <table style="width: 100%;">
        <tr>
            <td style="width: 60px; vertical-align: top;">
                <div style="width: 40px; height: 40px; background: #f59e0b; border-radius: 50%; text-align: center; line-height: 40px; color: white; font-size: 18px;">!</div>
            </td>
            <td>
                <p style="margin: 0 0 5px 0; font-weight: 600; color: #92400e;">Pending Payouts</p>
                <p style="margin: 0; color: #92400e; font-size: 14px;">
                    You have <strong>${{ number_format($pendingPayouts, 2) }}</strong> in pending payouts that will be processed soon.
                </p>
            </td>
        </tr>
    </table>
</div>
@endif

<!-- Action Button -->
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ url('/login') }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">View Full Earnings Report</a>
</div>

<p style="font-size: 14px; color: #64748b; margin-top: 20px; text-align: center;">
    Thank you for being a valued member of our care team!
</p>

<p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection
