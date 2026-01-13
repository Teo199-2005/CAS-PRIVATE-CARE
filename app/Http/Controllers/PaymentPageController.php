<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PaymentPageController
 * 
 * Handles payment page display and payment success processing.
 * This controller manages the payment flow for booking payments.
 */
class PaymentPageController extends Controller
{
    /**
     * Display the payment page for a booking
     */
    public function show(Request $request)
    {
        $bookingId = $request->query('booking_id');
        
        if (!$bookingId) {
            return redirect('/client/dashboard')->with('error', 'No booking specified');
        }
        
        $booking = Booking::with(['client', 'assignments.caregiver.user', 'referralCode'])
            ->where('id', $bookingId)
            ->first();
            
        if (!$booking) {
            return redirect('/client/dashboard')->with('error', 'Booking not found');
        }
        
        // Verify ownership (if authenticated)
        if (auth()->check() && auth()->user()->user_type === 'client') {
            if ($booking->client_id !== auth()->id()) {
                return redirect('/client/dashboard')->with('error', 'Unauthorized access');
            }
        }
        
        $stripeKey = config('stripe.key');
        
        return view('payment', compact('booking', 'bookingId', 'stripeKey'));
    }

    /**
     * Handle payment success - update database and show confirmation
     */
    public function success(Request $request)
    {
        $bookingId = $request->query('booking_id');
        $paymentIntentId = $request->query('payment_intent');
        
        Log::info("=== PAYMENT SUCCESS PAGE LOADED ===", [
            'booking_id' => $bookingId,
            'payment_intent' => $paymentIntentId,
            'all_params' => $request->all()
        ]);
        
        if (!$bookingId) {
            return redirect('/client/dashboard')->with('error', 'No booking specified');
        }
        
        $booking = Booking::with(['client', 'assignments.caregiver.user'])
            ->where('id', $bookingId)
            ->first();
            
        if (!$booking) {
            return redirect('/client/dashboard')->with('error', 'Booking not found');
        }
        
        // Update database if not already paid
        if ($booking->payment_status !== 'paid') {
            $this->processPaymentSuccess($booking, $paymentIntentId);
            
            // Refresh booking object
            $booking = Booking::with(['client', 'assignments.caregiver.user'])
                ->where('id', $bookingId)
                ->first();
        } else {
            Log::info("Payment Success - Booking already paid", [
                'booking_id' => $bookingId
            ]);
        }
        
        return view('payment-success', compact('booking', 'bookingId', 'paymentIntentId'));
    }

