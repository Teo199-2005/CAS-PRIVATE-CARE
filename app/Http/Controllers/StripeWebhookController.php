<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhooks
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

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

                default:
                    Log::info('Unhandled webhook event type: ' . $event->type);
            }
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage(), [
                'event_type' => $event->type,
                'event_id' => $event->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
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
     */
    protected function handlePaymentIntentFailed($paymentIntent)
    {
        Log::warning('Payment intent failed', ['payment_intent_id' => $paymentIntent->id]);

        $booking = Booking::where('stripe_payment_intent_id', $paymentIntent->id)
            ->orWhere('payment_intent_id', $paymentIntent->id)
            ->first();
        
        if ($booking) {
            $booking->update([
                'payment_status' => 'failed'
            ]);

            Log::warning('Booking marked as failed payment', ['booking_id' => $booking->id]);
        }
    }
}
