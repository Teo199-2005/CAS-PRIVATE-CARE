<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;
use App\Models\Booking;

class ClientPaymentController extends Controller
{
    protected StripeClient $stripe;

    /**
     * Stripe processing fee rates (business rules)
     * Domestic (US): 2.9% + $0.30
     * International: 4.9% + $0.30
     */
    private float $stripeFeeDomestic = 0.029;
    private float $stripeFeeInternational = 0.049;
    private float $stripeFixedFee = 0.30;

    private function calculateProcessingFee(float $targetAmount, string $cardCountry = 'US'): float
    {
        $rate = strtoupper($cardCountry) === 'US' ? $this->stripeFeeDomestic : $this->stripeFeeInternational;
        $gross = ($targetAmount + $this->stripeFixedFee) / (1 - $rate);
        return round($gross - $targetAmount, 2);
    }

    private function calculateAdjustedTotal(float $targetAmount, string $cardCountry = 'US'): float
    {
        return round($targetAmount + $this->calculateProcessingFee($targetAmount, $cardCountry), 2);
    }

    public function __construct()
    {
        // SECURITY: Use config() instead of env() for cached config support
        $this->stripe = new StripeClient(config('stripe.secret_key') ?: config('services.stripe.secret') ?: env('STRIPE_SECRET'));
    }

