<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StripePaymentService;
use App\Services\PaymentFeeService;
use App\Services\AuditLogService;
use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\TimeTracking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * @deprecated This controller is deprecated as of January 28, 2026.
 * 
 * USE THE NEW CONTROLLERS INSTEAD:
 * - App\Http\Controllers\Stripe\ClientStripeController (client payments)
 * - App\Http\Controllers\Stripe\CaregiverStripeController (caregiver Connect)
 * - App\Http\Controllers\Stripe\HousekeeperStripeController (housekeeper Connect)
 * - App\Http\Controllers\Stripe\AdminStripeController (admin operations)
 * 
 * New API routes use the v2/ prefix:
 * - POST /api/v2/stripe/process-payment
 * - POST /api/v2/caregiver/stripe/onboard
 * - POST /api/v2/housekeeper/stripe/onboard
 * - GET /api/v2/admin/stripe/payments
 * 
 * This controller will be removed in a future release.
 * 
 * @see App\Services\Stripe\StripeClientService
 * @see App\Services\Stripe\StripeConnectService
 * @see App\Services\Stripe\StripePayoutService
 * @see App\Services\Stripe\StripeAdminService
 */
class StripeController extends Controller
{
    use ApiResponseTrait;
    protected $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Handle incoming Stripe webhook
     * Delegates to StripeWebhookController
     * POST /api/stripe/webhook
     */
    public function webhook(Request $request)
    {
        $webhookController = app(\App\Http\Controllers\StripeWebhookController::class);
        return $webhookController->handleWebhook($request);
    }

    /**
     * ===========================
     * CLIENT ENDPOINTS
     * ===========================
     */

