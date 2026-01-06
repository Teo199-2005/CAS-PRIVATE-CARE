<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\SetupIntent;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Transfer;
use Stripe\Charge;
use App\Models\TimeTracking;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Payment Service
 * 
 * Handles all Stripe operations including:
 * - Client payment collection
 * - Caregiver Connect onboarding
 * - Minute-accurate payment calculations
 * - Automated transfers and payouts
 */
class StripePaymentService
{
    private $stripe;
    
    public function __construct()
    {
        $this->stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
        Stripe::setApiKey(config('stripe.secret_key'));
    }

    /**
     * =======================
     * CLIENT PAYMENT METHODS
     * =======================
     */

    /**
     * Create a Stripe Customer for a client
     */
    public function createCustomer(User $client): ?string
    {
        try {
            if ($client->stripe_customer_id) {
                return $client->stripe_customer_id;
            }

            $customer = Customer::create([
                'email' => $client->email,
                'name' => $client->name,
                'metadata' => [
                    'user_id' => $client->id,
                    'user_type' => 'client',
                    'platform' => 'CAS Private Care'
                ]
            ]);

            $client->update(['stripe_customer_id' => $customer->id]);

            return $customer->id;
        } catch (\Exception $e) {
            Log::error('Stripe Customer Creation Failed', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create Setup Intent for saving card without charging
     * Used when client books service - card is saved for later charge
     */
    public function createSetupIntent(User $client): array
    {
        try {
            $customerId = $this->createCustomer($client);
            
            if (!$customerId) {
                throw new \Exception('Failed to create Stripe customer');
            }

            $setupIntent = SetupIntent::create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'metadata' => [
                    'user_id' => $client->id,
                    'purpose' => 'save_payment_method'
                ]
            ]);

            return [
                'success' => true,
                'client_secret' => $setupIntent->client_secret,
                'setup_intent_id' => $setupIntent->id
            ];
        } catch (\Exception $e) {
            Log::error('Setup Intent Creation Failed', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Charge client based on actual hours worked
     * Called after caregiver clocks out
     */
    public function chargeClientForTimeTracking(TimeTracking $timeTracking): array
    {
        try {
            // Get client info
            $booking = $timeTracking->booking;
            if (!$booking) {
                throw new \Exception('No booking found for time tracking');
            }

            $clientUser = User::find($booking->client_id);
            if (!$clientUser || !$clientUser->stripe_customer_id) {
                throw new \Exception('Client has no payment method');
            }

            // Calculate minute-accurate charge
            $minutes = $this->calculateMinutesWorked(
                $timeTracking->clock_in_time,
                $timeTracking->clock_out_time
            );

            $hours = $minutes / 60; // Decimal hours
            $total = $timeTracking->total_client_charge;

            // Get payment method
            $customer = Customer::retrieve($clientUser->stripe_customer_id);
            $paymentMethodId = $customer->invoice_settings->default_payment_method 
                ?? $customer->default_source;

            if (!$paymentMethodId) {
                throw new \Exception('No payment method on file');
            }

            // Create charge
            $paymentIntent = PaymentIntent::create([
                'amount' => round($total * 100), // Stripe uses cents
                'currency' => 'usd',
                'customer' => $clientUser->stripe_customer_id,
                'payment_method' => $paymentMethodId,
                'off_session' => true,
                'confirm' => true,
                'description' => "Care service - {$hours} hours",
                'metadata' => [
                    'time_tracking_id' => $timeTracking->id,
                    'booking_id' => $booking->id,
                    'client_id' => $clientUser->id,
                    'caregiver_id' => $timeTracking->caregiver_id,
                    'minutes_worked' => $minutes,
                    'hours_worked' => $hours,
                    'rate_per_hour' => ($total / $hours),
                    'has_referral' => $booking->referral_code_id ? 'yes' : 'no'
                ]
            ]);

            // Update time tracking with Stripe charge ID
            $timeTracking->update([
                'stripe_charge_id' => $paymentIntent->id,
                'client_charged_at' => now()
            ]);

            return [
                'success' => true,
                'charge_id' => $paymentIntent->id,
                'amount' => $total,
                'hours' => $hours
            ];

        } catch (\Exception $e) {
            Log::error('Client Charge Failed', [
                'time_tracking_id' => $timeTracking->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * =======================
     * CAREGIVER CONNECT
     * =======================
     */

    /**
     * Create Stripe Connect Account for caregiver
     */
    public function createConnectAccount(Caregiver $caregiver): ?string
    {
        try {
            if ($caregiver->stripe_connect_id) {
                return $caregiver->stripe_connect_id;
            }

            $user = $caregiver->user;

            $account = Account::create([
                'type' => 'express',
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'individual',
                'metadata' => [
                    'caregiver_id' => $caregiver->id,
                    'user_id' => $user->id,
                    'platform' => 'CAS Private Care'
                ]
            ]);

            $caregiver->update(['stripe_connect_id' => $account->id]);

            return $account->id;
        } catch (\Exception $e) {
            Log::error('Connect Account Creation Failed', [
                'caregiver_id' => $caregiver->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Generate onboarding link for caregiver to add bank details
     */
    public function createOnboardingLink(Caregiver $caregiver): array
    {
        try {
            $accountId = $this->createConnectAccount($caregiver);
            
            if (!$accountId) {
                throw new \Exception('Failed to create Connect account');
            }

            $accountLink = AccountLink::create([
                'account' => $accountId,
                'refresh_url' => url('/caregiver-dashboard?refresh=true'),
                'return_url' => url('/caregiver-dashboard?success=true'),
                'type' => 'account_onboarding',
                'collect' => 'eventually_due',
            ]);

            return [
                'success' => true,
                'url' => $accountLink->url
            ];
        } catch (\Exception $e) {
            Log::error('Onboarding Link Failed', [
                'caregiver_id' => $caregiver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create Account Session for embedded onboarding
     */
    public function createAccountSession(Caregiver $caregiver): array
    {
        try {
            $accountId = $this->createConnectAccount($caregiver);
            
            if (!$accountId) {
                throw new \Exception('Failed to create Connect account');
            }

            // Create Account Session for embedded component
            $accountSession = \Stripe\AccountSession::create([
                'account' => $accountId,
                'components' => [
                    'account_onboarding' => [
                        'enabled' => true,
                        'features' => [
                            'external_account_collection' => true,
                        ],
                    ],
                ],
            ]);

            return [
                'success' => true,
                'client_secret' => $accountSession->client_secret,
                'account_id' => $accountId
            ];
        } catch (\Exception $e) {
            Log::error('Account Session Failed', [
                'caregiver_id' => $caregiver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add bank account to Connect account (Custom Form)
     */
    public function addBankAccountToConnect(Caregiver $caregiver, array $bankData): array
    {
        try {
            $accountId = $this->createConnectAccount($caregiver);
            
            if (!$accountId) {
                throw new \Exception('Failed to create Connect account');
            }

            // Create bank account token
            $token = \Stripe\Token::create([
                'bank_account' => [
                    'country' => 'US',
                    'currency' => 'usd',
                    'account_holder_name' => $bankData['accountHolderName'],
                    'account_holder_type' => 'individual',
                    'routing_number' => $bankData['routingNumber'],
                    'account_number' => $bankData['accountNumber'],
                ],
            ]);

            // Add external account to Connect account
            $externalAccount = \Stripe\Account::createExternalAccount(
                $accountId,
                ['external_account' => $token->id]
            );

            Log::info('Bank Account Added', [
                'caregiver_id' => $caregiver->id,
                'account_id' => $accountId,
                'bank_account_id' => $externalAccount->id
            ]);

            return [
                'success' => true,
                'message' => 'Bank account connected successfully',
                'bank_account_id' => $externalAccount->id
            ];
        } catch (\Exception $e) {
            Log::error('Bank Account Connection Failed', [
                'caregiver_id' => $caregiver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add debit card to Connect account
     */
    public function addCardToConnect(Caregiver $caregiver, array $cardData): array
    {
        try {
            $accountId = $this->createConnectAccount($caregiver);
            
            if (!$accountId) {
                throw new \Exception('Failed to create Connect account');
            }

            // Parse expiry date (MM/YY)
            list($expMonth, $expYear) = explode('/', $cardData['expiryDate']);
            $expYear = '20' . $expYear; // Convert YY to YYYY

            // Create card token
            $token = \Stripe\Token::create([
                'card' => [
                    'number' => $cardData['cardNumber'],
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                    'cvc' => $cardData['cvv'],
                    'name' => $cardData['cardholderName'],
                ],
            ]);

            // Add external account to Connect account
            $externalAccount = \Stripe\Account::createExternalAccount(
                $accountId,
                ['external_account' => $token->id]
            );

            Log::info('Debit Card Added', [
                'caregiver_id' => $caregiver->id,
                'account_id' => $accountId,
                'card_id' => $externalAccount->id
            ]);

            return [
                'success' => true,
                'message' => 'Debit card connected successfully',
                'card_id' => $externalAccount->id
            ];
        } catch (\Exception $e) {
            Log::error('Card Connection Failed', [
                'caregiver_id' => $caregiver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add Alipay account to Connect account
     */
    public function addAlipayToConnect(Caregiver $caregiver, array $alipayData): array
    {
        try {
            $accountId = $this->createConnectAccount($caregiver);
            
            if (!$accountId) {
                throw new \Exception('Failed to create Connect account');
            }

            // NOTE: Stripe doesn't support Alipay as a payout method for US Connect accounts
            // This is a placeholder for future international support
            // For now, we'll store the information but return an informative message
            
            Log::info('Alipay Account Information Received', [
                'caregiver_id' => $caregiver->id,
                'account_id' => $accountId,
                'alipay_id' => $alipayData['alipayId']
            ]);

            return [
                'success' => false,
                'message' => 'Alipay payouts are currently only available for accounts in China, Hong Kong, and Singapore. Please select Bank Account or Debit Card for US-based payouts.'
            ];
        } catch (\Exception $e) {
            Log::error('Alipay Connection Failed', [
                'caregiver_id' => $caregiver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Add Cash App to Connect account
     */
    public function addCashAppToConnect(Caregiver $caregiver, array $cashappData): array
    {
        try {
            $accountId = $this->createConnectAccount($caregiver);
            
            if (!$accountId) {
                throw new \Exception('Failed to create Connect account');
            }

            // NOTE: Stripe doesn't directly support Cash App as a payout method
            // Cash App payouts would typically go through bank account or debit card
            // This is a placeholder implementation
            
            Log::info('Cash App Information Received', [
                'caregiver_id' => $caregiver->id,
                'account_id' => $accountId,
                'cashtag' => $cashappData['cashtag']
            ]);

            return [
                'success' => false,
                'message' => 'Cash App direct payouts are not currently supported. Please link your Cash App debit card instead using the Debit Card option.'
            ];
        } catch (\Exception $e) {
            Log::error('Cash App Connection Failed', [
                'caregiver_id' => $caregiver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if caregiver has completed onboarding
     */
    public function isConnectAccountComplete(Caregiver $caregiver): bool
    {
        try {
            if (!$caregiver->stripe_connect_id) {
                return false;
            }

            $account = Account::retrieve($caregiver->stripe_connect_id);
            
            return $account->charges_enabled && $account->payouts_enabled;
        } catch (\Exception $e) {
            Log::error('Connect Account Check Failed', [
                'caregiver_id' => $caregiver->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * =======================
     * PAYMENT DISTRIBUTION
     * =======================
     */

    /**
     * Transfer funds to caregiver's connected account
     */
    public function transferToCaregiver(TimeTracking $timeTracking): array
    {
        try {
            $caregiver = $timeTracking->caregiver;
            
            if (!$caregiver->stripe_connect_id) {
                throw new \Exception('Caregiver has no Connect account');
            }

            if (!$this->isConnectAccountComplete($caregiver)) {
                throw new \Exception('Caregiver onboarding not complete');
            }

            // Ensure client has been charged first
            if (!$timeTracking->stripe_charge_id) {
                throw new \Exception('Client has not been charged yet');
            }

            $amount = $timeTracking->caregiver_earnings;

            $transfer = Transfer::create([
                'amount' => round($amount * 100), // Cents
                'currency' => 'usd',
                'destination' => $caregiver->stripe_connect_id,
                'description' => "Payment for {$timeTracking->hours_worked} hours",
                'metadata' => [
                    'time_tracking_id' => $timeTracking->id,
                    'caregiver_id' => $caregiver->id,
                    'hours_worked' => $timeTracking->hours_worked,
                    'payment_type' => 'caregiver_earnings'
                ]
            ]);

            // Mark as paid
            $timeTracking->update([
                'stripe_transfer_id' => $transfer->id,
                'paid_at' => now(),
                'payment_status' => 'paid'
            ]);

            return [
                'success' => true,
                'transfer_id' => $transfer->id,
                'amount' => $amount
            ];

        } catch (\Exception $e) {
            Log::error('Caregiver Transfer Failed', [
                'time_tracking_id' => $timeTracking->id,
                'error' => $e->getMessage()
            ]);

            $timeTracking->update(['payment_status' => 'failed']);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Transfer to marketing partner
     */
    public function transferToMarketing(User $marketingUser, $amount, $metadata = []): array
    {
        try {
            if (!$marketingUser->stripe_connect_id) {
                throw new \Exception('Marketing partner has no Connect account');
            }

            $transfer = Transfer::create([
                'amount' => round($amount * 100),
                'currency' => 'usd',
                'destination' => $marketingUser->stripe_connect_id,
                'description' => "Marketing commission",
                'metadata' => array_merge([
                    'user_id' => $marketingUser->id,
                    'payment_type' => 'marketing_commission'
                ], $metadata)
            ]);

            return [
                'success' => true,
                'transfer_id' => $transfer->id,
                'amount' => $amount
            ];

        } catch (\Exception $e) {
            Log::error('Marketing Transfer Failed', [
                'user_id' => $marketingUser->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Transfer to training center
     */
    public function transferToTraining(User $trainingUser, $amount, $metadata = []): array
    {
        try {
            if (!$trainingUser->stripe_connect_id) {
                throw new \Exception('Training center has no Connect account');
            }

            $transfer = Transfer::create([
                'amount' => round($amount * 100),
                'currency' => 'usd',
                'destination' => $trainingUser->stripe_connect_id,
                'description' => "Training center commission",
                'metadata' => array_merge([
                    'user_id' => $trainingUser->id,
                    'payment_type' => 'training_commission'
                ], $metadata)
            ]);

            return [
                'success' => true,
                'transfer_id' => $transfer->id,
                'amount' => $amount
            ];

        } catch (\Exception $e) {
            Log::error('Training Transfer Failed', [
                'user_id' => $trainingUser->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Add bank account for marketing staff
     */
    public function addMarketingBankAccount(User $marketingUser, array $bankDetails): array
    {
        try {
            // Create Connect account if doesn't exist
            if (!$marketingUser->stripe_connect_id) {
                $account = $this->createConnectAccount($marketingUser);
                if (!$account['success']) {
                    throw new \Exception($account['error']);
                }
                $marketingUser->stripe_connect_id = $account['account_id'];
                $marketingUser->save();
            }

            // Create bank account token
            $bankToken = \Stripe\Token::create([
                'bank_account' => [
                    'country' => 'US',
                    'currency' => 'usd',
                    'account_holder_name' => $bankDetails['accountHolderName'],
                    'account_holder_type' => 'individual',
                    'routing_number' => $bankDetails['routingNumber'],
                    'account_number' => $bankDetails['accountNumber'],
                ]
            ]);

            // Add external account to Connect account
            $externalAccount = \Stripe\Account::createExternalAccount(
                $marketingUser->stripe_connect_id,
                ['external_account' => $bankToken->id]
            );

            return [
                'success' => true,
                'account_id' => $externalAccount->id
            ];

        } catch (\Exception $e) {
            Log::error('Marketing Bank Account Failed', [
                'user_id' => $marketingUser->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Add bank account for training center
     */
    public function addTrainingBankAccount(User $trainingUser, array $bankDetails): array
    {
        try {
            // Create Connect account if doesn't exist
            if (!$trainingUser->stripe_connect_id) {
                $account = $this->createConnectAccount($trainingUser);
                if (!$account['success']) {
                    throw new \Exception($account['error']);
                }
                $trainingUser->stripe_connect_id = $account['account_id'];
                $trainingUser->save();
            }

            // Create bank account token
            $bankToken = \Stripe\Token::create([
                'bank_account' => [
                    'country' => 'US',
                    'currency' => 'usd',
                    'account_holder_name' => $bankDetails['accountHolderName'],
                    'account_holder_type' => 'individual',
                    'routing_number' => $bankDetails['routingNumber'],
                    'account_number' => $bankDetails['accountNumber'],
                ]
            ]);

            // Add external account to Connect account
            $externalAccount = \Stripe\Account::createExternalAccount(
                $trainingUser->stripe_connect_id,
                ['external_account' => $bankToken->id]
            );

            return [
                'success' => true,
                'account_id' => $externalAccount->id
            ];

        } catch (\Exception $e) {
            Log::error('Training Bank Account Failed', [
                'user_id' => $trainingUser->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * =======================
     * CALCULATIONS
     * =======================
     */

    /**
     * Calculate minutes worked with late check
     */
    public function calculateMinutesWorked($clockInTime, $clockOutTime, $scheduledTime = null): int
    {
        $clockIn = Carbon::parse($clockInTime);
        $clockOut = Carbon::parse($clockOutTime);
        
        $minutes = $clockOut->diffInMinutes($clockIn);
        
        return $minutes;
    }

    /**
     * Calculate late minutes (if clock-in was after scheduled time)
     */
    public function calculateLateMinutes($clockInTime, $scheduledTime): int
    {
        if (!$scheduledTime) {
            return 0;
        }

        $clockIn = Carbon::parse($clockInTime);
        $scheduled = Carbon::parse($scheduledTime);
        
        if ($clockIn->lte($scheduled)) {
            return 0; // Not late
        }

        return $clockIn->diffInMinutes($scheduled);
    }

    /**
     * Calculate exact payment by minute
     * 
     * @param int $minutes Total minutes worked
     * @param float $hourlyRate Rate per hour
     * @return float Exact payment amount
     */
    public function calculatePaymentByMinute(int $minutes, float $hourlyRate): float
    {
        $hours = $minutes / 60;
        return round($hours * $hourlyRate, 2);
    }

    /**
     * Recalculate time tracking with minute accuracy
     * This ensures no overpayment
     */
    public function recalculateTimeTracking(TimeTracking $timeTracking): void
    {
        $minutes = $this->calculateMinutesWorked(
            $timeTracking->clock_in_time,
            $timeTracking->clock_out_time
        );

        $hours = $minutes / 60;

        // Recalculate all amounts by minute
        $caregiverEarnings = $this->calculatePaymentByMinute($minutes, 28.00);
        
        $marketingCommission = 0;
        $clientRate = 45.00;
        
        if ($timeTracking->marketing_partner_id) {
            $marketingCommission = $this->calculatePaymentByMinute($minutes, 1.00);
            $clientRate = 40.00;
        }

        $trainingCommission = 0;
        if ($timeTracking->training_center_user_id) {
            $trainingCommission = $this->calculatePaymentByMinute($minutes, 0.50);
        }

        $totalClientCharge = $this->calculatePaymentByMinute($minutes, $clientRate);
        $agencyCommission = $totalClientCharge - $caregiverEarnings - $marketingCommission - $trainingCommission;

        $timeTracking->update([
            'hours_worked' => $hours,
            'caregiver_earnings' => $caregiverEarnings,
            'marketing_partner_commission' => $marketingCommission > 0 ? $marketingCommission : null,
            'training_center_commission' => $trainingCommission > 0 ? $trainingCommission : null,
            'agency_commission' => $agencyCommission,
            'total_client_charge' => $totalClientCharge
        ]);
    }

    /**
     * =======================
     * BATCH PAYOUTS
     * =======================
     */

    /**
     * Process weekly caregiver payouts (called by cron)
     */
    public function processWeeklyPayouts(): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Get all pending time trackings from last week
        $pendingTrackings = TimeTracking::where('payment_status', 'pending')
            ->whereNotNull('stripe_charge_id') // Client has been charged
            ->whereNotNull('caregiver_earnings')
            ->whereBetween('work_date', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ])
            ->get();

        foreach ($pendingTrackings as $tracking) {
            $result = $this->transferToCaregiver($tracking);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'time_tracking_id' => $tracking->id,
                    'error' => $result['error']
                ];
            }
        }

        return $results;
    }

    /**
     * Process monthly partner payouts
     */
    public function processMonthlyPartnerPayouts(): array
    {
        $results = [
            'marketing' => ['success' => 0, 'failed' => 0],
            'training' => ['success' => 0, 'failed' => 0],
            'errors' => []
        ];

        $lastMonth = now()->subMonth();

        // Marketing partners
        $marketingCommissions = TimeTracking::select('marketing_partner_id')
            ->selectRaw('SUM(marketing_partner_commission) as total')
            ->whereNotNull('marketing_partner_id')
            ->whereNotNull('stripe_charge_id') // Client charged
            ->whereYear('work_date', $lastMonth->year)
            ->whereMonth('work_date', $lastMonth->month)
            ->where('payment_status', 'paid') // Caregiver already paid
            ->where('marketing_paid', false)
            ->groupBy('marketing_partner_id')
            ->get();

        foreach ($marketingCommissions as $commission) {
            $user = User::find($commission->marketing_partner_id);
            if ($user) {
                $result = $this->transferToMarketing($user, $commission->total, [
                    'period' => $lastMonth->format('Y-m'),
                    'type' => 'monthly_payout'
                ]);

                if ($result['success']) {
                    // Mark all related trackings as marketing_paid
                    TimeTracking::where('marketing_partner_id', $user->id)
                        ->whereYear('work_date', $lastMonth->year)
                        ->whereMonth('work_date', $lastMonth->month)
                        ->update(['marketing_paid' => true]);
                    
                    $results['marketing']['success']++;
                } else {
                    $results['marketing']['failed']++;
                    $results['errors'][] = $result['error'];
                }
            }
        }

        // Similar process for training centers...

        return $results;
    }
}
