@extends('emails.layout')

@section('title', 'Welcome to CAS Private Care')

@section('content')
    <h2>Welcome to CAS Private Care!</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Thank you for joining CAS Private Care. We're excited to have you as part of our community!</p>
    
    @if($requiresApproval)
        <div class="highlight">
            <strong>Application Pending Approval</strong><br>
            Your account has been created successfully. Your application is currently pending approval by our administration team. You will receive an email notification once your application has been reviewed and approved.
        </div>
        
        <p>Once approved, you'll be able to:</p>
        <ul>
            <li>Access your contractor dashboard</li>
            <li>Receive assignments and bookings</li>
            <li>Manage your profile and availability</li>
        </ul>
    @else
        <p>Your account has been successfully created! You can now:</p>
        <ul>
            <li>Book caregiving services</li>
            <li>Browse available caregivers</li>
            <li>Manage your bookings and appointments</li>
        </ul>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/login') }}" class="button">Log In to Your Dashboard</a>
        </div>
    @endif
    
    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
    
    <p>Welcome aboard!<br>The CAS Private Care Team</p>
@endsection


