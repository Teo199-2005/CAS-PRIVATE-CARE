<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Services\WebhookRetryService;

class StripeWebhookController extends Controller
{
    protected WebhookRetryService $retryService;

    public function __construct(WebhookRetryService $retryService)
    {
        $this->retryService = $retryService;
    }

    /**
     * Handle incoming Stripe webhooks
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (!$webhookSecret) {
            Log::error('Stripe webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sig, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe webhook invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe webhook invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        Log::info('Stripe webhook received: ' . $event->type, ['event_id' => $event->id]);

        try {
            switch ($event->type) {
                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;

                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;

                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;

                case 'charge.dispute.created':
                    $this->handleDisputeCreated($event->data->object);
                    break;

                case 'charge.dispute.closed':
                    $this->handleDisputeClosed($event->data->object);
                    break;

                case 'charge.refunded':
                    $this->handleChargeRefunded($event->data->object);
                    break;

                default:
                    Log::info('Unhandled webhook event type: ' . $event->type);
            }
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage(), [
                'event_type' => $event->type,
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString()
            ]);

            // Queue for retry if processing failed
            $this->retryService->queueForRetry(
                'stripe',
                $event->type,
                json_decode($payload, true),
                $e->getMessage()
            );

            // Return 200 to prevent Stripe from retrying (we handle our own retries)
            return response()->json(['received' => true, 'queued_for_retry' => true]);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Handle successful invoice payment
     */
    protected function handleInvoicePaymentSucceeded($invoice)
    {
        Log::info('Invoice payment succeeded', ['invoice_id' => $invoice->id]);

        $subscriptionId = $invoice->subscription;
        
        if ($subscriptionId) {
            $booking = Booking::where('stripe_subscription_id', $subscriptionId)->first();
            
            if ($booking) {
                $booking->update([
                    'payment_status' => 'paid',
                    'payment_date' => now(),
                    'next_payment_date' => $invoice->period_end ? date('Y-m-d H:i:s', $invoice->period_end) : null
                ]);

                Log::info('Booking payment updated', ['booking_id' => $booking->id]);

                // Send success email to client
                $client = User::find($booking->client_id);
                if ($client && $client->email) {
                    try {
                        // You can create a Mailable class or use a simple notification
                        Mail::raw(
                            "Your recurring payment of $" . number_format($invoice->amount_paid / 100, 2) . " was successful.\n\n" .
                            "Booking ID: {$booking->id}\n" .
                            "Next payment date: " . ($booking->next_payment_date ? date('F j, Y', strtotime($booking->next_payment_date)) : 'N/A'),
                            function ($message) use ($client) {
                                $message->to($client->email)
                                    ->subject('Payment Successful - Booking #' . $client->id);
                            }
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to send payment success email: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Handle failed invoice payment
     */
    protected function handleInvoicePaymentFailed($invoice)
    {
        Log::warning('Invoice payment failed', ['invoice_id' => $invoice->id]);

        $subscriptionId = $invoice->subscription;
        
        if ($subscriptionId) {
            $booking = Booking::where('stripe_subscription_id', $subscriptionId)->first();
            
            if ($booking) {
                $booking->update([
                    'payment_status' => 'failed'
                ]);

                Log::warning('Booking payment marked as failed', ['booking_id' => $booking->id]);

                // Send failure notification to client
                $client = User::find($booking->client_id);
                if ($client && $client->email) {
                    try {
                        Mail::raw(
                            "Your recurring payment for Booking #{$booking->id} has failed.\n\n" .
                            "Amount: $" . number_format($invoice->amount_due / 100, 2) . "\n" .
                            "Please update your payment method in your dashboard to continue your service.\n\n" .
                            "If you have questions, please contact support.",
                            function ($message) use ($client, $booking) {
                                $message->to($client->email)
                                    ->subject('Payment Failed - Action Required - Booking #' . $booking->id);
                            }
                        );
                    } catch (\Exception $e) {
                        Log::error('Failed to send payment failure email: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Handle subscription deletion/cancellation
     */
    protected function handleSubscriptionDeleted($subscription)
    {
        Log::info('Subscription deleted', ['subscription_id' => $subscription->id]);

        $booking = Booking::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($booking) {
            $booking->update([
                'auto_pay_enabled' => false,
                'payment_type' => 'one-time'
            ]);

            Log::info('Booking auto-pay disabled due to subscription cancellation', ['booking_id' => $booking->id]);

            // Notify client
            $client = User::find($booking->client_id);
            if ($client && $client->email) {
                try {
                    Mail::raw(
                        "Your recurring payment for Booking #{$booking->id} has been canceled.\n\n" .
                        "No further automatic charges will occur.\n" .
                        "You can re-enable auto-pay anytime from your dashboard.",
                        function ($message) use ($client, $booking) {
                            $message->to($client->email)
                                ->subject('Subscription Canceled - Booking #' . $booking->id);
                        }
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send subscription cancellation email: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Handle subscription updates
     */
    protected function handleSubscriptionUpdated($subscription)
    {
        Log::info('Subscription updated', ['subscription_id' => $subscription->id]);

        $booking = Booking::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($booking) {
            // Update next payment date
            if (isset($subscription->current_period_end)) {
                $booking->update([
                    'next_payment_date' => date('Y-m-d H:i:s', $subscription->current_period_end)
                ]);
            }

            // Check if subscription was paused or reactivated
            if ($subscription->status === 'active' && !$booking->auto_pay_enabled) {
                $booking->update(['auto_pay_enabled' => true]);
            } elseif ($subscription->status === 'canceled' && $booking->auto_pay_enabled) {
                $booking->update(['auto_pay_enabled' => false]);
            }
        }
    }

    /**
     * Handle successful one-time payment intent
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('Payment intent succeeded', ['payment_intent_id' => $paymentIntent->id]);

        $booking = Booking::where('stripe_payment_intent_id', $paymentIntent->id)
            ->orWhere('payment_intent_id', $paymentIntent->id)
            ->first();
        
        if ($booking) {
            $booking->update([
                'payment_status' => 'paid',
                'payment_date' => now()
            ]);

            Log::info('Booking marked as paid', ['booking_id' => $booking->id]);
        }
    }

    /**
     * Handle failed one-time payment intent
     * SECURITY: Logs critical alerts for admin monitoring
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        // SECURITY: Critical alert for failed payments - enables admin monitoring
        Log::alert('CRITICAL: Payment intent failed - requires review', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => isset($paymentIntent->amount) ? $paymentIntent->amount / 100 : 'unknown',
            'failure_code' => $paymentIntent->last_payment_error->code ?? 'unknown',
            'failure_message' => $paymentIntent->last_payment_error->message ?? 'Unknown error',
            'customer_id' => $paymentIntent->customer ?? 'unknown',
            'metadata' => $paymentIntent->metadata ?? [],
        ]);

        $booking = Booking::where('stripe_payment_intent_id', $paymentIntent->id)
            ->orWhere('payment_intent_id', $paymentIntent->id)
            ->first();
        
        if ($booking) {
            $booking->update([
                'payment_status' => 'failed'
            ]);

            Log::warning('Booking marked as failed payment', [
                'booking_id' => $booking->id,
                'client_id' => $booking->client_id,
                'payment_intent' => $paymentIntent->id,
            ]);
            
            // Log for admin dashboard visibility
            try {
                // Create a record for admin review if model exists
                if (class_exists('\App\Models\FailedPayment')) {
                    \App\Models\FailedPayment::create([
                        'booking_id' => $booking->id,
                        'client_id' => $booking->client_id,
                        'payment_intent_id' => $paymentIntent->id,
                        'amount' => isset($paymentIntent->amount) ? $paymentIntent->amount / 100 : 0,
                        'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Unknown',
                        'requires_action' => true,
                        'created_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Model may not exist, continue
                Log::info('FailedPayment model not available for tracking');
            }
        }
    }

    /**
     * Handle dispute created event
     * CRITICAL: Disputes require immediate admin attention
     * 
     * @param object $dispute Stripe dispute object
     */
    protected function handleDisputeCreated($dispute)
    {
        // CRITICAL: Log at highest priority for admin dashboard
        Log::critical('STRIPE DISPUTE CREATED - IMMEDIATE ACTION REQUIRED', [
            'dispute_id' => $dispute->id,
            'charge_id' => $dispute->charge ?? 'unknown',
            'amount' => isset($dispute->amount) ? $dispute->amount / 100 : 'unknown',
            'currency' => $dispute->currency ?? 'usd',
            'reason' => $dispute->reason ?? 'unknown',
            'status' => $dispute->status ?? 'unknown',
            'evidence_due_by' => isset($dispute->evidence_details->due_by) 
                ? date('Y-m-d H:i:s', $dispute->evidence_details->due_by) 
                : 'unknown',
        ]);

        // Try to find the related booking via the charge
        $booking = null;
        if (isset($dispute->payment_intent)) {
            $booking = Booking::where('stripe_payment_intent_id', $dispute->payment_intent)
                ->orWhere('payment_intent_id', $dispute->payment_intent)
                ->first();
        }

        // Mark booking as disputed if found
        if ($booking) {
            $booking->update([
                'payment_status' => 'disputed',
                'dispute_id' => $dispute->id,
                'dispute_reason' => $dispute->reason ?? 'unknown',
                'dispute_created_at' => now(),
            ]);

            Log::warning('Booking marked as disputed', [
                'booking_id' => $booking->id,
                'dispute_id' => $dispute->id,
            ]);

            // Send urgent notification to admin
            try {
                \App\Services\NotificationService::create(
                    null, // All admins
                    'URGENT: Payment Dispute Created',
                    "A dispute has been filed for Booking #{$booking->id}. Amount: $" . 
                        number_format(($dispute->amount ?? 0) / 100, 2) . ". Reason: " . 
                        ($dispute->reason ?? 'unknown') . ". Evidence due by: " .
                        (isset($dispute->evidence_details->due_by) 
                            ? date('F j, Y', $dispute->evidence_details->due_by) 
                            : 'unknown'),
                    'Stripe',
                    'urgent'
                );
            } catch (\Exception $e) {
                Log::error('Failed to create dispute notification: ' . $e->getMessage());
            }

            // Send email alert to client
            $client = User::find($booking->client_id);
            if ($client && $client->email) {
                try {
                    Mail::raw(
                        "A dispute has been filed for your Booking #{$booking->id}.\n\n" .
                        "Amount: $" . number_format(($dispute->amount ?? 0) / 100, 2) . "\n" .
                        "Reason: " . ($dispute->reason ?? 'Not specified') . "\n\n" .
                        "Our team will review this dispute and contact you if we need additional information.\n\n" .
                        "If you have any questions, please contact support.",
                        function ($message) use ($client, $booking) {
                            $message->to($client->email)
                                ->subject('Payment Dispute Notice - Booking #' . $booking->id);
                        }
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send dispute email: ' . $e->getMessage());
                }
            }
        } else {
            Log::warning('Dispute received but no matching booking found', [
                'dispute_id' => $dispute->id,
                'payment_intent' => $dispute->payment_intent ?? 'none',
            ]);
        }
    }

    /**
     * Handle dispute closed event
     * 
     * @param object $dispute Stripe dispute object
     */
    protected function handleDisputeClosed($dispute)
    {
        Log::info('Stripe dispute closed', [
            'dispute_id' => $dispute->id,
            'status' => $dispute->status ?? 'unknown',
            'outcome' => $dispute->status ?? 'unknown',
        ]);

        // Find and update the related booking
        $booking = null;
        if (isset($dispute->payment_intent)) {
            $booking = Booking::where('stripe_payment_intent_id', $dispute->payment_intent)
                ->orWhere('payment_intent_id', $dispute->payment_intent)
                ->first();
        }

        if ($booking) {
            $newStatus = 'paid'; // Default to paid if dispute won
            
            if ($dispute->status === 'lost') {
                $newStatus = 'refunded';
                Log::warning('Dispute lost - booking refunded', ['booking_id' => $booking->id]);
            } elseif ($dispute->status === 'won') {
                Log::info('Dispute won - payment retained', ['booking_id' => $booking->id]);
            }

            $booking->update([
                'payment_status' => $newStatus,
                'dispute_resolved_at' => now(),
                'dispute_outcome' => $dispute->status ?? 'unknown',
            ]);

            // Notify admin of outcome
            try {
                \App\Services\NotificationService::create(
                    null,
                    'Dispute Resolved: ' . ucfirst($dispute->status ?? 'Unknown'),
                    "Dispute for Booking #{$booking->id} has been " . ($dispute->status ?? 'resolved') . ".",
                    'Stripe',
                    'normal'
                );
            } catch (\Exception $e) {
                Log::error('Failed to create dispute resolution notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Handle charge refunded event
     * 
     * @param object $charge Stripe charge object
     */
    protected function handleChargeRefunded($charge)
    {
        Log::info('Charge refunded', [
            'charge_id' => $charge->id,
            'amount_refunded' => isset($charge->amount_refunded) ? $charge->amount_refunded / 100 : 'unknown',
            'refund_reason' => $charge->refunds->data[0]->reason ?? 'not specified',
        ]);

        // Find the related booking
        $booking = null;
        if (isset($charge->payment_intent)) {
            $booking = Booking::where('stripe_payment_intent_id', $charge->payment_intent)
                ->orWhere('payment_intent_id', $charge->payment_intent)
                ->first();
        }

        if ($booking) {
            // Determine if full or partial refund
            $originalAmount = $charge->amount ?? 0;
            $refundedAmount = $charge->amount_refunded ?? 0;
            $isFullRefund = $refundedAmount >= $originalAmount;

            $booking->update([
                'payment_status' => $isFullRefund ? 'refunded' : 'partially_refunded',
                'refund_amount' => $refundedAmount / 100,
                'refund_date' => now(),
                'refund_reason' => $charge->refunds->data[0]->reason ?? 'not specified',
            ]);

            Log::info('Booking marked as ' . ($isFullRefund ? 'fully' : 'partially') . ' refunded', [
                'booking_id' => $booking->id,
                'refund_amount' => $refundedAmount / 100,
            ]);

            // Notify client of refund
            $client = User::find($booking->client_id);
            if ($client && $client->email) {
                try {
                    Mail::raw(
                        "A refund has been processed for your Booking #{$booking->id}.\n\n" .
                        "Refund Amount: $" . number_format($refundedAmount / 100, 2) . "\n" .
                        "This refund should appear on your statement within 5-10 business days.\n\n" .
                        "If you have any questions, please contact support.",
                        function ($message) use ($client, $booking) {
                            $message->to($client->email)
                                ->subject('Refund Processed - Booking #' . $booking->id);
                        }
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send refund email: ' . $e->getMessage());
                }
            }
        }
    }
}
