<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\MarketingPayout;
use App\Models\TrainingPayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Transfer;

/**
 * Stripe Payout Service
 * 
 * Handles payouts to connected accounts:
 * - Marketing referral payouts
 * - Training completion payouts
 * - Booking completion payouts
 * 
 * @package App\Services\Stripe
 */
class StripePayoutService
{
    private StripeClient $stripe;
    private StripeConnectService $connectService;

    public function __construct(StripeConnectService $connectService)
    {
        $this->stripe = new StripeClient(config('stripe.secret_key'));
        Stripe::setApiKey(config('stripe.secret_key'));
        $this->connectService = $connectService;
    }

    /**
     * Process marketing referral payout
     */
    public function processMarketingPayout(
        User $recipient,
        float $amount,
        string $referralCode,
        int $referredUserId,
        ?string $description = null
    ): array {
        try {
            // Validate connected account
            if (!$recipient->stripe_account_id) {
                return [
                    'success' => false,
                    'error' => 'Recipient has no Stripe account'
                ];
            }

            if (!$this->connectService->canReceivePayouts($recipient->stripe_account_id)) {
                return [
                    'success' => false,
                    'error' => 'Recipient Stripe account is not active'
                ];
            }

            $amountInCents = (int) round($amount * 100);

            if ($amountInCents <= 0) {
                return [
                    'success' => false,
                    'error' => 'Invalid payout amount'
                ];
            }

            return DB::transaction(function () use ($recipient, $amountInCents, $amount, $referralCode, $referredUserId, $description) {
                // Check for duplicate payout
                $existingPayout = MarketingPayout::where('referral_code', $referralCode)
                    ->where('referred_user_id', $referredUserId)
                    ->where('status', 'completed')
                    ->exists();

                if ($existingPayout) {
                    return [
                        'success' => false,
                        'error' => 'Payout already processed for this referral'
                    ];
                }

                $idempotencyKey = 'marketing_payout_' . $referralCode . '_' . $referredUserId;

                $transfer = Transfer::create([
                    'amount' => $amountInCents,
                    'currency' => 'usd',
                    'destination' => $recipient->stripe_account_id,
                    'description' => $description ?? 'Marketing referral bonus',
                    'metadata' => [
                        'type' => 'marketing_payout',
                        'referral_code' => $referralCode,
                        'referred_user_id' => $referredUserId,
                        'recipient_user_id' => $recipient->id
                    ],
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);

                // Record payout
                MarketingPayout::create([
                    'user_id' => $recipient->id,
                    'referral_code' => $referralCode,
                    'referred_user_id' => $referredUserId,
                    'amount' => $amount,
                    'stripe_transfer_id' => $transfer->id,
                    'status' => 'completed',
                    'paid_at' => now()
                ]);

                Log::info('Marketing payout processed', [
                    'recipient_id' => $recipient->id,
                    'amount' => $amount,
                    'transfer_id' => $transfer->id
                ]);

                return [
                    'success' => true,
                    'transfer_id' => $transfer->id,
                    'amount' => $amount
                ];
            });

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::error('Invalid Stripe request for marketing payout', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Invalid Stripe account'
            ];
        } catch (\Exception $e) {
            Log::error('Marketing payout failed', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process training completion payout
     */
    public function processTrainingPayout(
        User $recipient,
        float $amount,
        int $trainingId,
        ?string $description = null
    ): array {
        try {
            // Validate connected account
            if (!$recipient->stripe_account_id) {
                return [
                    'success' => false,
                    'error' => 'Recipient has no Stripe account'
                ];
            }

            if (!$this->connectService->canReceivePayouts($recipient->stripe_account_id)) {
                return [
                    'success' => false,
                    'error' => 'Recipient Stripe account is not active'
                ];
            }

            $amountInCents = (int) round($amount * 100);

            if ($amountInCents <= 0) {
                return [
                    'success' => false,
                    'error' => 'Invalid payout amount'
                ];
            }

            return DB::transaction(function () use ($recipient, $amountInCents, $amount, $trainingId, $description) {
                // Check for duplicate payout
                $existingPayout = TrainingPayout::where('training_id', $trainingId)
                    ->where('user_id', $recipient->id)
                    ->where('status', 'completed')
                    ->exists();

                if ($existingPayout) {
                    return [
                        'success' => false,
                        'error' => 'Training payout already processed'
                    ];
                }

                $idempotencyKey = 'training_payout_' . $trainingId . '_' . $recipient->id;

                $transfer = Transfer::create([
                    'amount' => $amountInCents,
                    'currency' => 'usd',
                    'destination' => $recipient->stripe_account_id,
                    'description' => $description ?? 'Training completion bonus',
                    'metadata' => [
                        'type' => 'training_payout',
                        'training_id' => $trainingId,
                        'recipient_user_id' => $recipient->id
                    ],
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);

                // Record payout
                TrainingPayout::create([
                    'user_id' => $recipient->id,
                    'training_id' => $trainingId,
                    'amount' => $amount,
                    'stripe_transfer_id' => $transfer->id,
                    'status' => 'completed',
                    'paid_at' => now()
                ]);

                Log::info('Training payout processed', [
                    'recipient_id' => $recipient->id,
                    'training_id' => $trainingId,
                    'amount' => $amount,
                    'transfer_id' => $transfer->id
                ]);

                return [
                    'success' => true,
                    'transfer_id' => $transfer->id,
                    'amount' => $amount
                ];
            });

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::error('Invalid Stripe request for training payout', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Invalid Stripe account'
            ];
        } catch (\Exception $e) {
            Log::error('Training payout failed', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process caregiver earnings payout for completed booking
     */
    public function processCaregiverEarnings(
        Caregiver $caregiver,
        int $bookingId,
        float $amount,
        float $platformFee = 0
    ): array {
        try {
            $user = $caregiver->user;

            if (!$user || !$user->stripe_account_id) {
                return [
                    'success' => false,
                    'error' => 'Caregiver has no Stripe account'
                ];
            }

            if (!$this->connectService->canReceivePayouts($user->stripe_account_id)) {
                return [
                    'success' => false,
                    'error' => 'Caregiver Stripe account is not active'
                ];
            }

            $netAmount = $amount - $platformFee;
            $amountInCents = (int) round($netAmount * 100);

            if ($amountInCents <= 0) {
                return [
                    'success' => false,
                    'error' => 'Invalid payout amount after fees'
                ];
            }

            $idempotencyKey = 'caregiver_earnings_' . $bookingId . '_' . $caregiver->id;

            $transfer = Transfer::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'destination' => $user->stripe_account_id,
                'description' => 'Booking #' . $bookingId . ' earnings',
                'metadata' => [
                    'type' => 'caregiver_earnings',
                    'booking_id' => $bookingId,
                    'caregiver_id' => $caregiver->id,
                    'gross_amount' => $amount,
                    'platform_fee' => $platformFee
                ],
            ], [
                'idempotency_key' => $idempotencyKey
            ]);

            Log::info('Caregiver earnings payout processed', [
                'caregiver_id' => $caregiver->id,
                'booking_id' => $bookingId,
                'amount' => $netAmount,
                'transfer_id' => $transfer->id
            ]);

            return [
                'success' => true,
                'transfer_id' => $transfer->id,
                'gross_amount' => $amount,
                'platform_fee' => $platformFee,
                'net_amount' => $netAmount
            ];

        } catch (\Exception $e) {
            Log::error('Caregiver earnings payout failed', [
                'caregiver_id' => $caregiver->id,
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process housekeeper earnings payout for completed booking
     */
    public function processHousekeeperEarnings(
        Housekeeper $housekeeper,
        int $bookingId,
        float $amount,
        float $platformFee = 0
    ): array {
        try {
            $user = $housekeeper->user;

            if (!$user || !$user->stripe_account_id) {
                return [
                    'success' => false,
                    'error' => 'Housekeeper has no Stripe account'
                ];
            }

            if (!$this->connectService->canReceivePayouts($user->stripe_account_id)) {
                return [
                    'success' => false,
                    'error' => 'Housekeeper Stripe account is not active'
                ];
            }

            $netAmount = $amount - $platformFee;
            $amountInCents = (int) round($netAmount * 100);

            if ($amountInCents <= 0) {
                return [
                    'success' => false,
                    'error' => 'Invalid payout amount after fees'
                ];
            }

            $idempotencyKey = 'housekeeper_earnings_' . $bookingId . '_' . $housekeeper->id;

            $transfer = Transfer::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'destination' => $user->stripe_account_id,
                'description' => 'Booking #' . $bookingId . ' earnings',
                'metadata' => [
                    'type' => 'housekeeper_earnings',
                    'booking_id' => $bookingId,
                    'housekeeper_id' => $housekeeper->id,
                    'gross_amount' => $amount,
                    'platform_fee' => $platformFee
                ],
            ], [
                'idempotency_key' => $idempotencyKey
            ]);

            Log::info('Housekeeper earnings payout processed', [
                'housekeeper_id' => $housekeeper->id,
                'booking_id' => $bookingId,
                'amount' => $netAmount,
                'transfer_id' => $transfer->id
            ]);

            return [
                'success' => true,
                'transfer_id' => $transfer->id,
                'gross_amount' => $amount,
                'platform_fee' => $platformFee,
                'net_amount' => $netAmount
            ];

        } catch (\Exception $e) {
            Log::error('Housekeeper earnings payout failed', [
                'housekeeper_id' => $housekeeper->id,
                'booking_id' => $bookingId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get transfer details
     */
    public function getTransfer(string $transferId): array
    {
        try {
            $transfer = $this->stripe->transfers->retrieve($transferId);

            return [
                'success' => true,
                'transfer' => [
                    'id' => $transfer->id,
                    'amount' => $transfer->amount / 100,
                    'currency' => $transfer->currency,
                    'destination' => $transfer->destination,
                    'created' => date('Y-m-d H:i:s', $transfer->created),
                    'description' => $transfer->description,
                    'metadata' => $transfer->metadata->toArray()
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get transfer', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * List transfers for a connected account
     */
    public function listTransfers(string $stripeAccountId, int $limit = 10): array
    {
        try {
            $transfers = $this->stripe->transfers->all([
                'destination' => $stripeAccountId,
                'limit' => $limit
            ]);

            $data = array_map(function ($t) {
                return [
                    'id' => $t->id,
                    'amount' => $t->amount / 100,
                    'currency' => $t->currency,
                    'created' => date('Y-m-d H:i:s', $t->created),
                    'description' => $t->description
                ];
            }, $transfers->data);

            return [
                'success' => true,
                'transfers' => $data
            ];

        } catch (\Exception $e) {
            Log::error('Failed to list transfers', [
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