    /**
     * Create Setup Intent for client to save card
     * POST /api/stripe/create-setup-intent
     */
    public function createSetupIntent(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->stripeService->createSetupIntent($user);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Save payment method after Setup Intent confirms
     * POST /api/stripe/save-payment-method
     */
    public function savePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string'
        ]);

        $user = Auth::user();
        
        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            // Attach payment method to customer
            $stripe->paymentMethods->attach(
                $request->payment_method_id,
                ['customer' => $user->stripe_customer_id]
            );

            // Set as default
            $stripe->customers->update(
                $user->stripe_customer_id,
                ['invoice_settings' => ['default_payment_method' => $request->payment_method_id]]
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment method saved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Process payment using a payment method
     * POST /api/stripe/setup-intent
     * Used by PaymentPage.vue to charge the card for a booking
     * 
     * SECURITY: Uses database transaction with row locking to prevent:
     * - Race conditions (double payment on simultaneous requests)
     * - Data inconsistency (partial updates if any step fails)
     */
    public function processPaymentWithMethod(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $request->validate([
                'payment_method_id' => 'required|string',
                'booking_id' => 'required|integer',
            ]);

            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            // Ensure customer exists
            if (!$user->stripe_customer_id) {
                $customer = $stripe->customers->create([
                    'email' => $user->email,
                    'name' => $user->name ?? $user->email,
                    'metadata' => ['user_id' => $user->id]
                ]);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            }

            // SECURITY: Use database transaction with row-level locking
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user, $stripe) {
                // Lock the booking row to prevent concurrent payments
                $booking = \App\Models\Booking::where('id', $request->booking_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Verify booking belongs to user (client_id in bookings refers to user_id)
                if ($booking->client_id !== $user->id) {
                    Log::warning('Unauthorized payment attempt', [
                        'user_id' => $user->id,
                        'booking_id' => $request->booking_id,
                        'booking_client_id' => $booking->client_id
                    ]);
                    return response()->json(['success' => false, 'message' => 'Unauthorized - This booking does not belong to you'], 403);
                }

                // Check if already paid (race-condition safe with lock)
                if ($booking->payment_status === 'paid') {
                    return response()->json(['success' => false, 'message' => 'Booking has already been paid'], 400);
                }

                // Attach payment method to customer (if not already)
                try {
                    $stripe->paymentMethods->attach(
                        $request->payment_method_id,
                        ['customer' => $user->stripe_customer_id]
                    );
                } catch (\Stripe\Exception\CardException $e) {
                    // Payment method may already be attached, continue
                    Log::info('Payment method attach skipped: ' . $e->getMessage());
                }

                // Calculate target total server-side (never trust client amount)
                $targetAmount = (float) app(\App\Http\Controllers\BookingController::class)->calculateBookingTotal($booking);

                // SECURITY: Validate calculated amount is positive
                if ($targetAmount <= 0) {
                    Log::alert('Zero/negative amount calculated', [
                        'booking_id' => $booking->id,
                        'calculated_amount' => $targetAmount
                    ]);
                    return response()->json(['success' => false, 'message' => 'Invalid booking amount'], 400);
                }

                // Determine card country (best-effort before charge). If unavailable, default US.
                $cardCountry = 'US';
                try {
                    $pm = $stripe->paymentMethods->retrieve($request->payment_method_id, []);
                    if ($pm && isset($pm->card) && isset($pm->card->country) && is_string($pm->card->country)) {
                        $cardCountry = strtoupper($pm->card->country);
                    }
                } catch (\Exception $e) {
                    // keep default
                }

                $processingFee = $this->calculateProcessingFee($targetAmount, $cardCountry);
                $adjustedAmount = $this->calculateAdjustedTotal($targetAmount, $cardCountry);
                $amountInCents = (int) round($adjustedAmount * 100);

                // SECURITY: Idempotency key prevents duplicate charges on network retry
                $idempotencyKey = 'payment_' . $booking->id . '_' . $user->id . '_' . now()->format('Ymd');

                // Create and confirm Payment Intent with SCA/3D Secure enforcement
                $paymentIntent = $stripe->paymentIntents->create([
                    'amount' => $amountInCents,
                    'currency' => 'usd',
                    'customer' => $user->stripe_customer_id,
                    'payment_method' => $request->payment_method_id,
                    'off_session' => true,
                    'confirm' => true,
                    // SECURITY: Enforce 3D Secure for SCA compliance (EU PSD2)
                    'payment_method_options' => [
                        'card' => [
                            'request_three_d_secure' => 'automatic',
                        ],
                    ],
                    'metadata' => [
                        'booking_id' => $request->booking_id,
                        'user_id' => $user->id,
                        'client_id' => $user->id // Same as user_id (booking.client_id = user.id)
                    ],
                    'description' => 'Booking #' . $request->booking_id . ' - ' . $booking->service_type,
                    // Enable Stripe's hosted receipt emails
                    'receipt_email' => $user->email,
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);

                Log::info('Payment processed successfully', [
                    'payment_intent_id' => $paymentIntent->id,
                    'booking_id' => $request->booking_id,
                    'amount' => $amountInCents
                ]);

                // Update booking payment status
                $booking->update([
                    'payment_status' => 'paid',
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'payment_date' => now(),
                ]);

                // Create payment record
                \App\Models\Payment::create([
                    'client_id' => $user->id,
                    'booking_id' => $booking->id,
                    'transaction_id' => $paymentIntent->id,
                    'amount' => $amountInCents / 100, // Convert from cents
                    'processing_fee' => $processingFee,
                    'status' => 'completed',
                    'payment_method' => 'credit_card',
                    'notes' => 'Booking payment for #' . $booking->id,
                    'paid_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'payment_intent_id' => $paymentIntent->id
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

    /**
     * Get client's saved payment methods
     * GET /api/stripe/payment-methods
     */
    public function getPaymentMethods(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->stripe_customer_id) {
            return response()->json(['payment_methods' => []]);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            $paymentMethods = $stripe->paymentMethods->all([
                'customer' => $user->stripe_customer_id,
                'type' => 'card',
            ]);


            $methods = array_map(function($pm) {
                return [
                    'id' => $pm->id,
                    'card' => [
                        'brand' => $pm->card->brand,
                        'last4' => $pm->card->last4,
                        'exp_month' => $pm->card->exp_month,
                        'exp_year' => $pm->card->exp_year,
                    ],
                    // Legacy flat format for backward compatibility
                    'brand' => $pm->card->brand,
                    'last4' => $pm->card->last4,
                    'exp_month' => $pm->card->exp_month,
                    'exp_year' => $pm->card->exp_year,
                ];
            }, $paymentMethods->data);

            return response()->json([
                'success' => true,
                'payment_methods' => $methods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete a saved payment method
     * DELETE /api/stripe/payment-methods/{paymentMethodId}
     */
    public function deletePaymentMethod(Request $request, $paymentMethodId)
    {
        $user = Auth::user();
        
        if (!$user->stripe_customer_id) {
            return response()->json([
                'success' => false,
                'message' => 'No payment methods found'
            ], 400);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            // First, verify the payment method belongs to this customer
            $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);
            
            if ($paymentMethod->customer !== $user->stripe_customer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method not found'
                ], 404);
            }
            
            // Detach the payment method from the customer
            $stripe->paymentMethods->detach($paymentMethodId);
            
            Log::info("Payment method deleted", [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted successfully'
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::warning("Failed to delete payment method - not found", [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error("Failed to delete payment method", [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method'
            ], 500);
        }
    }

    /**
     * ===========================
     * CAREGIVER ENDPOINTS
     * ===========================
     */

    /**
     * Create Stripe Connect onboarding link
     * POST /api/stripe/create-onboarding-link
     */
    public function createOnboardingLink(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $caregiver = Caregiver::where('user_id', $user->id)->first();
        
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver profile not found'], 404);
        }

        $result = $this->stripeService->createOnboardingLink($caregiver);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'url' => $result['url']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Create Account Session for embedded onboarding
     * POST /api/stripe/create-account-session
     */
    public function createAccountSession(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $caregiver = Caregiver::where('user_id', $user->id)->first();
        
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver profile not found'], 404);
        }

        $result = $this->stripeService->createAccountSession($caregiver);

        return response()->json($result);
    }

    /**
     * Connect bank account via custom form
     * POST /api/stripe/connect-bank-account
     */
    public function connectBankAccount(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'accountHolderName' => 'required|string|max:255',
            'routingNumber' => 'required|string|size:9',
            'accountNumber' => 'required|string|min:4|max:17',
            'accountType' => 'required|in:checking,savings'
        ]);

        $caregiver = Caregiver::where('user_id', $user->id)->first();
        
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver profile not found'], 404);
        }

        $result = $this->stripeService->addBankAccountToConnect($caregiver, [
            'accountHolderName' => $request->accountHolderName,
            'routingNumber' => $request->routingNumber,
            'accountNumber' => $request->accountNumber,
            'accountType' => $request->accountType
        ]);

        return response()->json($result);
    }

    /**
     * Connect payout method (Bank, Card, Alipay, Cash App)
     * POST /api/stripe/connect-payout-method
     */
    public function connectPayoutMethod(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $caregiver = Caregiver::where('user_id', $user->id)->first();
        
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver profile not found'], 404);
        }

        $payoutMethod = $request->input('payoutMethod');

        switch ($payoutMethod) {
            case 'bank':
                $request->validate([
                    'accountHolderName' => 'required|string|max:255',
                    'routingNumber' => 'required|string|size:9',
                    'accountNumber' => 'required|string|min:4|max:17',
                    'accountType' => 'required|in:checking,savings'
                ]);

                // For now, save bank info directly to user record (test mode)
                $user->stripe_connect_id = 'test_' . uniqid();
                $user->bank_account_last_four = substr($request->accountNumber, -4);
                $user->bank_name = 'Test Bank (Routing: ' . $request->routingNumber . ')';
                $user->save();

                $result = [
                    'success' => true,
                    'message' => 'Bank account connected successfully'
                ];
                break;

            case 'card':
                $request->validate([
                    'cardholderName' => 'required|string|max:255',
                    'cardNumber' => 'required|string',
                    'expiryDate' => 'required|string',
                    'cvv' => 'required|string|min:3|max:4'
                ]);

                // For now, save card info directly to user record (test mode)
                $user->stripe_connect_id = 'test_' . uniqid();
                $user->bank_account_last_four = substr($request->cardNumber, -4);
                $user->bank_name = 'Debit Card (**** ' . substr($request->cardNumber, -4) . ')';
                $user->save();

                $result = [
                    'success' => true,
                    'message' => 'Debit card connected successfully'
                ];
                break;

            case 'alipay':
                $request->validate([
                    'accountName' => 'required|string|max:255',
                    'alipayId' => 'required|string'
                ]);

                $result = $this->stripeService->addAlipayToConnect($caregiver, [
                    'accountName' => $request->accountName,
                    'alipayId' => $request->alipayId
                ]);
                break;

            case 'cashapp':
                $request->validate([
                    'accountName' => 'required|string|max:255',
                    'cashtag' => 'required|string'
                ]);

                $result = $this->stripeService->addCashAppToConnect($caregiver, [
                    'accountName' => $request->accountName,
                    'cashtag' => $request->cashtag
                ]);
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payout method'
                ], 400);
        }

        return response()->json($result);
    }

    /**
     * Check onboarding status
     * GET /api/stripe/onboarding-status
     */
    public function checkOnboardingStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $caregiver = Caregiver::where('user_id', $user->id)->first();
        
        if (!$caregiver) {
            return response()->json(['error' => 'Caregiver profile not found'], 404);
        }

        $isComplete = $this->stripeService->isConnectAccountComplete($caregiver);

        return response()->json([
            'complete' => $isComplete,
            'has_connect_id' => !empty($caregiver->stripe_connect_id)
        ]);
    }

    /**
     * ===========================
     * ADMIN ENDPOINTS
     * ===========================
     */

    /**
     * Manually trigger payment for a time tracking entry
     * POST /api/stripe/admin/process-payment
     */
    public function adminProcessPayment(Request $request)
    {
        // Only admins can call this
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'time_tracking_id' => 'required|exists:time_trackings,id'
        ]);

        $timeTracking = TimeTracking::find($request->time_tracking_id);

        // Step 1: Charge client
        $chargeResult = $this->stripeService->chargeClientForTimeTracking($timeTracking);
        
        if (!$chargeResult['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to charge client: ' . $chargeResult['error']
            ], 400);
        }

        // Step 2: Transfer to caregiver
        $transferResult = $this->stripeService->transferToCaregiver($timeTracking);

        if (!$transferResult['success']) {
            return response()->json([
                'success' => false,
                'error' => 'Client charged but caregiver transfer failed: ' . $transferResult['error']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'charge_id' => $chargeResult['charge_id'],
            'transfer_id' => $transferResult['transfer_id']
        ]);
    }

    /**
     * Process all pending payments for the week
     * POST /api/stripe/admin/process-weekly-payouts
     */
    public function adminProcessWeeklyPayouts(Request $request)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $results = $this->stripeService->processWeeklyPayouts();

        return response()->json([
            'success' => true,
            'processed' => $results['success'],
            'failed' => $results['failed'],
            'errors' => $results['errors']
        ]);
    }

    /**
     * View payment history for a user
     * GET /api/stripe/admin/payment-history/{user_id}
     */
    public function adminPaymentHistory(Request $request, $userId)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $userId)->first();
            
            $payments = TimeTracking::where('caregiver_id', $caregiver->id)
                ->whereNotNull('stripe_charge_id')
                ->with(['booking', 'client'])
                ->orderBy('work_date', 'desc')
                ->get()
                ->map(function($t) {
                    return [
                        'id' => $t->id,
                        'date' => $t->work_date,
                        'hours' => $t->hours_worked,
                        'earnings' => $t->caregiver_earnings,
                        'status' => $t->payment_status,
                        'paid_at' => $t->paid_at,
                        'stripe_transfer_id' => $t->stripe_transfer_id
                    ];
                });

            return response()->json(['payments' => $payments]);
        }

        // Client payment history
        if ($user->user_type === 'client') {
            $payments = TimeTracking::whereHas('booking', function($q) use ($userId) {
                $q->where('client_id', $userId);
            })
            ->whereNotNull('stripe_charge_id')
            ->orderBy('work_date', 'desc')
            ->get()
            ->map(function($t) {
                return [
                    'id' => $t->id,
                    'date' => $t->work_date,
                    'hours' => $t->hours_worked,
                    'amount_charged' => $t->total_client_charge,
                    'stripe_charge_id' => $t->stripe_charge_id,
                    'charged_at' => $t->client_charged_at
                ];
            });

            return response()->json(['payments' => $payments]);
        }

        return response()->json(['payments' => []]);
    }

    /**
     * ===========================
     * WEBHOOKS
     * ===========================
     */

    /**
     * Handle Stripe webhooks
     * POST /stripe/webhook
     * 
     * NOTE: This is a LEGACY handler. The primary webhook handler is in
     * StripeWebhookController.php which has more complete event handling.
     * This method is kept for backward compatibility with any existing
     * webhook endpoints that may be configured.
     * 
     * SECURITY: Validates webhook signature to prevent fake payment injections
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');

        // SECURITY: Reject webhooks without valid signature
        if (empty($webhookSecret)) {
            Log::error('Stripe webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe webhook invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Stripe webhook invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info('Stripe webhook received (legacy handler): ' . $event->type, ['event_id' => $event->id]);

        // Handle events
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                Log::info('Payment succeeded', ['payment_intent' => $paymentIntent->id]);
                
                // Update booking if metadata contains booking_id
                if (!empty($paymentIntent->metadata->booking_id)) {
                    $booking = \App\Models\Booking::find($paymentIntent->metadata->booking_id);
                    if ($booking && $booking->payment_status !== 'paid') {
                        $booking->update([
                            'payment_status' => 'paid',
                            'stripe_payment_intent_id' => $paymentIntent->id,
                            'payment_date' => now(),
                        ]);
                        Log::info('Booking marked as paid via webhook', ['booking_id' => $booking->id]);
                    }
                }
                break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                Log::error('Payment failed', ['payment_intent' => $paymentIntent->id]);
                
                // SECURITY: Alert on failed payments for monitoring
                Log::alert('Payment intent failed - requires review', [
                    'payment_intent_id' => $paymentIntent->id,
                    'amount' => $paymentIntent->amount / 100,
                    'failure_message' => $paymentIntent->last_payment_error->message ?? 'Unknown',
                ]);
                
                // Update booking status if applicable
                if (!empty($paymentIntent->metadata->booking_id)) {
                    $booking = \App\Models\Booking::find($paymentIntent->metadata->booking_id);
                    if ($booking) {
                        $booking->update(['payment_status' => 'failed']);
                    }
                }
                break;

            case 'account.updated':
                // Update Connect account status
                $account = $event->data->object;
                $caregiver = Caregiver::where('stripe_connect_id', $account->id)->first();
                if ($caregiver) {
                    $caregiver->update([
                        'stripe_charges_enabled' => $account->charges_enabled,
                        'stripe_payouts_enabled' => $account->payouts_enabled
                    ]);
                    Log::info('Caregiver Connect account updated', [
                        'caregiver_id' => $caregiver->id,
                        'charges_enabled' => $account->charges_enabled,
                        'payouts_enabled' => $account->payouts_enabled,
                    ]);
                }
                break;
                
            default:
                Log::info('Unhandled webhook event type: ' . $event->type);
        }

        return response()->json(['success' => true]);
    }

    /**
     * ===========================
     * MARKETING STAFF ENDPOINTS
     * ===========================
     */

    /**
     * Connect payout method for marketing staff (Bank, Card, Alipay, Cash App)
     * POST /api/stripe/connect-bank-account-marketing
     */
    public function connectMarketingBankAccount(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->user_type !== 'marketing') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payoutMethod = $request->input('payoutMethod', 'bank');

        switch ($payoutMethod) {
            case 'bank':
                $request->validate([
                    'accountHolderName' => 'required|string|max:255',
                    'routingNumber' => 'required|string|size:9',
                    'accountNumber' => 'required|string|min:4|max:17',
                    'accountType' => 'required|in:checking,savings'
                ]);

                $result = $this->stripeService->addMarketingBankAccount($user, $request->only([
                    'accountHolderName',
                    'routingNumber',
                    'accountNumber',
                    'accountType'
                ]));

                if ($result['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Bank account connected successfully'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);

            case 'card':
                $request->validate([
                    'cardholderName' => 'required|string|max:255',
                    'cardNumber' => 'required|string',
                    'expiryDate' => 'required|string',
                    'cvv' => 'required|string|min:3|max:4'
                ]);

                $user->stripe_connect_id = $user->stripe_connect_id ?: 'test_' . uniqid();
                $user->bank_account_last_four = substr(str_replace(' ', '', $request->cardNumber), -4);
                $user->bank_name = 'Debit Card (**** ' . substr(str_replace(' ', '', $request->cardNumber), -4) . ')';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Debit card connected successfully'
                ]);

            case 'alipay':
                $request->validate([
                    'accountName' => 'required|string|max:255',
                    'alipayId' => 'required|string'
                ]);

                $user->stripe_connect_id = $user->stripe_connect_id ?: 'test_' . uniqid();
                $user->bank_name = 'Alipay (' . $request->input('alipayId') . ')';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Alipay connected successfully'
                ]);

            case 'cashapp':
                $request->validate([
                    'accountName' => 'required|string|max:255',
                    'cashtag' => 'required|string'
                ]);

                $user->stripe_connect_id = $user->stripe_connect_id ?: 'test_' . uniqid();
                $user->bank_name = 'Cash App ($' . $request->input('cashtag') . ')';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Cash App Pay connected successfully'
                ]);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payout method'
                ], 400);
        }
    }

    /**
     * Pay marketing commission
     * POST /api/stripe/admin/pay-marketing-commission/{userId}
     */
    public function payMarketingCommission(Request $request, $userId)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // SECURITY: Wrap in transaction with row locking to prevent race conditions
        return DB::transaction(function () use ($userId) {
            $marketingUser = User::findOrFail($userId);

            if ($marketingUser->user_type !== 'marketing') {
                return response()->json(['error' => 'User is not marketing staff'], 400);
            }

            // SECURITY: Lock rows for update to prevent concurrent payments
            $pendingCommissions = TimeTracking::where('marketing_partner_id', $userId)
                ->where('payment_status', 'pending')
                ->whereNotNull('marketing_partner_commission')
                ->where('marketing_partner_commission', '>', 0)
                ->lockForUpdate()
                ->get();

            if ($pendingCommissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending commissions to pay'
                ], 400);
            }

            $totalAmount = $pendingCommissions->sum('marketing_partner_commission');

            // Transfer to marketing staff (StripePaymentService now has idempotency)
            $result = $this->stripeService->transferToMarketing($marketingUser, $totalAmount, [
                'commission_count' => $pendingCommissions->count(),
                'payment_date' => now()->toDateString(),
                'time_tracking_id' => $pendingCommissions->pluck('id')->implode('_')
            ]);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer failed: ' . $result['error']
                ], 400);
            }

            // Mark commissions as paid (inside transaction)
            TimeTracking::whereIn('id', $pendingCommissions->pluck('id'))
                ->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'marketing_commission_paid_at' => now(),
                    'marketing_commission_stripe_transfer_id' => $result['transfer_id']
                ]);

            Log::info('Marketing commission paid via StripeController', [
                'user_id' => $userId,
                'amount' => $totalAmount,
                'transfer_id' => $result['transfer_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Commission paid successfully',
                'amount' => $totalAmount,
                'transfer_id' => $result['transfer_id'],
                'entries_paid' => $pendingCommissions->count()
            ]);
        }); // End transaction
    }

    /**
     * ===========================
     * TRAINING CENTER ENDPOINTS
     * ===========================
     */

    /**
     * Connect payout method for training center (Bank, Card, Alipay, Cash App)
     * POST /api/stripe/connect-bank-account-training
     */
    public function connectTrainingBankAccount(Request $request)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->user_type, ['training', 'training_center'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $payoutMethod = $request->input('payoutMethod', 'bank');

        switch ($payoutMethod) {
            case 'bank':
                $request->validate([
                    'accountHolderName' => 'required|string|max:255',
                    'routingNumber' => 'required|string|size:9',
                    'accountNumber' => 'required|string|min:4|max:17',
                    'accountType' => 'required|in:checking,savings'
                ]);

                $result = $this->stripeService->addTrainingBankAccount($user, $request->only([
                    'accountHolderName',
                    'routingNumber',
                    'accountNumber',
                    'accountType'
                ]));

                if ($result['success']) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Bank account connected successfully'
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => $result['error']
                ], 400);

            case 'card':
                $request->validate([
                    'cardholderName' => 'required|string|max:255',
                    'cardNumber' => 'required|string',
                    'expiryDate' => 'required|string',
                    'cvv' => 'required|string|min:3|max:4'
                ]);

                $user->stripe_connect_id = $user->stripe_connect_id ?: 'test_' . uniqid();
                $user->bank_account_last_four = substr(str_replace(' ', '', $request->cardNumber), -4);
                $user->bank_name = 'Debit Card (**** ' . substr(str_replace(' ', '', $request->cardNumber), -4) . ')';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Debit card connected successfully'
                ]);

            case 'alipay':
                $request->validate([
                    'accountName' => 'required|string|max:255',
                    'alipayId' => 'required|string'
                ]);

                $user->stripe_connect_id = $user->stripe_connect_id ?: 'test_' . uniqid();
                $user->bank_name = 'Alipay (' . $request->input('alipayId') . ')';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Alipay connected successfully'
                ]);

            case 'cashapp':
                $request->validate([
                    'accountName' => 'required|string|max:255',
                    'cashtag' => 'required|string'
                ]);

                $user->stripe_connect_id = $user->stripe_connect_id ?: 'test_' . uniqid();
                $user->bank_name = 'Cash App ($' . $request->input('cashtag') . ')';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Cash App Pay connected successfully'
                ]);

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payout method'
                ], 400);
        }
    }

    /**
     * Pay training commission
     * POST /api/stripe/admin/pay-training-commission/{userId}
     */
    public function payTrainingCommission(Request $request, $userId)
    {
        if (!Auth::user() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // SECURITY: Wrap in transaction with row locking to prevent race conditions
        return DB::transaction(function () use ($userId) {
            $trainingUser = User::findOrFail($userId);

            if (!in_array($trainingUser->user_type, ['training', 'training_center'])) {
                return response()->json(['error' => 'User is not training center'], 400);
            }

            // SECURITY: Lock rows for update to prevent concurrent payments
            $pendingCommissions = TimeTracking::where('training_center_user_id', $userId)
                ->where('payment_status', 'pending')
                ->whereNotNull('training_center_commission')
                ->where('training_center_commission', '>', 0)
                ->lockForUpdate()
                ->get();

            if ($pendingCommissions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pending commissions to pay'
                ], 400);
            }

            $totalAmount = $pendingCommissions->sum('training_center_commission');

            // Transfer to training center (StripePaymentService now has idempotency)
            $result = $this->stripeService->transferToTraining($trainingUser, $totalAmount, [
                'commission_count' => $pendingCommissions->count(),
                'payment_date' => now()->toDateString(),
                'time_tracking_id' => $pendingCommissions->pluck('id')->implode('_')
            ]);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transfer failed: ' . $result['error']
                ], 400);
            }

            // Mark commissions as paid (inside transaction)
            TimeTracking::whereIn('id', $pendingCommissions->pluck('id'))
                ->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'training_commission_paid_at' => now(),
                    'training_commission_stripe_transfer_id' => $result['transfer_id']
                ]);

            Log::info('Training commission paid via StripeController', [
                'user_id' => $userId,
                'amount' => $totalAmount,
                'transfer_id' => $result['transfer_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Commission paid successfully',
                'amount' => $totalAmount,
                'transfer_id' => $result['transfer_id'],
                'entries_paid' => $pendingCommissions->count()
            ]);
        }); // End transaction
    }

    /**
     * ===========================
     * HOUSEKEEPER ENDPOINTS
     * ===========================
     */

    /**
     * Create Stripe Connect onboarding link for Housekeeper
     * POST /api/stripe/housekeeper/create-onboarding-link
     */
    public function createHousekeeperOnboardingLink(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $housekeeper = Housekeeper::where('user_id', $user->id)->first();
        
        if (!$housekeeper) {
            return response()->json(['error' => 'Housekeeper profile not found'], 404);
        }

        $result = $this->stripeService->createOnboardingLinkForHousekeeper($housekeeper);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'url' => $result['url']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Connect payout method for Housekeeper (Bank, Card, etc.)
     * POST /api/stripe/housekeeper/connect-payout-method
     */
    public function connectHousekeeperPayoutMethod(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $housekeeper = Housekeeper::where('user_id', $user->id)->first();
        
        if (!$housekeeper) {
            return response()->json(['error' => 'Housekeeper profile not found'], 404);
        }

        $payoutMethod = $request->input('payoutMethod');

        switch ($payoutMethod) {
            case 'bank':
                $request->validate([
                    'accountHolderName' => 'required|string|max:255',
                    'routingNumber' => 'required|string|size:9',
                    'accountNumber' => 'required|string|min:4|max:17',
                    'accountType' => 'required|in:checking,savings'
                ]);

                // Save bank info to housekeeper record
                $housekeeper->stripe_connect_id = 'test_' . uniqid();
                $housekeeper->stripe_charges_enabled = true;
                $housekeeper->stripe_payouts_enabled = true;
                $housekeeper->save();

                // Also save to user record for display
                $user->stripe_connect_id = $housekeeper->stripe_connect_id;
                $user->bank_account_last_four = substr($request->accountNumber, -4);
                $user->bank_name = 'Test Bank (Routing: ' . $request->routingNumber . ')';
                $user->save();

                $result = [
                    'success' => true,
                    'message' => 'Bank account connected successfully'
                ];
                break;

            case 'card':
                $request->validate([
                    'cardholderName' => 'required|string|max:255',
                    'cardNumber' => 'required|string',
                    'expiryDate' => 'required|string',
                    'cvv' => 'required|string|min:3|max:4'
                ]);

                // Save card info to housekeeper record
                $housekeeper->stripe_connect_id = 'test_' . uniqid();
                $housekeeper->stripe_charges_enabled = true;
                $housekeeper->stripe_payouts_enabled = true;
                $housekeeper->save();

                // Also save to user record for display
                $user->stripe_connect_id = $housekeeper->stripe_connect_id;
                $user->bank_account_last_four = substr($request->cardNumber, -4);
                $user->bank_name = 'Debit Card (**** ' . substr($request->cardNumber, -4) . ')';
                $user->save();

                $result = [
                    'success' => true,
                    'message' => 'Debit card connected successfully'
                ];
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payout method'
                ], 400);
        }

        return response()->json($result);
    }

    /**
     * Check onboarding status for Housekeeper
     * GET /api/stripe/housekeeper/onboarding-status
     */
    public function checkHousekeeperOnboardingStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $housekeeper = Housekeeper::where('user_id', $user->id)->first();
        
        if (!$housekeeper) {
            return response()->json(['error' => 'Housekeeper profile not found'], 404);
        }

        $isComplete = !empty($housekeeper->stripe_connect_id) && 
                      $housekeeper->stripe_charges_enabled && 
                      $housekeeper->stripe_payouts_enabled;

        return response()->json([
            'complete' => $isComplete,
            'has_connect_id' => !empty($housekeeper->stripe_connect_id),
            'charges_enabled' => $housekeeper->stripe_charges_enabled ?? false,
            'payouts_enabled' => $housekeeper->stripe_payouts_enabled ?? false
        ]);
    }

    /**
     * Get housekeeper connection status
     * GET /api/stripe/housekeeper/connection-status
     */
    public function getHousekeeperConnectionStatus(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $housekeeper = Housekeeper::where('user_id', $user->id)->first();
        
        if (!$housekeeper) {
            return response()->json(['error' => 'Housekeeper profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'connected' => !empty($housekeeper->stripe_connect_id),
            'stripe_connect_id' => $housekeeper->stripe_connect_id,
            'charges_enabled' => $housekeeper->stripe_charges_enabled ?? false,
            'payouts_enabled' => $housekeeper->stripe_payouts_enabled ?? false,
            'bank_account_last_four' => $user->bank_account_last_four,
            'bank_name' => $user->bank_name
        ]);
    }

    /**
     * Remove payout method for caregiver
     * DELETE /api/caregiver/payout-method
     */
    public function removePayoutMethod(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            // Clear payout method fields
            $user->stripe_connect_id = null;
            $user->bank_account_last_four = null;
            $user->bank_name = null;
            $user->save();

            // Also clear from caregiver record if exists
            $caregiver = \App\Models\Caregiver::where('user_id', $user->id)->first();
            if ($caregiver) {
                $caregiver->stripe_connect_id = null;
                $caregiver->stripe_charges_enabled = false;
                $caregiver->stripe_payouts_enabled = false;
                $caregiver->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Payout method removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing payout method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payout method'
            ], 500);
        }
    }

    /**
     * Remove payout method for housekeeper
     * DELETE /api/housekeeper/payout-method
     */
    public function removeHousekeeperPayoutMethod(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            // Clear payout method fields
            $user->stripe_connect_id = null;
            $user->bank_account_last_four = null;
            $user->bank_name = null;
            $user->save();

            // Also clear from housekeeper record if exists
            $housekeeper = \App\Models\Housekeeper::where('user_id', $user->id)->first();
            if ($housekeeper) {
                $housekeeper->stripe_connect_id = null;
                $housekeeper->stripe_charges_enabled = false;
                $housekeeper->stripe_payouts_enabled = false;
                $housekeeper->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Payout method removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing housekeeper payout method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payout method'
            ], 500);
        }
    }

    /**
     * Remove payout method for marketing staff
     * DELETE /api/marketing/payout-method
     */
    public function removeMarketingPayoutMethod(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            if ($user->user_type !== 'marketing') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $user->stripe_connect_id = null;
            $user->bank_account_last_four = null;
            $user->bank_name = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Payout method removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing marketing payout method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payout method'
            ], 500);
        }
    }

    /**
     * Remove payout method for training center
     * DELETE /api/training/payout-method
     */
    public function removeTrainingPayoutMethod(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            if (!in_array($user->user_type, ['training', 'training_center'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $user->stripe_connect_id = null;
            $user->bank_account_last_four = null;
            $user->bank_name = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Payout method removed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing training payout method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payout method'
            ], 500);
        }
    }

    /**
     * Get payout methods for caregiver
     * GET /api/caregiver/payout-methods
     */
    public function getPayoutMethods(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            $caregiver = \App\Models\Caregiver::where('user_id', $user->id)->first();
            
            $methods = [];
            if ($user->stripe_connect_id || ($caregiver && $caregiver->stripe_connect_id)) {
                $methods[] = [
                    'id' => 1,
                    'type' => 'bank',
                    'name' => $user->bank_name ?: 'Bank Transfer (ACH)',
                    'details' => $user->bank_account_last_four ? '****' . $user->bank_account_last_four : 'Connected via Stripe',
                    'is_active' => true
                ];
            }

            return response()->json([
                'success' => true,
                'methods' => $methods,
                'balance' => [
                    'current' => 0,
                    'totalPaid' => 0,
                    'lastPayment' => 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting payout methods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payout methods'
            ], 500);
        }
    }

    /**
     * Get payout methods for housekeeper
     * GET /api/housekeeper/payout-methods
     */
    public function getHousekeeperPayoutMethods(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            $housekeeper = \App\Models\Housekeeper::where('user_id', $user->id)->first();
            
            $methods = [];
            if ($user->stripe_connect_id || ($housekeeper && $housekeeper->stripe_connect_id)) {
                $methods[] = [
                    'id' => 1,
                    'type' => 'bank',
                    'name' => $user->bank_name ?: 'Bank Transfer (ACH)',
                    'details' => $user->bank_account_last_four ? '****' . $user->bank_account_last_four : 'Connected via Stripe',
                    'is_active' => true
                ];
            }

            return response()->json([
                'success' => true,
                'methods' => $methods,
                'balance' => [
                    'current' => 0,
                    'totalPaid' => 0,
                    'lastPayment' => 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting housekeeper payout methods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payout methods'
            ], 500);
        }
    }
}
