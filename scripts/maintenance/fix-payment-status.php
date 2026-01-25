<?php
/**
 * Fix Payment Status for Demo Client
 * Run with: php fix-payment-status.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Payment Status Fix Tool ===\n\n";

// Find the demo client
$user = App\Models\User::where('email', 'client@demo.com')->first();

if (!$user) {
    echo "❌ User client@demo.com not found\n";
    exit(1);
}

echo "✅ Found user: {$user->name} (ID: {$user->id})\n\n";

// Get all bookings for this client
$bookings = App\Models\Booking::where('client_id', $user->id)->get();

echo "📋 Found {$bookings->count()} booking(s):\n\n";

foreach ($bookings as $booking) {
    echo "Booking #{$booking->id}:\n";
    echo "  - Status: {$booking->status}\n";
    echo "  - Payment Status: " . ($booking->payment_status ?? 'null') . "\n";
    echo "  - Payment Intent ID: " . ($booking->stripe_payment_intent_id ?? 'null') . "\n";
    echo "  - Service Date: {$booking->service_date}\n";
    echo "  - Duration: {$booking->duration_days} days\n";
    echo "  - Hourly Rate: $" . ($booking->hourly_rate ?? 45) . "\n";
    
    // Check for Payment records
    $payments = App\Models\Payment::where('booking_id', $booking->id)->get();
    echo "  - Payment Records: {$payments->count()}\n";
    foreach ($payments as $payment) {
        echo "    * Payment #{$payment->id}: Status={$payment->status}, Amount=\${$payment->amount}\n";
    }
    
    echo "\n";
}

// Check if there's a booking with stripe_payment_intent_id but no payment_status = 'paid'
$bookingsToFix = $bookings->filter(function($b) {
    return $b->stripe_payment_intent_id && $b->payment_status !== 'paid';
});

if ($bookingsToFix->count() > 0) {
    echo "⚠️  Found {$bookingsToFix->count()} booking(s) with payment intent but not marked as paid.\n\n";
    
    foreach ($bookingsToFix as $booking) {
        echo "Fixing Booking #{$booking->id}...\n";
        
        // Update booking payment_status
        $booking->update([
            'payment_status' => 'paid',
            'payment_date' => now()
        ]);
        
        // Calculate amount
        $hours = 8;
        if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
            $hours = (int)$matches[1];
        }
        $rate = $booking->hourly_rate ?: 45;
        $amount = $hours * $booking->duration_days * $rate;
        
        // Check if Payment record exists
        $existingPayment = App\Models\Payment::where('booking_id', $booking->id)
            ->where('status', 'completed')
            ->first();
            
        if (!$existingPayment) {
            // Create Payment record
            App\Models\Payment::create([
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'amount' => $amount,
                'platform_fee' => $amount * 0.15,
                'caregiver_amount' => $amount * 0.85,
                'payment_method' => 'stripe',
                'status' => 'completed',
                'transaction_id' => $booking->stripe_payment_intent_id,
                'paid_at' => now(),
                'notes' => 'Fixed via fix-payment-status.php'
            ]);
            echo "  ✅ Created Payment record\n";
        }
        
        echo "  ✅ Updated booking payment_status to 'paid'\n";
    }
} else {
    // Check if any approved bookings need to be marked as paid (for recent successful payments)
    $approvedBookings = $bookings->filter(function($b) {
        return $b->status === 'approved' && $b->payment_status !== 'paid';
    });
    
    if ($approvedBookings->count() > 0) {
        echo "⚠️  Found {$approvedBookings->count()} approved booking(s) without payment status.\n";
        echo "    If payment was just completed via Stripe, run:\n";
        echo "    php fix-payment-status.php --mark-paid BOOKING_ID\n\n";
    }
}

// Also check for approved bookings that may have been paid recently
$unpaidApproved = $bookings->filter(function($b) {
    return $b->status === 'approved' && $b->payment_status !== 'paid';
})->first();

if ($unpaidApproved) {
    echo "\n🔧 To manually mark booking #{$unpaidApproved->id} as paid, run:\n";
    echo "   php fix-payment-status.php --mark-paid {$unpaidApproved->id}\n";
}

// Handle --mark-paid argument
if (isset($argv[1]) && $argv[1] === '--mark-paid' && isset($argv[2])) {
    $bookingId = (int)$argv[2];
    $booking = App\Models\Booking::find($bookingId);
    
    if ($booking) {
        echo "\n🔄 Marking Booking #{$bookingId} as paid...\n";
        
        $booking->update([
            'payment_status' => 'paid',
            'payment_date' => now()
        ]);
        
        $hours = 8;
        if (preg_match('/(\d+)\s*Hours?/i', $booking->duty_type, $matches)) {
            $hours = (int)$matches[1];
        }
        $rate = $booking->hourly_rate ?: 45;
        $amount = $hours * $booking->duration_days * $rate;
        
        $existingPayment = App\Models\Payment::where('booking_id', $booking->id)
            ->where('status', 'completed')
            ->first();
            
        if (!$existingPayment) {
            App\Models\Payment::create([
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'amount' => $amount,
                'platform_fee' => $amount * 0.15,
                'caregiver_amount' => $amount * 0.85,
                'payment_method' => 'credit_card',
                'status' => 'completed',
                'transaction_id' => 'manual_' . time(),
                'paid_at' => now(),
                'notes' => 'Manually marked as paid via fix-payment-status.php'
            ]);
            echo "✅ Created Payment record (Amount: \${$amount})\n";
        }
        
        echo "✅ Booking #{$bookingId} marked as paid!\n";
    } else {
        echo "❌ Booking #{$bookingId} not found.\n";
    }
}

echo "\n=== Done ===\n";
