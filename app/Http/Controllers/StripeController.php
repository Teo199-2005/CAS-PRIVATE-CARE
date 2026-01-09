<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StripePaymentService;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\TimeTracking;
use Illuminate\Support\Facades\Auth;

class StripeController extends Controller
{
    protected $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
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
                'amount' => 'required|integer|min:100', // Amount in cents
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

            // Get the booking
            $booking = \App\Models\Booking::findOrFail($request->booking_id);

            // Verify booking belongs to user (client_id in bookings refers to user_id)
            if ($booking->client_id !== $user->id) {
                Log::warning('Unauthorized payment attempt', [
                    'user_id' => $user->id,
                    'booking_id' => $request->booking_id,
                    'booking_client_id' => $booking->client_id
                ]);
                return response()->json(['success' => false, 'message' => 'Unauthorized - This booking does not belong to you'], 403);
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

            // Create and confirm Payment Intent
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $request->amount,
                'currency' => 'usd',
                'customer' => $user->stripe_customer_id,
                'payment_method' => $request->payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'metadata' => [
                    'booking_id' => $request->booking_id,
                    'user_id' => $user->id,
                    'client_id' => $user->id // Same as user_id (booking.client_id = user.id)
                ],
                'description' => 'Booking #' . $request->booking_id . ' - ' . $booking->service_type,
            ]);

            Log::info('Payment processed successfully', [
                'payment_intent_id' => $paymentIntent->id,
                'booking_id' => $request->booking_id,
                'amount' => $request->amount
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
                'amount' => $request->amount / 100, // Convert from cents
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
                    'brand' => $pm->card->brand,
                    'last4' => $pm->card->last4,
                    'exp_month' => $pm->card->exp_month,
                    'exp_year' => $pm->card->exp_year,
                ];
            }, $paymentMethods->data);

            return response()->json([
                'payment_methods' => $methods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
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
                    'cvv' => 'required|string|size:3|size:4'
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
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle events
        switch ($event->type) {
            case 'payment_intent.succeeded':
                // Log successful payment
                \Log::info('Payment succeeded', ['payment_intent' => $event->data->object->id]);
                break;

            case 'payment_intent.payment_failed':
                // Handle failed payment
                \Log::error('Payment failed', ['payment_intent' => $event->data->object->id]);
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
                }
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * ===========================
     * MARKETING STAFF ENDPOINTS
     * ===========================
     */

    /**
     * Connect bank account for marketing staff
     * POST /api/stripe/connect-bank-account-marketing
     */
    public function connectMarketingBankAccount(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'marketing') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'accountHolderName' => 'required|string',
            'routingNumber' => 'required|string|size:9',
            'accountNumber' => 'required|string',
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

        $marketingUser = User::findOrFail($userId);

        if ($marketingUser->user_type !== 'marketing') {
            return response()->json(['error' => 'User is not marketing staff'], 400);
        }

        // Get all pending commissions
        $pendingCommissions = TimeTracking::where('marketing_partner_id', $userId)
            ->where('payment_status', 'pending')
            ->whereNotNull('marketing_partner_commission')
            ->where('marketing_partner_commission', '>', 0)
            ->get();

        if ($pendingCommissions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No pending commissions to pay'
            ], 400);
        }

        $totalAmount = $pendingCommissions->sum('marketing_partner_commission');

        // Transfer to marketing staff
        $result = $this->stripeService->transferToMarketing($marketingUser, $totalAmount, [
            'commission_count' => $pendingCommissions->count(),
            'payment_date' => now()->toDateString()
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $result['error']
            ], 400);
        }

        // Mark commissions as paid
        TimeTracking::where('marketing_partner_id', $userId)
            ->where('payment_status', 'pending')
            ->whereNotNull('marketing_partner_commission')
            ->update([
                'payment_status' => 'paid',
                'paid_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Commission paid successfully',
            'amount' => $totalAmount,
            'transfer_id' => $result['transfer_id'],
            'entries_paid' => $pendingCommissions->count()
        ]);
    }

    /**
     * ===========================
     * TRAINING CENTER ENDPOINTS
     * ===========================
     */

    /**
     * Connect bank account for training center
     * POST /api/stripe/connect-bank-account-training
     */
    public function connectTrainingBankAccount(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->user_type, ['training', 'training_center'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'accountHolderName' => 'required|string',
            'routingNumber' => 'required|string|size:9',
            'accountNumber' => 'required|string',
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

        $trainingUser = User::findOrFail($userId);

        if (!in_array($trainingUser->user_type, ['training', 'training_center'])) {
            return response()->json(['error' => 'User is not training center'], 400);
        }

        // Get all pending commissions
        $pendingCommissions = TimeTracking::where('training_center_user_id', $userId)
            ->where('payment_status', 'pending')
            ->whereNotNull('training_center_commission')
            ->where('training_center_commission', '>', 0)
            ->get();

        if ($pendingCommissions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No pending commissions to pay'
            ], 400);
        }

        $totalAmount = $pendingCommissions->sum('training_center_commission');

        // Transfer to training center
        $result = $this->stripeService->transferToTraining($trainingUser, $totalAmount, [
            'commission_count' => $pendingCommissions->count(),
            'payment_date' => now()->toDateString()
        ]);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Transfer failed: ' . $result['error']
            ], 400);
        }

        // Mark commissions as paid
        TimeTracking::where('training_center_user_id', $userId)
            ->where('payment_status', 'pending')
            ->whereNotNull('training_center_commission')
            ->update([
                'payment_status' => 'paid',
                'paid_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Commission paid successfully',
            'amount' => $totalAmount,
            'transfer_id' => $result['transfer_id'],
            'entries_paid' => $pendingCommissions->count()
        ]);
    }
}
