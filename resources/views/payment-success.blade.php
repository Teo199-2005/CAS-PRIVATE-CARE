<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Successful - CAS Private Care</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            max-width: 600px;
            width: 100%;
            padding: 48px 32px;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon i {
            font-size: 48px;
            color: white;
        }

        .success-title {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .success-subtitle {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .booking-details {
            background: #f9fafb;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .detail-value {
            color: #6b7280;
            font-size: 14px;
        }

        .buttons {
            display: flex;
            gap: 16px;
            margin-top: 32px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            transform: translateY(-2px);
        }

        .confirmation-message {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }

        .confirmation-message i {
            font-size: 24px;
            color: #059669;
        }

        .confirmation-message p {
            color: #065f46;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }

        @media (max-width: 640px) {
            .success-container {
                padding: 32px 20px;
            }

            .success-title {
                font-size: 24px;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <!-- Success Icon -->
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>

        <!-- Success Message -->
        <h1 class="success-title">Payment Successful!</h1>
        <p class="success-subtitle">
            Thank you for your payment. Your booking has been confirmed and you will receive a confirmation email shortly.
        </p>

        <!-- Confirmation Message -->
        <div class="confirmation-message">
            <i class="bi bi-envelope-check"></i>
            <p>
                A confirmation email has been sent to <strong>{{ $booking->client->email ?? 'your email' }}</strong> with your booking details and receipt.
            </p>
        </div>

        <!-- Booking Details -->
        <div class="booking-details">
            <div class="detail-row">
                <span class="detail-label">Booking ID</span>
                <span class="detail-value">#{{ $bookingId }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Service Type</span>
                <span class="detail-value">{{ $booking->service_type ?? 'Caregiving Service' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Service Date</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($booking->service_date)->format('M d, Y') }}</span>
            </div>
            @if($booking->duration_days)
            <div class="detail-row">
                <span class="detail-label">Duration</span>
                <span class="detail-value">{{ $booking->duration_days }} days</span>
            </div>
            @endif
            @if($booking->duty_type)
            <div class="detail-row">
                <span class="detail-label">Duty Type</span>
                <span class="detail-value">{{ $booking->duty_type }}</span>
            </div>
            @endif
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value">
                    <span style="display: inline-flex; align-items: center; gap: 4px; background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 6px; font-weight: 600; font-size: 12px;">
                        <i class="bi bi-check-circle"></i>
                        Confirmed
                    </span>
                </span>
            </div>
            @if($paymentIntentId)
            <div class="detail-row">
                <span class="detail-label">Payment ID</span>
                <span class="detail-value" style="font-family: monospace; font-size: 12px;">{{ $paymentIntentId }}</span>
            </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="buttons">
            <a href="/receipts/payment/{{ $bookingId }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-receipt"></i>
                View Receipt
            </a>
            <a href="/client/dashboard" class="btn btn-secondary">
                <i class="bi bi-speedometer2"></i>
                Go to Dashboard
            </a>
        </div>
    </div>

    <script>
        // Update payment status in database when page loads
        (async function() {
            const bookingId = '{{ $bookingId }}';
            const paymentIntentId = '{{ $paymentIntentId ?? "" }}';
            
            if (bookingId && paymentIntentId) {
                try {
                    const response = await fetch('/api/bookings/update-payment-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            booking_id: bookingId,
                            payment_intent_id: paymentIntentId
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Set localStorage flags for dashboard to detect
                        localStorage.setItem('payment_completed', 'true');
                        localStorage.setItem('payment_booking_id', bookingId);
                        localStorage.setItem('payment_timestamp', Date.now().toString());
                    }
                } catch (error) {
                    // Payment status update failed silently
                }
            }
        })();
        
        // Auto-redirect to dashboard after 30 seconds
        setTimeout(() => {
            window.location.href = '/client/dashboard';
        }, 30000);
    </script>
</body>
</html>
