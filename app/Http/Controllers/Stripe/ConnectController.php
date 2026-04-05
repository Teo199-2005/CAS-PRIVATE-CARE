<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Stripe Connect for caregiver payouts is retired (W-2 payroll / Gusto direct deposit).
 * Client payment flows use other Stripe endpoints.
 */
class ConnectController extends Controller
{
    use ApiResponseTrait;

    private const RETIRED_MESSAGE = 'Stripe Connect is no longer used for caregiver payouts. Complete Payroll onboarding in your caregiver dashboard for W-2 direct deposit and tax information.';

    public function __construct(protected StripePaymentService $stripeService) {}

    public function createOnboardingLink(Request $request)
    {
        Auth::user();

        return $this->errorResponse(self::RETIRED_MESSAGE, 410);
    }

    public function checkStatus(Request $request)
    {
        Auth::user();

        return $this->errorResponse(self::RETIRED_MESSAGE, 410);
    }

    public function connectBankAccount(Request $request)
    {
        Auth::user();

        return $this->errorResponse(self::RETIRED_MESSAGE, 410);
    }

    public function createAccountSession(Request $request)
    {
        Auth::user();

        return $this->errorResponse(self::RETIRED_MESSAGE, 410);
    }

    public function getPayoutMethods(Request $request)
    {
        Auth::user();

        return $this->errorResponse(self::RETIRED_MESSAGE, 410);
    }

    public function getBalance(Request $request)
    {
        Auth::user();

        return $this->errorResponse(self::RETIRED_MESSAGE, 410);
    }
}
