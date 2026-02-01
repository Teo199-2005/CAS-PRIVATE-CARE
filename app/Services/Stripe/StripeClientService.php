<?php

declare(strict_types=1);

namespace App\Services\Stripe;

use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentFeeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\Stripe;
use Stripe\StripeClient;

/**
 * Stripe Client Service
 * 
 * Handles all client-facing Stripe operations:
 * - Setup Intents (save cards)
 * - Payment Methods (list, delete)
 * - Payment Processing
 * 
 * @package App\Services\Stripe
 */
class StripeClientService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret_key'));
        Stripe::setApiKey(config('stripe.secret_key'));
    }

    /**
     * Create or retrieve Stripe Customer for a user
     */
    public function ensureCustomer(User $user): ?string
    {
        try {
            if ($user->stripe_customer_id) {
                return $user->stripe_customer_id;
            }

            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name ?? $user->email,
                'metadata' => [
                    'user_id' => $user->id,
                    'user_type' => $user->user_type,
                    'platform' => 'CAS Private Care'
                ]
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);

            Log::info('Stripe customer created', [
                'user_id' => $user->id,
                'customer_id' => $customer->id
            ]);

            return $customer->id;
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe customer', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create Setup Intent for saving card without charging
     */
    public function createSetupIntent(User $user): array
    {
        try {
            $customerId = $this->ensureCustomer($user);
            
            if (!$customerId) {
                throw new \Exception('Failed to create Stripe customer');
            }

            $setupIntent = SetupIntent::create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'metadata' => [
                    'user_id' => $user->id,
                    'purpose' => 'save_payment_method'
                ]
            ]);

            return [
                'success' => true,
                'client_secret' => $setupIntent->client_secret,
                'setup_intent_id' => $setupIntent->id
            ];
        } catch (\Exception $e) {
            Log::error('Setup Intent creation failed', [
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
     * Get all saved payment methods for a user
     */
    public function getPaymentMethods(User $user): array
    {
        if (!$user->stripe_customer_id) {
            return ['success' => true, 'payment_methods' => []];
        }

        try {
            $paymentMethods = $this->stripe->paymentMethods->all([
                'customer' => $user->stripe_customer_id,
                'type' => 'card',
            ]);

            $methods = array_map(function ($pm) {
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

            return [
                'success' => true,
                'payment_methods' => $methods
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch payment methods', [
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
     * Attach a payment method to customer
     */
    public function attachPaymentMethod(User $user, string $paymentMethodId): array
    {
        try {
            $customerId = $this->ensureCustomer($user);
            
            if (!$customerId) {
                throw new \Exception('No Stripe customer found');
            }

            $this->stripe->paymentMethods->attach(
                $paymentMethodId,
                ['customer' => $customerId]
            );

            // Set as default
            $this->stripe->customers->update(
                $customerId,
                ['invoice_settings' => ['default_payment_method' => $paymentMethodId]]
            );

            return [
                'success' => true,
                'message' => 'Payment method saved successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to attach payment method', [
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
     * Delete a saved payment method
     */
    public function deletePaymentMethod(User $user, string $paymentMethodId): array
    {
        if (!$user->stripe_customer_id) {
            return [
                'success' => false,
                'error' => 'No payment methods found'
            ];
        }

        try {
            // Verify ownership
            $paymentMethod = $this->stripe->paymentMethods->retrieve($paymentMethodId);
            
            if ($paymentMethod->customer !== $user->stripe_customer_id) {
                return [
                    'success' => false,
                    'error' => 'Payment method not found'
                ];
            }
            
            $this->stripe->paymentMethods->detach($paymentMethodId);
            
            Log::info('Payment method deleted', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId
            ]);

            return [
                'success' => true,
                'message' => 'Payment method deleted successfully'
            ];
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return [
                'success' => false,
                'error' => 'Payment method not found'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to delete payment method', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'error' => 'Failed to delete payment method'
            ];
        }
    }

    /**
     * Process payment for a booking
     * 
     * SECURITY: Uses database transaction with row locking
     */
    public function processBookingPayment(
        User $user,
        int $bookingId,
        string $paymentMethodId,
        callable $calculateTotal
    ): array {
        try {
            $customerId = $this->ensureCustomer($user);
            
            if (!$customerId) {
                throw new \Exception('Failed to create Stripe customer');
            }

            // Try to attach payment method (may already be attached)
            try {
                $this->stripe->paymentMethods->attach(
                    $paymentMethodId,
                    ['customer' => $customerId]
                );
            } catch (\Stripe\Exception\CardException $e) {
                Log::info('Payment method attach skipped: ' . $e->getMessage());
            }

            return DB::transaction(function () use ($user, $bookingId, $paymentMethodId, $calculateTotal, $customerId) {
                // Lock booking row
                $booking = Booking::where('id', $bookingId)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Verify ownership
                if ($booking->client_id !== $user->id) {
                    Log::warning('Unauthorized payment attempt', [
                        'user_id' => $user->id,
                        'booking_id' => $bookingId
                    ]);
                    throw new \Exception('Unauthorized - This booking does not belong to you');
                }

                // Check already paid
                if ($booking->payment_status === 'paid') {
                    throw new \Exception('Booking has already been paid');
                }

                // Calculate amount server-side
                $targetAmount = (float) $calculateTotal($booking);
                
                if ($targetAmount <= 0) {
                    Log::alert('Invalid booking amount', [
                        'booking_id' => $bookingId,
                        'amount' => $targetAmount
                    ]);
                    throw new \Exception('Invalid booking amount');
                }

                // Get card country for fee calculation
                $cardCountry = $this->getCardCountry($paymentMethodId);
                $processingFee = PaymentFeeService::calculateProcessingFee($targetAmount, $cardCountry);
                $adjustedAmount = PaymentFeeService::calculateAdjustedTotal($targetAmount, $cardCountry);
                $amountInCents = (int) round($adjustedAmount * 100);

                // Idempotency key prevents duplicate charges
                $idempotencyKey = 'payment_' . $booking->id . '_' . $user->id . '_' . now()->format('Ymd');

                $paymentIntent = $this->stripe->paymentIntents->create([
                    'amount' => $amountInCents,
                    'currency' => 'usd',
                    'customer' => $customerId,
                    'payment_method' => $paymentMethodId,
                    'off_session' => true,
                    'confirm' => true,
                    'payment_method_options' => [
                        'card' => [
                            'request_three_d_secure' => 'automatic',
                        ],
                    ],
                    'metadata' => [
                        'booking_id' => $bookingId,
                        'user_id' => $user->id,
                        'client_id' => $user->id
                    ],
                    'description' => 'Booking #' . $bookingId . ' - ' . $booking->service_type,
                    'receipt_email' => $user->email,
                ], [
                    'idempotency_key' => $idempotencyKey
                ]);

                Log::info('Payment processed successfully', [
                    'payment_intent_id' => $paymentIntent->id,
                    'booking_id' => $bookingId,
                    'amount' => $amountInCents
                ]);

                // Update booking
                $booking->update([
                    'payment_status' => 'paid',
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'payment_date' => now(),
                ]);

                // Create payment record
                Payment::create([
                    'client_id' => $user->id,
                    'booking_id' => $booking->id,
                    'transaction_id' => $paymentIntent->id,
                    'amount' => $amountInCents / 100,
                    'processing_fee' => $processingFee,
                    'status' => 'completed',
                    'payment_method' => 'credit_card',
                    'notes' => 'Booking payment for #' . $booking->id,
                    'paid_at' => now(),
                ]);

                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'payment_intent_id' => $paymentIntent->id
                ];
            });

        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Stripe card error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Card declined: ' . $e->getMessage()
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Payment failed: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Payment processing error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get card country for fee calculation
     */
    private function getCardCountry(string $paymentMethodId): string
    {
        try {
            $pm = $this->stripe->paymentMethods->retrieve($paymentMethodId);
            if ($pm && isset($pm->card) && isset($pm->card->country)) {
                return strtoupper($pm->card->country);
            }
        } catch (\Exception $e) {
            // Default to US
        }
        return 'US';
    }
}
