<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\StripeClient;

/**
 * Stripe Admin Service
 * 
 * Handles admin-only Stripe operations:
 * - View all payments
 * - Process refunds
 * - Manage connected accounts
 * - View platform earnings
 * 
 * @package App\Services\Stripe
 */
class StripeAdminService
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
     * Get payment details for admin view
     */
    public function getPaymentDetails(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId, [
                'expand' => ['customer', 'payment_method', 'charges']
            ]);

            // Get local records
            $payment = Payment::where('transaction_id', $paymentIntentId)->first();
            $booking = Booking::where('stripe_payment_intent_id', $paymentIntentId)->first();

            return [
                'success' => true,
                'stripe' => [
                    'id' => $paymentIntent->id,
                    'amount' => $paymentIntent->amount / 100,
                    'currency' => $paymentIntent->currency,
                    'status' => $paymentIntent->status,
                    'created' => date('Y-m-d H:i:s', $paymentIntent->created),
                    'customer_email' => $paymentIntent->customer?->email ?? null,
                    'payment_method' => $paymentIntent->payment_method?->type ?? 'unknown',
                    'metadata' => $paymentIntent->metadata->toArray(),
                    'refunded' => $paymentIntent->charges?->data[0]?->refunded ?? false,
                    'amount_refunded' => ($paymentIntent->charges?->data[0]?->amount_refunded ?? 0) / 100
                ],
                'local' => [
                    'payment' => $payment ? [
                        'id' => $payment->id,
                        'client_id' => $payment->client_id,
                        'booking_id' => $payment->booking_id,
                        'amount' => $payment->amount,
                        'status' => $payment->status
                    ] : null,
                    'booking' => $booking ? [
                        'id' => $booking->id,
                        'client_id' => $booking->client_id,
                        'caregiver_id' => $booking->caregiver_id,
                        'status' => $booking->status,
                        'payment_status' => $booking->payment_status
                    ] : null
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Admin: Failed to get payment details', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process a refund (full or partial)
     */
    public function processRefund(
        string $paymentIntentId,
        ?float $amount = null,
        string $reason = 'requested_by_customer'
    ): array {
        try {
            // Get payment intent first
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'error' => 'Payment cannot be refunded - status: ' . $paymentIntent->status
                ];
            }

            $chargeId = $paymentIntent->latest_charge;

            if (!$chargeId) {
                return [
                    'success' => false,
                    'error' => 'No charge found for this payment'
                ];
            }

            $refundParams = [
                'charge' => $chargeId,
                'reason' => $reason,
                'metadata' => [
                    'refunded_by' => 'admin',
                    'original_payment_intent' => $paymentIntentId
                ]
            ];

            // Partial refund
            if ($amount !== null) {
                $amountInCents = (int) round($amount * 100);
                $refundParams['amount'] = $amountInCents;
            }

            $idempotencyKey = 'refund_' . $paymentIntentId . '_' . now()->format('YmdHis');

            $refund = Refund::create($refundParams, [
                'idempotency_key' => $idempotencyKey
            ]);

            // Update local records
            DB::transaction(function () use ($paymentIntentId, $refund, $amount) {
                $booking = Booking::where('stripe_payment_intent_id', $paymentIntentId)->first();
                if ($booking) {
                    // Full refund
                    if ($amount === null) {
                        $booking->update([
                            'payment_status' => 'refunded',
                            'refunded_at' => now()
                        ]);
                    } else {
                        $booking->update([
                            'payment_status' => 'partial_refund',
                            'refunded_amount' => DB::raw('IFNULL(refunded_amount, 0) + ' . $amount)
                        ]);
                    }
                }

                $payment = Payment::where('transaction_id', $paymentIntentId)->first();
                if ($payment) {
                    $payment->update([
                        'status' => $amount === null ? 'refunded' : 'partial_refund',
                        'refund_id' => $refund->id,
                        'refunded_at' => now()
                    ]);
                }
            });

            Log::info('Admin: Refund processed', [
                'payment_intent_id' => $paymentIntentId,
                'refund_id' => $refund->id,
                'amount' => $amount ?? 'full'
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => ($refund->amount ?? 0) / 100,
                'status' => $refund->status
            ];

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::error('Admin: Invalid refund request', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Admin: Refund failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all connected accounts with status
     */
    public function listConnectedAccounts(int $limit = 100): array
    {
        try {
            $accounts = $this->stripe->accounts->all(['limit' => $limit]);

            $data = array_map(function ($account) {
                return [
                    'id' => $account->id,
                    'email' => $account->email,
                    'business_type' => $account->business_type,
                    'type' => $account->type,
                    'charges_enabled' => $account->charges_enabled ?? false,
                    'payouts_enabled' => $account->payouts_enabled ?? false,
                    'details_submitted' => $account->details_submitted ?? false,
                    'created' => date('Y-m-d H:i:s', $account->created),
                    'metadata' => $account->metadata->toArray()
                ];
            }, $accounts->data);

            return [
                'success' => true,
                'accounts' => $data,
                'total' => count($data)
            ];

        } catch (\Exception $e) {
            Log::error('Admin: Failed to list connected accounts', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get connected account details
     */
    public function getConnectedAccountDetails(string $accountId): array
    {
        try {
            $account = $this->stripe->accounts->retrieve($accountId);

            // Find local user
            $user = User::where('stripe_account_id', $accountId)->first();
            $caregiver = Caregiver::where('stripe_account_id', $accountId)->first();
            $housekeeper = Housekeeper::where('stripe_account_id', $accountId)->first();

            return [
                'success' => true,
                'stripe' => [
                    'id' => $account->id,
                    'email' => $account->email,
                    'business_type' => $account->business_type,
                    'type' => $account->type,
                    'charges_enabled' => $account->charges_enabled ?? false,
                    'payouts_enabled' => $account->payouts_enabled ?? false,
                    'details_submitted' => $account->details_submitted ?? false,
                    'requirements' => $account->requirements ?? null,
                    'created' => date('Y-m-d H:i:s', $account->created)
                ],
                'local' => [
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'user_type' => $user->user_type
                    ] : null,
                    'caregiver' => $caregiver ? [
                        'id' => $caregiver->id,
                        'stripe_status' => $caregiver->stripe_status
                    ] : null,
                    'housekeeper' => $housekeeper ? [
                        'id' => $housekeeper->id,
                        'stripe_status' => $housekeeper->stripe_status
                    ] : null
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Admin: Failed to get account details', [
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
     * Get platform balance
     */
    public function getPlatformBalance(): array
    {
        try {
            $balance = $this->stripe->balance->retrieve();

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
            Log::error('Admin: Failed to get platform balance', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get recent payments list
     */
    public function listRecentPayments(int $limit = 25): array
    {
        try {
            $paymentIntents = $this->stripe->paymentIntents->all([
                'limit' => $limit
            ]);

            $data = array_map(function ($pi) {
                return [
                    'id' => $pi->id,
                    'amount' => $pi->amount / 100,
                    'currency' => $pi->currency,
                    'status' => $pi->status,
                    'created' => date('Y-m-d H:i:s', $pi->created),
                    'customer' => $pi->customer,
                    'description' => $pi->description,
                    'metadata' => $pi->metadata->toArray()
                ];
            }, $paymentIntents->data);

            return [
                'success' => true,
                'payments' => $data
            ];

        } catch (\Exception $e) {
            Log::error('Admin: Failed to list payments', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get recent transfers list
     */
    public function listRecentTransfers(int $limit = 25): array
    {
        try {
            $transfers = $this->stripe->transfers->all([
                'limit' => $limit
            ]);

            $data = array_map(function ($t) {
                return [
                    'id' => $t->id,
                    'amount' => $t->amount / 100,
                    'currency' => $t->currency,
                    'destination' => $t->destination,
                    'created' => date('Y-m-d H:i:s', $t->created),
                    'description' => $t->description,
                    'metadata' => $t->metadata->toArray()
                ];
            }, $transfers->data);

            return [
                'success' => true,
                'transfers' => $data
            ];

        } catch (\Exception $e) {
            Log::error('Admin: Failed to list transfers', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Sync connected account status to local database
     */
    public function syncConnectedAccountStatus(string $accountId): array
    {
        try {
            $account = $this->stripe->accounts->retrieve($accountId);

            $status = 'pending';
            if (($account->charges_enabled ?? false) && ($account->payouts_enabled ?? false)) {
                $status = 'active';
            } elseif (!empty($account->requirements?->currently_due)) {
                $status = 'incomplete';
            }

            // Update local records
            $updated = [];

            $caregiver = Caregiver::where('stripe_account_id', $accountId)->first();
            if ($caregiver) {
                $caregiver->update([
                    'stripe_status' => $status,
                    'can_receive_payments' => $status === 'active'
                ]);
                $updated[] = 'caregiver:' . $caregiver->id;
            }

            $housekeeper = Housekeeper::where('stripe_account_id', $accountId)->first();
            if ($housekeeper) {
                $housekeeper->update([
                    'stripe_status' => $status,
                    'can_receive_payments' => $status === 'active'
                ]);
                $updated[] = 'housekeeper:' . $housekeeper->id;
            }

            return [
                'success' => true,
                'status' => $status,
                'updated' => $updated
            ];

        } catch (\Exception $e) {
            Log::error('Admin: Failed to sync account status', [
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
     * Retrieve payment dashboard statistics
     */
    public function getDashboardStats(string $startDate = null, string $endDate = null): array
    {
        try {
            $start = $startDate ? strtotime($startDate) : strtotime('-30 days');
            $end = $endDate ? strtotime($endDate) : time();

            // Get successful payments
            $payments = $this->stripe->paymentIntents->all([
                'created' => [
                    'gte' => $start,
                    'lte' => $end
                ],
                'limit' => 100
            ]);

            $totalRevenue = 0;
            $successfulPayments = 0;
            $failedPayments = 0;

            foreach ($payments->data as $pi) {
                if ($pi->status === 'succeeded') {
                    $totalRevenue += $pi->amount;
                    $successfulPayments++;
                } elseif (in_array($pi->status, ['canceled', 'requires_payment_method'])) {
                    $failedPayments++;
                }
            }

            // Get transfers
            $transfers = $this->stripe->transfers->all([
                'created' => [
                    'gte' => $start,
                    'lte' => $end
                ],
                'limit' => 100
            ]);

            $totalPayouts = 0;
            foreach ($transfers->data as $t) {
                $totalPayouts += $t->amount;
            }

            return [
                'success' => true,
                'period' => [
                    'start' => date('Y-m-d', $start),
                    'end' => date('Y-m-d', $end)
                ],
                'revenue' => [
                    'total' => $totalRevenue / 100,
                    'currency' => 'USD'
                ],
                'payments' => [
                    'successful' => $successfulPayments,
                    'failed' => $failedPayments,
                    'success_rate' => $successfulPayments > 0 
                        ? round(($successfulPayments / ($successfulPayments + $failedPayments)) * 100, 1) 
                        : 0
                ],
                'payouts' => [
                    'total' => $totalPayouts / 100,
                    'count' => count($transfers->data)
                ],
                'net_platform' => ($totalRevenue - $totalPayouts) / 100
            ];

        } catch (\Exception $e) {
            Log::error('Admin: Failed to get dashboard stats', [
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
