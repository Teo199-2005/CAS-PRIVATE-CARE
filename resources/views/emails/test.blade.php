@extends('emails.layout')

@section('title', 'Test Email')

@section('content')
    <table style="width:100%;max-width:640px;margin:0 auto;font-family: Arial, Helvetica, sans-serif;color:#333;">
        <tr>
            <td style="padding:20px 0;text-align:left;">
                <?php
                    // Build a reliable image source for the logo.
                    // 1) Prefer embedding as a CID via $message->embed() when available (best compatibility).
                    // 2) Otherwise fall back to a base64 data URI so the image renders even when APP_URL is local.
                    $logoPath = public_path('logo.png');
                    $logoSrc = '';
                    if (file_exists($logoPath)) {
                        // If the $message object exists during mail rendering, try embedding as CID.
                        if (isset($message)) {
                            try {
                                $logoSrc = $message->embed($logoPath);
                            } catch (\Exception $e) {
                                // ignore and fall through to base64 fallback
                            }
                        }

                        // If embedding didn't produce a src, create a base64 data URI fallback.
                        if (empty($logoSrc)) {
                            $logoData = file_get_contents($logoPath);
                            $logoSrc = 'data:image/png;base64,' . base64_encode($logoData);
                        }
                    }
                ?>
                <img src="{{ $logoSrc ?: asset('logo.png') }}" alt="CAS Private Care" style="height:56px;vertical-align:middle;" />
            </td>
        </tr>

        <tr>
            <td style="background:#ffffff;padding:24px;border-radius:8px;border:1px solid #ececec;">
                <h1 style="margin:0 0 12px;color:#0b63d6;font-size:22px;font-weight:700;">Test Email Successful! ✅</h1>

                <p style="margin:0 0 12px;">Hello,</p>

                <p style="margin:0 0 16px;">This is a test email from your CAS Private Care application.</p>

                <div style="background-color:#fff7ec;padding:18px;border-radius:6px;margin:18px 0;border-left:6px solid #ff7a00;color:#3b3b3b;">
                    <div style="font-weight:700;margin-bottom:6px;"><span style="color:#0b63d6;">Email Configuration Status:</span> <span style="font-weight:600;color:#ff7a00;">Working correctly!</span></div>
                    <div style="margin-bottom:4px;"><strong style="color:#0b63d6;">Brevo SMTP:</strong> <span style="color:#1f2937;">Connected successfully</span></div>
                    <div><strong style="color:#0b63d6;">Timestamp:</strong> <span style="color:#1f2937;">{{ now()->format('F d, Y h:i A') }}</span></div>
                </div>

                <p style="margin:0 0 8px;">If you received this email, your email system is properly configured and ready to send:</p>
                <ul style="margin:0 0 16px;padding-left:20px;color:#333;">
                    <li>Welcome emails</li>
                    <li>Email verification</li>
                    <li>Password reset emails</li>
                    <li>Booking approval notifications</li>
                    <li>Contractor approval notifications</li>
                    <li>Announcements</li>
                </ul>

                <p style="margin:0 0 0;color:#374151;">Best regards,<br><span style="color:#0b63d6;font-weight:600;">The CAS Private Care Team</span></p>
            </td>
        </tr>

        <tr>
            <td style="padding:16px 0;text-align:center;color:#9ca3af;font-size:12px;">
                This email was sent by <span style="color:#0b63d6;font-weight:600;">CAS Private Care LLC</span>.
            </td>
        </tr>
    </table>
@endsection