    protected function ensureCustomer($user)
    {
        try {
            // Check if user already has a Stripe customer ID
            if (!empty($user->stripe_customer_id)) {
                Log::info('Using existing Stripe customer ID: ' . $user->stripe_customer_id);
                return $user->stripe_customer_id;
            }

            Log::info('Creating new Stripe customer for user: ' . $user->id);

            // Create new Stripe customer
            $customer = $this->stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name ?? $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => $user->user_type ?? 'client'
                ]
            ]);

            Log::info('Stripe customer created: ' . $customer->id);

            // Try to save the customer ID to the user record
            try {
                $user->stripe_customer_id = $customer->id;
                $user->save();
                Log::info('Stripe customer ID saved to user record');
            } catch (\Throwable $e) {
                Log::warning('Unable to save stripe_customer_id to user: ' . $e->getMessage());
                // Don't fail the request, just log the warning
            }

            return $customer->id;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error creating customer: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error ensuring customer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Extract hours per day from duty_type text.
     * Examples: "8 Hours", "12 hours", "24 Hours", "8 Hours per Day"
     */
    private function extractHoursFromDutyType(?string $dutyType): int
    {
        if (!$dutyType) return 8;

        if (preg_match('/(\d{1,2})\s*(hour|hours)/i', $dutyType, $matches)) {
            $h = (int) $matches[1];
            return $h > 0 ? $h : 8;
        }

        return 8;
    }

    /**
     * Booking service total (before Stripe processing fee).
     */
    private function calculateBookingTotal(Booking $booking): float
    {
        $hours = $this->extractHoursFromDutyType($booking->duty_type);
        $days = (int) ($booking->duration_days ?: 0);
        $rate = (float) ($booking->hourly_rate ?: 0);

        $total = $hours * $days * $rate;

        if (!empty($booking->referral_discount_applied)) {
            $total -= $hours * $days * (float) $booking->referral_discount_applied;
        }

        return (float) $total;
    }

    // Create a SetupIntent client secret for the frontend to collect a payment method
    public function createSetupIntent(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning('Setup intent: No authenticated user');
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            Log::info('Creating setup intent for user: ' . $user->id . ' (' . $user->email . ')');

            // Check Stripe configuration
            if (empty(env('STRIPE_SECRET'))) {
                Log::error('STRIPE_SECRET is not configured');
                return response()->json(['message' => 'Payment system not configured'], 500);
            }

            $customerId = $this->ensureCustomer($user);
            
            Log::info('Customer ID obtained: ' . $customerId);

            $intent = $this->stripe->setupIntents->create([
                'customer' => $customerId,
                'usage' => 'off_session',
                'payment_method_types' => ['card'], // Explicitly specify card payments
            ]);

            Log::info('Setup intent created successfully: ' . $intent->id);

            return response()->json([
                'success' => true,
                'client_secret' => $intent->client_secret
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error in setup intent: ' . $e->getMessage(), [
                'error_type' => get_class($e),
                'error_code' => $e->getStripeCode(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Stripe API error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Setup intent creation failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create setup intent: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a PaymentIntent for a booking payment
     * This is used when the client wants to pay for a booking
     * 
     * SECURITY: Calculates amount server-side, uses row locking to prevent race conditions
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                Log::warning('Payment intent: No authenticated user');
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            Log::info('Creating payment intent for user: ' . $user->id . ' (' . $user->email . ')');

            $request->validate([
                'booking_id' => 'required|integer|exists:bookings,id',
            ]);

            $bookingId = $request->booking_id;

            // SECURITY: Use database transaction with row-level locking
            return DB::transaction(function () use ($bookingId, $user) {
                // Lock the booking row to prevent concurrent payment intents
                $booking = Booking::where('id', $bookingId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Verify booking belongs to user (client_id in bookings refers to user_id)
                if ($booking->client_id !== $user->id) {
                    Log::warning('Unauthorized payment attempt', [
                        'user_id' => $user->id,
                        'booking_id' => $bookingId,
                        'booking_client_id' => $booking->client_id
                    ]);
                    return response()->json(['message' => 'Unauthorized - booking does not belong to you'], 403);
                }

                // Check if already paid
                if ($booking->payment_status === 'paid') {
                    return response()->json(['message' => 'Booking has already been paid'], 400);
                }

                // Check Stripe configuration - use config() instead of env()
                $stripeSecret = config('stripe.secret_key') ?: config('services.stripe.secret') ?: env('STRIPE_SECRET');
                if (empty($stripeSecret)) {
                    Log::error('STRIPE_SECRET is not configured');
                    return response()->json(['message' => 'Payment system not configured'], 500);
                }

                $customerId = $this->ensureCustomer($user);
                
                // SECURITY: Calculate amount server-side, never trust client-provided amount
                $targetAmount = (float) $this->calculateBookingTotal($booking);
                
                // SECURITY: Validate calculated amount is positive
                if ($targetAmount <= 0) {
                    Log::alert('Zero/negative amount calculated for payment intent', [
                        'booking_id' => $booking->id,
                        'calculated_amount' => $targetAmount
                    ]);
                    return response()->json(['message' => 'Invalid booking amount'], 400);
                }
                
                // Calculate with processing fee
                $adjustedAmount = $this->calculateAdjustedTotal($targetAmount, 'US');
                $amountInCents = (int) round($adjustedAmount * 100);

                Log::info('Creating payment intent', [
                    'customer_id' => $customerId,
                    'amount' => $amountInCents,
                    'booking_id' => $bookingId
                ]);

                // Create the payment intent
                $paymentIntent = $this->stripe->paymentIntents->create([
                    'amount' => $amountInCents,
                    'currency' => 'usd',
                    'customer' => $customerId,
                    'metadata' => [
                        'booking_id' => $bookingId,
                        'user_id' => $user->id,
                        'client_id' => $user->id // Same as user_id (booking.client_id = user.id)
                    ],
                    'automatic_payment_methods' => [
                        'enabled' => true,
                    ],
                    'description' => 'Booking #' . $bookingId . ' - ' . $booking->service_type,
                ]);

                Log::info('Payment intent created: ' . $paymentIntent->id);

                return response()->json([
                    'success' => true,
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id
                ]);
            });

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error creating payment intent: ' . $e->getMessage(), [
                'error_type' => get_class($e),
                'error_code' => $e->getStripeCode(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Stripe API error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Payment intent creation failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent: ' . $e->getMessage()
            ], 500);
        }
    }

    // List saved payment methods (cards)
    public function listPaymentMethods(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $customerId = $this->ensureCustomer($user);

        $methods = $this->stripe->paymentMethods->all([
            'customer' => $customerId,
            'type' => 'card',
        ]);

        // Return both 'data' and 'payment_methods' for compatibility
        return response()->json([
            'success' => true,
            'data' => $methods->data,
            'payment_methods' => $methods->data
        ]);
    }

    // Alias for listPaymentMethods for route compatibility
    public function getPaymentMethods(Request $request)
    {
        return $this->listPaymentMethods($request);
    }

    // Attach a payment method (from SetupIntent) to the customer and set as default
    public function attachPaymentMethod(Request $request)
    {
        $request->validate(['payment_method' => 'required|string']);

        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $customerId = $this->ensureCustomer($user);

        try {
            $pm = $this->stripe->paymentMethods->attach($request->payment_method, [
                'customer' => $customerId,
            ]);

            $this->stripe->customers->update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $pm->id,
                ],
            ]);

            return response()->json(['payment_method' => $pm]);
        } catch (\Exception $e) {
            Log::error('attachPaymentMethod error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // Detach a payment method
    public function detachPaymentMethod(Request $request, $pmId)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        try {
            $detached = $this->stripe->paymentMethods->detach($pmId);
            return response()->json(['detached' => $detached]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Charge a saved payment method for a booking
     * POST /api/stripe/charge-saved-method
     * 
     * SECURITY: Uses database transaction with row locking to prevent:
     * - Race conditions (double payment on simultaneous requests)
     * - Data inconsistency (partial updates if any step fails)
     */
    public function chargeSavedMethod(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        try {
            $request->validate([
                'payment_method_id' => 'required|string',
                'booking_id' => 'required|integer|exists:bookings,id',
            ]);

            // SECURITY: Use database transaction with row-level locking
            return DB::transaction(function () use ($request, $user) {
                // Lock the booking row to prevent concurrent payments
                $booking = Booking::where('id', $request->booking_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Verify booking belongs to user
                if ($booking->client_id !== $user->id) {
                    Log::warning('Unauthorized charge attempt', [
                        'user_id' => $user->id,
                        'booking_id' => $request->booking_id,
                    ]);
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }

                // Check if already paid (with lock, this is now race-condition safe)
                if ($booking->payment_status === 'paid') {
                    return response()->json(['success' => false, 'message' => 'Booking has already been paid'], 400);
                }

                $customerId = $this->ensureCustomer($user);

                // Calculate target total server-side (never trust client amount)
                $targetAmount = (float) $this->calculateBookingTotal($booking);
                
                // SECURITY: Validate calculated amount is positive
                if ($targetAmount <= 0) {
                    Log::alert('Zero/negative amount calculated', [
                        'booking_id' => $booking->id,
                        'calculated_amount' => $targetAmount
                    ]);
                    return response()->json(['success' => false, 'message' => 'Invalid booking amount'], 400);
                }

                // Determine card country (best-effort). If unavailable, default US.
                $cardCountry = 'US';
                try {
                    $pm = $this->stripe->paymentMethods->retrieve($request->payment_method_id, []);
                    if ($pm && isset($pm->card) && isset($pm->card->country) && is_string($pm->card->country)) {
                        $cardCountry = strtoupper($pm->card->country);
                    }
                } catch (\Exception $e) {
                    // keep default
                }

                $processingFee = $this->calculateProcessingFee($targetAmount, $cardCountry);
                $adjustedAmount = $this->calculateAdjustedTotal($targetAmount, $cardCountry);
                $amountInCents = (int) round($adjustedAmount * 100);

                Log::info('Charging saved payment method', [
                    'user_id' => $user->id,
                    'booking_id' => $request->booking_id,
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $amountInCents
                ]);

                // SECURITY: Idempotency key prevents duplicate charges on network retry
                $idempotencyKey = 'charge_booking_' . $booking->id . '_' . $user->id . '_' . now()->format('Ymd');

                // Create and confirm Payment Intent with saved payment method
                $paymentIntent = $this->stripe->paymentIntents->create([
                    'amount' => $amountInCents,
                    'currency' => 'usd',
                    'customer' => $customerId,
                    'payment_method' => $request->payment_method_id,
                    'off_session' => true,
                    'confirm' => true,
                    'metadata' => [
                        'booking_id' => $request->booking_id,
                        'user_id' => $user->id,
                        'client_id' => $user->id
                    ],
                    'description' => 'Booking #' . $request->booking_id . ' - ' . $booking->service_type,
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);

                Log::info('Payment processed successfully', [
                    'payment_intent_id' => $paymentIntent->id,
                    'booking_id' => $request->booking_id,
                    'status' => $paymentIntent->status
                ]);

                // Update booking payment status and enable recurring by default
                $booking->update([
                    'payment_status' => 'paid',
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'payment_date' => now(),
                    'recurring_service' => true, // Auto-enable recurring for paid bookings
                    'auto_pay_enabled' => true, // Enable auto-pay with saved card
                    'recurring_status' => 'active', // Set recurring status to active
                ]);

                // Create payment record
                \App\Models\Payment::create([
                    'client_id' => $user->id,
                    'booking_id' => $booking->id,
                    'transaction_id' => $paymentIntent->id,
                    'amount' => $amountInCents / 100,
                    'processing_fee' => $processingFee,
                    'status' => 'completed',
                    'payment_method' => 'credit_card',
                    'notes' => 'Booking payment using saved card for #' . $booking->id,
                    'paid_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful! Auto-renewal has been enabled for this contract.',
                    'payment_intent_id' => $paymentIntent->id,
                    'recurring_enabled' => true
                ]);
            });

        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Stripe card error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Card declined: ' . $e->getMessage()
            ], 400);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Create a subscription for a booking (creates price and subscription)
    public function createSubscription(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'amount' => 'required|integer|min:100', // In cents
            'interval' => 'required|string|in:month,week,year'
        ]);

        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        $booking = Booking::findOrFail($request->booking_id);
        
        // Verify the booking belongs to this client
        if ($booking->client_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if booking already has a subscription
        if ($booking->stripe_subscription_id) {
            return response()->json(['message' => 'Booking already has an active subscription'], 400);
        }

        $customerId = $this->ensureCustomer($user);

        try {
            // Create a product and price for this booking
            $product = $this->stripe->products->create([
                'name' => 'Caregiving Service - Booking #' . $booking->id,
                'description' => 'Recurring payment for booking #' . $booking->id,
                'metadata' => [
                    'booking_id' => $booking->id,
                    'client_id' => $user->id
                ]
            ]);

            $price = $this->stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => $request->amount,
                'currency' => 'usd',
                'recurring' => [
                    'interval' => $request->interval,
                ],
                'metadata' => [
                    'booking_id' => $booking->id
                ]
            ]);

            // Create subscription
            $subscription = $this->stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => [['price' => $price->id]],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'booking_id' => $booking->id,
                    'client_id' => $user->id
                ]
            ]);

            // Update booking with subscription info
            $booking->update([
                'stripe_subscription_id' => $subscription->id,
                'stripe_price_id' => $price->id,
                'payment_type' => 'recurring',
                'auto_pay_enabled' => true,
                'next_payment_date' => $subscription->current_period_end 
                    ? date('Y-m-d H:i:s', $subscription->current_period_end) 
                    : null
            ]);

            Log::info('Subscription created for booking', [
                'booking_id' => $booking->id,
                'subscription_id' => $subscription->id
            ]);

            return response()->json([
                'success' => true,
                'subscription' => $subscription,
                'booking' => $booking->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('createSubscription error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // Cancel a subscription
    public function cancelSubscription(Request $request, $subscriptionId)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

        // Find booking with this subscription
        $booking = Booking::where('stripe_subscription_id', $subscriptionId)
            ->where('client_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Subscription not found or unauthorized'], 404);
        }

        try {
            $sub = $this->stripe->subscriptions->cancel($subscriptionId, []);
            
            // Update booking
            $booking->update([
                'auto_pay_enabled' => false,
                'payment_type' => 'one-time'
            ]);

            Log::info('Subscription canceled for booking', [
                'booking_id' => $booking->id,
                'subscription_id' => $subscriptionId
            ]);

            return response()->json([
                'success' => true,
                'canceled' => $sub,
                'booking' => $booking->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('cancelSubscription error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