    /**
     * Process the payment success - update booking and create payment record
     */
    protected function processPaymentSuccess(Booking $booking, ?string $paymentIntentId)
    {
        try {
            Log::info("Payment Success - Booking not paid, updating now", [
                'booking_id' => $booking->id,
                'current_status' => $booking->payment_status
            ]);
            
            // Calculate amount from booking
            $hours = $this->extractHoursFromDutyType($booking->duty_type);
            $rate = $booking->assigned_hourly_rate ?: 28;
            $amount = $hours * $booking->duration_days * $rate;
            $platformFee = $amount * 0.10;
            $caregiverAmount = $amount * 0.90;
            
            // Try to verify with Stripe if payment_intent provided
            if ($paymentIntentId) {
                $stripeData = $this->verifyStripePayment($paymentIntentId);
                if ($stripeData) {
                    $amount = $stripeData['amount'];
                    $platformFee = $amount * 0.10;
                    $caregiverAmount = $amount * 0.90;
                }
            }
            
            // Update booking
            DB::table('bookings')->where('id', $booking->id)->update([
                'payment_status' => 'paid',
                'stripe_payment_intent_id' => $paymentIntentId,
                'payment_date' => now(),
                'updated_at' => now(),
            ]);
            
            // Create payment record if it doesn't exist
            $existingPayment = DB::table('payments')->where('booking_id', $booking->id)->first();
            
            if (!$existingPayment) {
                DB::table('payments')->insert([
                    'booking_id' => $booking->id,
                    'client_id' => $booking->client_id,
                    'amount' => $amount,
                    'platform_fee' => $platformFee,
                    'caregiver_amount' => $caregiverAmount,
                    'payment_method' => 'credit_card',
                    'status' => 'completed',
                    'transaction_id' => $paymentIntentId ?: 'payment_' . time(),
                    'paid_at' => now(),
                    'notes' => 'Stripe payment completed',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info("=== PAYMENT RECORD CREATED ===", [
                    'booking_id' => $booking->id,
                    'amount' => $amount,
                    'platform_fee' => $platformFee,
                    'caregiver_amount' => $caregiverAmount
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Payment Success - Error updating database: " . $e->getMessage(), [
                'booking_id' => $booking->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Extract hours from duty type string
     */
    protected function extractHoursFromDutyType(?string $dutyType): int
    {
        if ($dutyType && preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches)) {
            return (int)$matches[1];
        }
        return 8; // Default
    }

    /**
     * Verify payment with Stripe API
     */
    protected function verifyStripePayment(string $paymentIntentId): ?array
    {
        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
            
            if ($paymentIntent->status === 'succeeded') {
                Log::info("Stripe verification successful", [
                    'stripe_status' => $paymentIntent->status,
                    'stripe_amount' => $paymentIntent->amount
                ]);
                
                return [
                    'amount' => $paymentIntent->amount / 100,
                    'status' => $paymentIntent->status
                ];
            }
        } catch (\Exception $stripeError) {
            Log::warning("Stripe verification failed, using calculated amount", [
                'error' => $stripeError->getMessage()
            ]);
        }
        
        return null;
    }

    /**
     * Update payment status via API
     */
    public function updatePaymentStatus(Request $request)
    {
        try {
            Log::info('Payment status update request received', [
                'booking_id' => $request->input('booking_id'),
                'payment_intent_id' => $request->input('payment_intent_id')
            ]);
            
            $bookingId = $request->input('booking_id');
            $paymentIntentId = $request->input('payment_intent_id');
            
            if (!$bookingId) {
                return response()->json(['success' => false, 'message' => 'No booking ID provided']);
            }
            
            $booking = Booking::find($bookingId);
            
            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
            }
            
            // Update the booking record
            $booking->update([
                'payment_status' => 'paid',
                'payment_intent_id' => $paymentIntentId,
                'stripe_payment_intent_id' => $paymentIntentId,
                'payment_date' => now()
            ]);
            
            // Calculate the booking amount
            $hours = $this->extractHoursFromDutyType($booking->duty_type);
            $rate = $booking->assigned_hourly_rate ?: 28;
            $amount = $hours * $booking->duration_days * $rate;
            $platformFee = $amount * 0.10;
            $caregiverAmount = $amount * 0.90;
            
            // Create a Payment record if it doesn't exist
            $existingPayment = Payment::where('booking_id', $booking->id)
                ->where('status', 'completed')
                ->first();
            
            if (!$existingPayment) {
                DB::table('payments')->insert([
                    'booking_id' => $booking->id,
                    'client_id' => $booking->client_id,
                    'amount' => $amount,
                    'platform_fee' => $platformFee,
                    'caregiver_amount' => $caregiverAmount,
                    'payment_method' => 'credit_card',
                    'status' => 'completed',
                    'transaction_id' => $paymentIntentId,
                    'paid_at' => now(),
                    'notes' => 'Stripe payment via Stripe Elements',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                Log::info('Payment record created for booking #' . $booking->id, [
                    'booking_id' => $booking->id,
                    'amount' => $amount,
                    'platform_fee' => $platformFee,
                    'caregiver_amount' => $caregiverAmount,
                    'payment_intent_id' => $paymentIntentId
                ]);
            }
            
            return response()->json([
                'success' => true,
                'booking' => $booking,
                'receipt_url' => route('receipt.payment', ['bookingId' => $booking->id])
            ]);
            
        } catch (\Exception $e) {
            Log::error('Payment status update error: ' . $e->getMessage(), [
                'booking_id' => $request->input('booking_id'),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
