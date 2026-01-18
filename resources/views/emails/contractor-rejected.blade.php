@extends('emails.layout')

@section('title', 'Application Status Update')

@section('content')
    <div style="text-align: center; margin-bottom: 25px;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #64748b 0%, #475569 100%); border-radius: 50%; margin: 0 auto 15px; line-height: 60px; font-size: 28px; font-weight: bold; color: white;">i</div>
        <h2 style="color: #1e293b; margin: 10px 0;">Application Status Update</h2>
    </div>
    
    <p style="font-size: 16px;">Hello {{ $user->name }},</p>
    
    <p style="font-size: 16px;">Thank you for your interest in becoming a contractor with CAS Private Care.</p>
    
    <div style="background-color: #fee2e2; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ef4444;">
        <strong style="color: #dc2626;">Application Status: Not Approved</strong><br>
        <span style="color: #7f1d1d;">We regret to inform you that your contractor application has not been approved at this time.</span>
    </div>
    
    @if($reason)
    <div style="background-color: #f8fafc; padding: 16px; border-radius: 8px; margin: 20px 0; border: 1px solid #e2e8f0;">
        <strong style="color: #475569;">Reason:</strong><br>
        <span style="color: #64748b;">{{ $reason }}</span>
    </div>
    @endif
    
    <p style="font-size: 16px;">We appreciate your interest in joining our platform. If you have any questions about this decision or would like to reapply in the future, please don't hesitate to contact our support team.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="mailto:support@casprivatecare.com" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">Contact Support</a>
    </div>
    
    <p style="font-size: 16px;">Thank you for your understanding.</p>
    
    <p style="font-size: 16px; margin-top: 25px;">Best regards,<br><strong>The CAS Private Care Team</strong></p>
@endsection



