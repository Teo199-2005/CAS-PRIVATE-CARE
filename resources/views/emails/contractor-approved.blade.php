@extends('emails.layout')

@section('title', 'Application Approved')

@section('content')
    <h2>Application Approved!</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Congratulations! Your application to become a contractor with CAS Private Care has been approved.</p>
    
    <div class="highlight">
        <strong>Your account is now active!</strong> You can now log in to your dashboard and start using the platform.
    </div>
    
    <p>Next steps:</p>
    <ul>
        <li>Log in to your account using your registered email and password</li>
        <li>Complete your profile to increase your visibility</li>
        <li>Start receiving assignments and bookings</li>
    </ul>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/login') }}" class="button">Log In to Dashboard</a>
    </div>
    
    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
    
    <p>Welcome aboard!<br>The CAS Private Care Team</p>
@endsection



