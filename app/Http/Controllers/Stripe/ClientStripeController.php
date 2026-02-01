<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\ProcessPaymentRequest;
use App\Http\Requests\Stripe\SavePaymentMethodRequest;
use App\Http\Requests\Stripe\DeletePaymentMethodRequest;
use App\Services\Stripe\StripeClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Client Stripe Controller
 * 
 * Handles client-facing Stripe operations:
 * - Setup Intents
 * - Payment Methods
 * - Booking Payments
 * 
 * @package App\Http\Controllers\Stripe
 */
class ClientStripeController extends Controller
{
    public function __construct(
        private readonly StripeClientService $clientService
    ) {}

    /**
     * Create Setup Intent for client to save card
     * POST /api/stripe/create-setup-intent
     */
    public function createSetupIntent(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->clientService->createSetupIntent($user);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'client_secret' => $result['client_secret']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Save payment method after Setup Intent confirms
     * POST /api/stripe/save-payment-method
     */
    public function savePaymentMethod(SavePaymentMethodRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        $result = $this->clientService->attachPaymentMethod($user, $request->payment_method_id);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Get client's saved payment methods
     * GET /api/stripe/payment-methods
     */
    public function getPaymentMethods(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $result = $this->clientService->getPaymentMethods($user);

        return response()->json($result);
    }

    /**
     * Delete a saved payment method
     * DELETE /api/stripe/payment-methods/{id}
     */
    public function deletePaymentMethod(Request $request, string $paymentMethodId): JsonResponse
    {
        $user = Auth::user();

        if ($user->user_type !== 'client') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->clientService->deletePaymentMethod($user, $paymentMethodId);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Process payment for a booking
     * POST /api/stripe/setup-intent
     */
    public function processPayment(ProcessPaymentRequest $request): JsonResponse
    {
        $user = Auth::user();

        // Get booking total calculator
        $calculateTotal = function ($booking) {
            return app(\App\Http\Controllers\BookingController::class)->calculateBookingTotal($booking);
        };

        $result = $this->clientService->processBookingPayment(
            $user,
            $request->booking_id,
            $request->payment_method_id,
            $calculateTotal
        );

        if ($result['success']) {
            return response()->json($result);
        }

        $statusCode = str_contains($result['error'] ?? '', 'Unauthorized') ? 403 : 400;
        return response()->json([
            'success' => false,
            'message' => $result['error']
        ], $statusCode);
    }
}
