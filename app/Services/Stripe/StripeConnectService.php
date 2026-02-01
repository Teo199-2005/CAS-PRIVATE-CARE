<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;
use Stripe\StripeClient;

/**
 * Stripe Connect Service
 * 
 * Handles Stripe Connect operations for service providers:
 * - Caregiver onboarding
 * - Housekeeper onboarding  
 * - Account status management
 * - Dashboard links
 * 
 * @package App\Services\Stripe
 */
class StripeConnectService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret_key'));
        Stripe::setApiKey(config('stripe.secret_key'));
    }

    /**
     * Create Stripe Connect account for a caregiver
     */
    public function createCaregiverAccount(User $user): array
    {
        try {
            // Check if already has account
            if ($user->stripe_account_id) {
                return $this->getOnboardingLink(
                    $user->stripe_account_id,
                    route('caregiver.stripe.callback'),
                    route('caregiver.stripe.refresh')
                );
            }

            $account = Account::create([
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
                    'user_type' => 'caregiver',
                    'platform' => 'CAS Private Care'
                ],
            ]);

            $user->update(['stripe_account_id' => $account->id]);

            // Update caregiver record
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            if ($caregiver) {
                $caregiver->update([
                    'stripe_account_id' => $account->id,
                    'stripe_status' => 'pending'
                ]);
            }

            Log::info('Caregiver Stripe Connect account created', [
                'user_id' => $user->id,
                'account_id' => $account->id
            ]);

            return $this->getOnboardingLink(
                $account->id,
                route('caregiver.stripe.callback'),
                route('caregiver.stripe.refresh')
            );

        } catch (\Exception $e) {
            Log::error('Failed to create caregiver Stripe Connect account', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to create Stripe account: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create Stripe Connect account for a housekeeper
     */
    public function createHousekeeperAccount(User $user): array
    {
        try {
            // Check if already has account
            if ($user->stripe_account_id) {
                return $this->getOnboardingLink(
                    $user->stripe_account_id,
                    route('housekeeper.stripe.callback'),
                    route('housekeeper.stripe.refresh')
                );
            }

            $account = Account::create([
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
                    'user_type' => 'housekeeper',
                    'platform' => 'CAS Private Care'
                ],
            ]);

            $user->update(['stripe_account_id' => $account->id]);

            // Update housekeeper record
            $housekeeper = Housekeeper::where('user_id', $user->id)->first();
            if ($housekeeper) {
                $housekeeper->update([
                    'stripe_account_id' => $account->id,
                    'stripe_status' => 'pending'
                ]);
            }

            Log::info('Housekeeper Stripe Connect account created', [
                'user_id' => $user->id,
                'account_id' => $account->id
            ]);

            return $this->getOnboardingLink(
                $account->id,
                route('housekeeper.stripe.callback'),
                route('housekeeper.stripe.refresh')
            );

        } catch (\Exception $e) {
            Log::error('Failed to create housekeeper Stripe Connect account', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to create Stripe account: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate onboarding link for Stripe Connect
     */
    private function getOnboardingLink(string $accountId, string $returnUrl, string $refreshUrl): array
    {
        try {
            $accountLink = AccountLink::create([
                'account' => $accountId,
                'refresh_url' => $refreshUrl,
                'return_url' => $returnUrl,
                'type' => 'account_onboarding',
            ]);

            return [
                'success' => true,
                'url' => $accountLink->url,
                'account_id' => $accountId
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create onboarding link', [
                'account_id' => $accountId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle Stripe Connect callback (onboarding complete)
     */
    public function handleCaregiverCallback(User $user): array
    {
        try {
            if (!$user->stripe_account_id) {
                return [
                    'success' => false,
                    'error' => 'No Stripe account found'
                ];
            }

            $account = $this->stripe->accounts->retrieve($user->stripe_account_id);

            $status = $this->determineAccountStatus($account);

            // Update caregiver record
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            if ($caregiver) {
                $caregiver->update([
                    'stripe_status' => $status,
                    'stripe_account_id' => $user->stripe_account_id,
                    'can_receive_payments' => $status === 'active',
                    'stripe_onboarding_completed_at' => $status === 'active' ? now() : null
                ]);
            }

            Log::info('Caregiver Stripe Connect callback processed', [
                'user_id' => $user->id,
                'status' => $status
            ]);

            return [
                'success' => true,
                'status' => $status,
                'charges_enabled' => $account->charges_enabled ?? false,
                'payouts_enabled' => $account->payouts_enabled ?? false
            ];

        } catch (\Exception $e) {
            Log::error('Stripe callback error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle Stripe Connect callback for housekeepers
     */
    public function handleHousekeeperCallback(User $user): array
    {
        try {
            if (!$user->stripe_account_id) {
                return [
                    'success' => false,
                    'error' => 'No Stripe account found'
                ];
            }

            $account = $this->stripe->accounts->retrieve($user->stripe_account_id);

            $status = $this->determineAccountStatus($account);

            // Update housekeeper record
            $housekeeper = Housekeeper::where('user_id', $user->id)->first();
            if ($housekeeper) {
                $housekeeper->update([
                    'stripe_status' => $status,
                    'stripe_account_id' => $user->stripe_account_id,
                    'can_receive_payments' => $status === 'active',
                    'stripe_onboarding_completed_at' => $status === 'active' ? now() : null
                ]);
            }

            Log::info('Housekeeper Stripe Connect callback processed', [
                'user_id' => $user->id,
                'status' => $status
            ]);

            return [
                'success' => true,
                'status' => $status,
                'charges_enabled' => $account->charges_enabled ?? false,
                'payouts_enabled' => $account->payouts_enabled ?? false
            ];

        } catch (\Exception $e) {
            Log::error('Stripe callback error', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Stripe Connect account status for caregiver
     */
    public function getCaregiverAccountStatus(User $user): array
    {
        try {
            if (!$user->stripe_account_id) {
                return [
                    'success' => true,
                    'has_account' => false,
                    'status' => 'not_started'
                ];
            }

            $account = $this->stripe->accounts->retrieve($user->stripe_account_id);

            return [
                'success' => true,
                'has_account' => true,
                'status' => $this->determineAccountStatus($account),
                'charges_enabled' => $account->charges_enabled ?? false,
                'payouts_enabled' => $account->payouts_enabled ?? false,
                'details_submitted' => $account->details_submitted ?? false,
                'requirements' => $account->requirements ?? null
            ];

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Account may have been deleted
            $user->update(['stripe_account_id' => null]);
            return [
                'success' => true,
                'has_account' => false,
                'status' => 'not_started'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get Stripe account status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get Stripe Connect account status for housekeeper
     */
    public function getHousekeeperAccountStatus(User $user): array
    {
        // Same logic as caregiver
        return $this->getCaregiverAccountStatus($user);
    }

    /**
     * Generate Stripe Express Dashboard link
     */
    public function getDashboardLink(User $user): array
    {
        try {
            if (!$user->stripe_account_id) {
                return [
                    'success' => false,
                    'error' => 'No Stripe account found'
                ];
            }

            $loginLink = $this->stripe->accounts->createLoginLink($user->stripe_account_id);

            return [
                'success' => true,
                'url' => $loginLink->url
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create dashboard link', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Unable to access Stripe dashboard. Please complete onboarding first.'
            ];
        }
    }

    /**
     * Determine account status from Stripe response
     */
    private function determineAccountStatus(\Stripe\Account $account): string
    {
        if (($account->charges_enabled ?? false) && ($account->payouts_enabled ?? false)) {
            return 'active';
        }

        if ($account->details_submitted ?? false) {
            return 'pending';
        }

        if (!empty($account->requirements?->currently_due)) {
            return 'incomplete';
        }

        return 'pending';
    }

    /**
     * Verify that a connected account can receive payouts
     */
    public function canReceivePayouts(string $stripeAccountId): bool
    {
        try {
            $account = $this->stripe->accounts->retrieve($stripeAccountId);
            return ($account->charges_enabled ?? false) && ($account->payouts_enabled ?? false);
        } catch (\Exception $e) {
            Log::error('Failed to verify payout capability', [
                'account_id' => $stripeAccountId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get account balance for a connected account
     */
    public function getAccountBalance(string $stripeAccountId): array
    {
        try {
            $balance = $this->stripe->balance->retrieve([], ['stripe_account' => $stripeAccountId]);

            $available = 0;
            $pending = 0;

            foreach ($balance->available as $b) {
                if ($b->currency === 'usd') {
                    $available = $b->amount / 100;
                }
            }

            foreach ($balance->pending as $b) {
                if ($b->currency === 'usd') {
                    $pending = $b->amount / 100;
                }
            }

            return [
                'success' => true,
                'available' => $available,
                'pending' => $pending,
                'currency' => 'USD'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get account balance', [
                'account_id' => $stripeAccountId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
