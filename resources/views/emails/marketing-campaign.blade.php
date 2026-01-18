@extends('emails.layout')

@section('title', $campaign->name ?? 'CAS Private Care')

@section('content')
<div style="margin-bottom: 25px;">
    {!! $content !!}
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $clickTrackUrl }}?redirect={{ urlencode(url('/book')) }}" style="display: inline-block; padding: 14px 32px; background-color: #f97316; color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 2px 4px rgba(249, 115, 22, 0.3);">Book Your Care Today</a>
</div>

<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
    <p style="font-size: 12px; color: #94a3b8; text-align: center;">
        You're receiving this email because you're a valued member of CAS Private Care.<br>
        <a href="{{ $clickTrackUrl }}?redirect={{ urlencode(url('/email/unsubscribe/' . $trackingId)) }}" style="color: #f97316;">Unsubscribe</a> from marketing emails.
    </p>
</div>

<!-- Tracking Pixel -->
<img src="{{ $trackingPixelUrl }}" width="1" height="1" style="display:none;" alt="" />
@endsection
