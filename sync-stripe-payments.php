<?php
/**
 * Sync Stripe Payments to Local Database
 * This script fetches all successful payments from Stripe and updates the local payments table
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\Booking;

echo "=== Syncing Stripe Payments ===\n\n";

// Initialize Stripe
\Stripe\Stripe::setApiKey(config('services.stripe.secret'));

try {
    // Fetch all successful payment intents from Stripe
    $paymentIntents = \Stripe\PaymentIntent::all([
        'limit' => 100,
    ]);

    $syncedCount = 0;
    $totalAmount = 0;

    foreach ($paymentIntents->data as $pi) {
        // Only process succeeded payment intents
        if ($pi->status !== 'succeeded') {
            continue;
        }

        $amount = $pi->amount / 100; // Convert from cents
        $totalAmount += $amount;

        // Check if this payment intent already exists in our database
        $existingPayment = Payment::where('stripe_payment_intent_id', $pi->id)->first();
        
        if (!$existingPayment) {
            // Try to find the booking from the description
            $description = $pi->description ?? '';
            $bookingId = null;
            
            // Extract booking ID from description like "Booking #22 - Housekeeping"
            if (preg_match('/Booking #(\d+)/', $description, $matches)) {
                $bookingId = (int)$matches[1];
            }

            // Get client ID from booking if available
            $clientId = null;
            $caregiverId = null;
            $housekeeperId = null;
            $providerType = 'caregiver';
            $booking = null;
            
            if ($bookingId) {
                $booking = Booking::find($bookingId);
                if ($booking) {
                    $clientId = $booking->client_id;
                    $caregiverId = $booking->caregiver_id;
                    $housekeeperId = $booking->housekeeper_id;
                    if ($booking->service_type === 'Housekeeping' || $housekeeperId) {
                        $providerType = 'housekeeper';
                    }
                } else {
                    // Booking doesn't exist, skip this payment or set booking_id to null
                    echo "⚠️ Skipping: Booking #{$bookingId} not found - {$description}\n";
                    continue;
                }
            } else {
                // No booking ID found in description, skip
                echo "⚠️ Skipping: No booking ID found - {$description}\n";
                continue;
            }
            
            // Must have a valid client_id
            if (!$clientId) {
                echo "⚠️ Skipping: No client ID for booking #{$bookingId}\n";
                continue;
            }

            // Calculate fees (approximate - Stripe typically takes ~2.9% + $0.30)
            $processingFee = round($amount * 0.029 + 0.30, 2);
            $platformFee = round($amount * 0.10, 2); // 10% platform fee
            $providerAmount = $amount - $processingFee - $platformFee;

            // Create payment record
            $payment = Payment::create([
                'booking_id' => $bookingId,
                'client_id' => $clientId,
                'caregiver_id' => $caregiverId,
                'housekeeper_id' => $housekeeperId,
                'amount' => $amount,
                'processing_fee' => $processingFee,
                'platform_fee' => $platformFee,
                'caregiver_amount' => $providerType === 'caregiver' ? $providerAmount : 0,
                'housekeeper_amount' => $providerType === 'housekeeper' ? $providerAmount : 0,
                'provider_type' => $providerType,
                'status' => 'completed',
                'payment_method' => 'credit_card',
                'stripe_payment_intent_id' => $pi->id,
                'stripe_customer_id' => $pi->customer,
                'paid_at' => \Carbon\Carbon::createFromTimestamp($pi->created),
                'notes' => $description . ' (Synced from Stripe)',
                'created_at' => \Carbon\Carbon::createFromTimestamp($pi->created),
                'updated_at' => now()
            ]);

            echo "✅ Synced: \${$amount} - {$description}\n";
            $syncedCount++;

            // Update booking payment status if we found the booking
            if ($bookingId && isset($booking)) {
                $booking->update([
                    'payment_status' => 'paid',
                    'stripe_payment_intent_id' => $pi->id
                ]);
                echo "   → Updated Booking #{$bookingId} payment status to 'paid'\n";
            }
        } else {
            // Update existing payment to completed if not already
            if ($existingPayment->status !== 'completed') {
                $existingPayment->update(['status' => 'completed']);
                echo "📝 Updated existing payment to completed: \${$amount}\n";
                $syncedCount++;
            }
        }
    }

    echo "\n=== Sync Complete ===\n";
    echo "Synced {$syncedCount} new payments\n";
    echo "Total Stripe revenue: \$" . number_format($totalAmount, 2) . "\n";

    // Verify the new total
    $dbTotal = Payment::where('status', 'completed')->sum('amount');
    echo "Database completed payments total: \$" . number_format($dbTotal, 2) . "\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
