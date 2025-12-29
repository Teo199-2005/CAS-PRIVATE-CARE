@extends('emails.layout')

@section('title', 'Application Status Update')

@section('content')
    <h2>Application Status Update</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Thank you for your interest in becoming a contractor with CAS Private Care.</p>
    
    <div style="background-color: #fee2e2; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid #ef4444;">
        <strong>Application Status: Not Approved</strong><br>
        We regret to inform you that your contractor application has not been approved at this time.
    </div>
    
    @if($reason)
    <div style="background-color: #f9fafb; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <strong>Reason:</strong><br>
        {{ $reason }}
    </div>
    @endif
    
    <p>We appreciate your interest in joining our platform. If you have any questions about this decision or would like to reapply in the future, please don't hesitate to contact our support team.</p>
    
    <p>Thank you for your understanding.</p>
    
    <p>Best regards,<br>The CAS Private Care Team</p>
@endsection


