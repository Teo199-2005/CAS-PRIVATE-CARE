@extends('emails.layout')

@section('title', 'Announcement')

@section('content')
    <h2>{{ $title }}</h2>
    
    <p>Hello,</p>
    
    <div style="background-color: @if($type === 'success') #d1fae5 @elseif($type === 'warning') #fef3c7 @elseif($type === 'error') #fee2e2 @else #e0e7ff @endif; padding: 20px; border-radius: 6px; margin: 20px 0; border-left: 4px solid @if($type === 'success') #10b981 @elseif($type === 'warning') #f59e0b @elseif($type === 'error') #ef4444 @else #3b82f6 @endif;">
        {!! nl2br(e($message)) !!}
    </div>
    
    <p>For more information, please log in to your dashboard or contact our support team.</p>
    
    <p>Best regards,<br>The CAS Private Care Team</p>
@endsection


