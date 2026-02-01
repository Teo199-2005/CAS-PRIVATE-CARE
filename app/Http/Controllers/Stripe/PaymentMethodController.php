<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Handles client payment method management
 * 
 * Endpoints:
 * - POST /api/stripe/setup-intent - Create setup intent
 * - POST /api/stripe/payment-methods - Save payment method
 * - GET /api/stripe/payment-methods - List payment methods
 * - DELETE /api/stripe/payment-methods/{id} - Delete payment method
 */
class PaymentMethodController extends Controller
{
    use ApiResponseTrait;

    protected StripePaymentService $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Create Setup Intent for client to save card
     * POST /api/stripe/setup-intent
     */
    public function createSetupIntent(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'client') {
            return $this->errorResponse('Unauthorized', 403);
        }

        try {
            $result = $this->stripeService->createSetupIntent($user);

            if ($result['success']) {
                return $this->successResponse([
                    'client_secret' => $result['client_secret']
                ]);
            }

            return $this->errorResponse($result['error'] ?? 'Failed to create setup intent');
        } catch (\Exception $e) {
            Log::error('Setup intent creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to create setup intent');
        }
    }

    /**
     * Save payment method after Setup Intent confirms
     * POST /api/stripe/payment-methods
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string'
        ]);

        $user = Auth::user();
        
        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            // Ensure customer exists
            if (!$user->stripe_customer_id) {
                $customer = $stripe->customers->create([
                    'email' => $user->email,
                    'name' => $user->name,
                    'metadata' => [
                        'user_id' => $user->id,
                        'user_type' => $user->user_type
                    ]
                ]);
                $user->stripe_customer_id = $customer->id;
                $user->save();
            }
            
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

            Log::info('Payment method saved', [
                'user_id' => $user->id,
                'payment_method_id' => $request->payment_method_id
            ]);

            return $this->successResponse([
                'message' => 'Payment method saved successfully'
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            Log::warning('Card error saving payment method', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Failed to save payment method', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to save payment method');
        }
    }

    /**
     * Get client's payment methods
     * GET /api/stripe/payment-methods
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->stripe_customer_id) {
            return $this->successResponse([
                'payment_methods' => [],
                'default_payment_method' => null
            ]);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            // Get all payment methods
            $paymentMethods = $stripe->paymentMethods->all([
                'customer' => $user->stripe_customer_id,
                'type' => 'card'
            ]);

            // Get default payment method
            $customer = $stripe->customers->retrieve($user->stripe_customer_id);
            $defaultPaymentMethod = $customer->invoice_settings->default_payment_method ?? null;

            // Format response
            $formatted = array_map(function ($pm) use ($defaultPaymentMethod) {
                return [
                    'id' => $pm->id,
                    'brand' => $pm->card->brand,
                    'last4' => $pm->card->last4,
                    'exp_month' => $pm->card->exp_month,
                    'exp_year' => $pm->card->exp_year,
                    'is_default' => $pm->id === $defaultPaymentMethod,
                    'created_at' => date('Y-m-d H:i:s', $pm->created)
                ];
            }, $paymentMethods->data);

            return $this->successResponse([
                'payment_methods' => $formatted,
                'default_payment_method' => $defaultPaymentMethod
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch payment methods', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to fetch payment methods');
        }
    }

    /**
     * Delete a payment method
     * DELETE /api/stripe/payment-methods/{paymentMethodId}
     */
    public function destroy(Request $request, string $paymentMethodId)
    {
        $user = Auth::user();
        
        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            // Verify the payment method belongs to this user
            $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);
            
            if ($paymentMethod->customer !== $user->stripe_customer_id) {
                return $this->errorResponse('Unauthorized', 403);
            }
            
            // Detach the payment method
            $stripe->paymentMethods->detach($paymentMethodId);

            Log::info('Payment method deleted', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId
            ]);

            return $this->successResponse([
                'message' => 'Payment method deleted successfully'
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return $this->errorResponse('Payment method not found', 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete payment method', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to delete payment method');
        }
    }

    /**
     * Set a payment method as default
     * PUT /api/stripe/payment-methods/{paymentMethodId}/default
     */
    public function setDefault(Request $request, string $paymentMethodId)
    {
        $user = Auth::user();
        
        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            // Verify the payment method belongs to this user
            $paymentMethod = $stripe->paymentMethods->retrieve($paymentMethodId);
            
            if ($paymentMethod->customer !== $user->stripe_customer_id) {
                return $this->errorResponse('Unauthorized', 403);
            }
            
            // Set as default
            $stripe->customers->update(
                $user->stripe_customer_id,
                ['invoice_settings' => ['default_payment_method' => $paymentMethodId]]
            );

            Log::info('Default payment method updated', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId
            ]);

            return $this->successResponse([
                'message' => 'Default payment method updated'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to set default payment method', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to update default payment method');
        }
    }
}
