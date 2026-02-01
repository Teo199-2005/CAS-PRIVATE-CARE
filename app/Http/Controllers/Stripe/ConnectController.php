<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\StripePaymentService;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Handles Stripe Connect onboarding for caregivers and housekeepers
 * 
 * Endpoints:
 * - POST /api/stripe/connect/onboard - Create onboarding link
 * - GET /api/stripe/connect/status - Check onboarding status
 * - POST /api/stripe/connect/bank-account - Connect bank account
 * - POST /api/stripe/connect/payout-method - Add payout method
 * - POST /api/stripe/connect/session - Create account session
 */
class ConnectController extends Controller
{
    use ApiResponseTrait;

    protected StripePaymentService $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Create Stripe Connect onboarding link for caregiver
     * POST /api/stripe/connect/onboard
     */
    public function createOnboardingLink(Request $request)
    {
        $user = Auth::user();
        
        if (!in_array($user->user_type, ['caregiver', 'housekeeper'])) {
            return $this->errorResponse('Only caregivers and housekeepers can onboard to Stripe', 403);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            // Get the profile (caregiver or housekeeper)
            $profile = $this->getStaffProfile($user);
            
            if (!$profile) {
                return $this->errorResponse('Profile not found', 404);
            }
            
            // Create or retrieve Connect account
            $stripeAccountId = $profile->stripe_account_id;
            
            if (!$stripeAccountId) {
                $account = $stripe->accounts->create([
                    'type' => 'express',
                    'country' => 'US',
                    'email' => $user->email,
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                    'business_type' => 'individual',
                    'metadata' => [
                        'user_id' => $user->id,
                        'user_type' => $user->user_type
                    ]
                ]);
                
                $stripeAccountId = $account->id;
                $profile->stripe_account_id = $stripeAccountId;
                $profile->save();
            }

            // Create account link
            $baseUrl = config('app.url');
            $accountLink = $stripe->accountLinks->create([
                'account' => $stripeAccountId,
                'refresh_url' => $baseUrl . '/dashboard?stripe_refresh=1',
                'return_url' => $baseUrl . '/dashboard?stripe_success=1',
                'type' => 'account_onboarding',
            ]);

            Log::info('Stripe Connect onboarding link created', [
                'user_id' => $user->id,
                'stripe_account_id' => $stripeAccountId
            ]);

            return $this->successResponse([
                'onboarding_url' => $accountLink->url,
                'stripe_account_id' => $stripeAccountId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create onboarding link', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to create onboarding link');
        }
    }

    /**
     * Check Stripe Connect onboarding status
     * GET /api/stripe/connect/status
     */
    public function checkStatus(Request $request)
    {
        $user = Auth::user();
        
        $profile = $this->getStaffProfile($user);
        
        if (!$profile || !$profile->stripe_account_id) {
            return $this->successResponse([
                'onboarded' => false,
                'status' => 'not_started',
                'details_submitted' => false,
                'payouts_enabled' => false,
                'charges_enabled' => false
            ]);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            $account = $stripe->accounts->retrieve($profile->stripe_account_id);

            $status = 'pending';
            if ($account->details_submitted && $account->payouts_enabled) {
                $status = 'complete';
            } elseif ($account->details_submitted) {
                $status = 'pending_verification';
            }

            return $this->successResponse([
                'onboarded' => $account->details_submitted && $account->payouts_enabled,
                'status' => $status,
                'details_submitted' => $account->details_submitted,
                'payouts_enabled' => $account->payouts_enabled,
                'charges_enabled' => $account->charges_enabled,
                'requirements' => $account->requirements->currently_due ?? [],
                'stripe_account_id' => $profile->stripe_account_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check onboarding status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to check onboarding status');
        }
    }

    /**
     * Connect bank account via Stripe
     * POST /api/stripe/connect/bank-account
     */
    public function connectBankAccount(Request $request)
    {
        $request->validate([
            'bank_account_token' => 'required|string'
        ]);

        $user = Auth::user();
        $profile = $this->getStaffProfile($user);

        if (!$profile || !$profile->stripe_account_id) {
            return $this->errorResponse('Please complete Stripe onboarding first', 400);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            $bankAccount = $stripe->accounts->createExternalAccount(
                $profile->stripe_account_id,
                ['external_account' => $request->bank_account_token]
            );

            Log::info('Bank account connected', [
                'user_id' => $user->id,
                'stripe_account_id' => $profile->stripe_account_id
            ]);

            return $this->successResponse([
                'message' => 'Bank account connected successfully',
                'bank_account' => [
                    'id' => $bankAccount->id,
                    'last4' => $bankAccount->last4,
                    'bank_name' => $bankAccount->bank_name,
                    'routing_number' => $bankAccount->routing_number
                ]
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Failed to connect bank account', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to connect bank account');
        }
    }

    /**
     * Create account session for embedded onboarding
     * POST /api/stripe/connect/session
     */
    public function createAccountSession(Request $request)
    {
        $user = Auth::user();
        $profile = $this->getStaffProfile($user);

        if (!$profile || !$profile->stripe_account_id) {
            return $this->errorResponse('Stripe account not found', 400);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            $accountSession = $stripe->accountSessions->create([
                'account' => $profile->stripe_account_id,
                'components' => [
                    'account_onboarding' => ['enabled' => true],
                    'payments' => ['enabled' => true],
                    'payouts' => ['enabled' => true]
                ]
            ]);

            return $this->successResponse([
                'client_secret' => $accountSession->client_secret
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create account session', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to create account session');
        }
    }

    /**
     * Get staff profile (caregiver or housekeeper)
     */
    protected function getStaffProfile($user)
    {
        if ($user->user_type === 'caregiver') {
            return Caregiver::where('user_id', $user->id)->first();
        } elseif ($user->user_type === 'housekeeper') {
            return Housekeeper::where('user_id', $user->id)->first();
        }
        
        return null;
    }

    /**
     * Get payout methods for connected account
     * GET /api/stripe/connect/payout-methods
     */
    public function getPayoutMethods(Request $request)
    {
        $user = Auth::user();
        $profile = $this->getStaffProfile($user);

        if (!$profile || !$profile->stripe_account_id) {
            return $this->successResponse([
                'payout_methods' => [],
                'default_payout_method' => null
            ]);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            $externalAccounts = $stripe->accounts->allExternalAccounts(
                $profile->stripe_account_id,
                ['object' => 'bank_account', 'limit' => 10]
            );

            $formatted = array_map(function ($account) {
                return [
                    'id' => $account->id,
                    'bank_name' => $account->bank_name,
                    'last4' => $account->last4,
                    'routing_number' => $account->routing_number,
                    'currency' => $account->currency,
                    'default_for_currency' => $account->default_for_currency
                ];
            }, $externalAccounts->data);

            return $this->successResponse([
                'payout_methods' => $formatted
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch payout methods', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to fetch payout methods');
        }
    }

    /**
     * Get account balance
     * GET /api/stripe/connect/balance
     */
    public function getBalance(Request $request)
    {
        $user = Auth::user();
        $profile = $this->getStaffProfile($user);

        if (!$profile || !$profile->stripe_account_id) {
            return $this->successResponse([
                'available' => 0,
                'pending' => 0,
                'currency' => 'usd'
            ]);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            $balance = $stripe->balance->retrieve([], [
                'stripe_account' => $profile->stripe_account_id
            ]);

            $available = 0;
            $pending = 0;

            foreach ($balance->available as $balanceItem) {
                if ($balanceItem->currency === 'usd') {
                    $available = $balanceItem->amount / 100;
                }
            }

            foreach ($balance->pending as $balanceItem) {
                if ($balanceItem->currency === 'usd') {
                    $pending = $balanceItem->amount / 100;
                }
            }

            return $this->successResponse([
                'available' => $available,
                'pending' => $pending,
                'currency' => 'usd'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch balance', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to fetch balance');
        }
    }
}
