<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\PaymentIntent;
use App\Models\User;
use App\Models\Booking;
use App\Models\TimeTracking;

class ClientPaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret'));
    }

    /**
     * Get all saved payment methods for the current client
     */
    public function getPaymentMethods(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get or create Stripe customer
            $stripeCustomerId = $this->getOrCreateStripeCustomer($user);

            // Retrieve all payment methods for this customer
            $paymentMethods = PaymentMethod::all([
                'customer' => $stripeCustomerId,
                'type' => 'card',
            ]);

            return response()->json([
                'success' => true,
                'payment_methods' => $paymentMethods->data,
                'customer_id' => $stripeCustomerId
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching payment methods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment methods',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a Payment Intent for checkout (NEW - with Payment Element)
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'amount' => 'required|numeric|min:1', // Minimum 1 cent (Stripe minimum is 50 cents)
            'currency' => 'nullable|string',
            'customer_email' => 'nullable|string|email'
        ]);

        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get booking
            $booking = Booking::find($request->booking_id);
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Verify booking belongs to user
            if ($booking->client_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to booking'
                ], 403);
            }

            // Ensure amount meets Stripe minimum (50 cents)
            $amountInCents = max(50, (int)$request->amount);

            // Get or create Stripe customer
            $stripeCustomerId = $this->getOrCreateStripeCustomer($user);

            // Create Payment Intent
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents, // Amount in cents
                'currency' => $request->currency ?? 'usd',
                'customer' => $stripeCustomerId,
                'description' => "Payment for Booking #{$booking->id} - {$booking->duty_type}",
                'metadata' => [
                    'booking_id' => $booking->id,
                    'client_id' => $user->id,
                    'client_name' => $user->name,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true, // Enable all payment methods (Card, Link, etc.)
                ],
                'receipt_email' => $request->customer_email ?? $user->email,
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating payment intent: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a SetupIntent for adding new payment method
     */
    public function createSetupIntent(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get or create Stripe customer
            $stripeCustomerId = $this->getOrCreateStripeCustomer($user);

            // Create SetupIntent
            $setupIntent = SetupIntent::create([
                'customer' => $stripeCustomerId,
                'payment_method_types' => ['card'],
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $setupIntent->client_secret,
                'setup_intent_id' => $setupIntent->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating setup intent: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create setup intent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Attach payment method to customer
     */
    public function attachPaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get or create Stripe customer
            $stripeCustomerId = $this->getOrCreateStripeCustomer($user);

            // Attach payment method to customer
            $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);
            $paymentMethod->attach(['customer' => $stripeCustomerId]);

            // Set as default payment method
            Customer::update($stripeCustomerId, [
                'invoice_settings' => [
                    'default_payment_method' => $request->payment_method_id,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method saved successfully',
                'payment_method' => $paymentMethod
            ]);

        } catch (\Exception $e) {
            \Log::error('Error attaching payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Charge a saved payment method
     */
    public function chargeSavedMethod(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'payment_method_id' => 'required|string',
            'password' => 'required|string',
            'amount' => 'required|numeric|min:0.50'
        ]);

        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incorrect password'
                ], 401);
            }

            // Get booking
            $booking = Booking::find($request->booking_id);
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Verify booking belongs to user
            if ($booking->client_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to booking'
                ], 403);
            }

            // Get Stripe customer
            $stripeCustomerId = $this->getOrCreateStripeCustomer($user);

            // Create Payment Intent
            $amountInCents = round($request->amount * 100);
            
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => 'usd',
                'customer' => $stripeCustomerId,
                'payment_method' => $request->payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'description' => "Payment for Booking #{$booking->id} - {$booking->duty_type}",
                'metadata' => [
                    'booking_id' => $booking->id,
                    'client_id' => $user->id,
                    'client_name' => $user->name,
                ]
            ]);

            // Update booking payment status
            $booking->update([
                'payment_status' => 'paid',
                'stripe_payment_intent_id' => $paymentIntent->id,
                'payment_date' => now()
            ]);

            // Create time tracking entry for payment record
            TimeTracking::create([
                'caregiver_id' => $booking->caregiver_id,
                'booking_id' => $booking->id,
                'clock_in' => $booking->start_date,
                'clock_out' => $booking->end_date,
                'total_hours' => $booking->hours ?? 0,
                'hourly_rate' => $booking->hourly_rate ?? 40,
                'total_earned' => $request->amount,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'stripe',
                'stripe_payment_intent_id' => $paymentIntent->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment_intent_id' => $paymentIntent->id,
                'amount_charged' => $request->amount
            ]);

        } catch (\Stripe\Exception\CardException $e) {
            // Card was declined
            \Log::error('Card declined: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Card declined: ' . $e->getError()->message
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Error charging saved payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a saved payment method
     */
    public function deletePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Detach payment method
            $paymentMethod = PaymentMethod::retrieve($request->payment_method_id);
            $paymentMethod->detach();

            return response()->json([
                'success' => true,
                'message' => 'Payment method removed successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payment method',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get or create Stripe customer for user
     */
    private function getOrCreateStripeCustomer(User $user)
    {
        // Check if user already has a Stripe customer ID
        if ($user->stripe_customer_id) {
            try {
                // Verify the customer exists in Stripe
                Customer::retrieve($user->stripe_customer_id);
                return $user->stripe_customer_id;
            } catch (\Exception $e) {
                // Customer doesn't exist, create a new one
                \Log::warning("Stripe customer {$user->stripe_customer_id} not found, creating new one");
            }
        }

        // Create new Stripe customer
        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
                'role' => $user->role
            ]
        ]);

        // Save customer ID to user record
        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }
}
