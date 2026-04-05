<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Services\Stripe\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Housekeeper Stripe Connect Controller
 * 
 * Handles housekeeper Stripe Connect operations:
 * - Account creation/onboarding
 * - Callback handling
 * - Status checking
 * - Dashboard access
 * 
 * @package App\Http\Controllers\Stripe
 */
class HousekeeperStripeController extends Controller
{
    public function __construct(
        private readonly StripeConnectService $connectService
    ) {}

    /**
     * Housekeeper / training-center Stripe Connect is decommissioned.
     */
    private function decommissionedJson(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'This account type is no longer supported for Stripe Connect.',
        ], 410);
    }

    /**
     * Start Stripe Connect onboarding for housekeeper
     * POST /api/housekeeper/stripe/onboard
     */
    public function startOnboarding(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->decommissionedJson();
    }

    /**
     * Handle Stripe Connect callback after onboarding
     * GET /housekeeper/stripe/callback
     */
    public function handleCallback(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login')->with('error', 'Please log in to continue');
        }

        return redirect('/login')->with('error', 'Housekeeper Stripe Connect is no longer available.');
    }

    /**
     * Handle Stripe Connect refresh (re-authentication)
     * GET /housekeeper/stripe/refresh
     */
    public function handleRefresh(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect('/login');
        }

        if ($request->wantsJson()) {
            return $this->decommissionedJson();
        }

        return redirect('/login')->with('error', 'Housekeeper Stripe Connect is no longer available.');
    }

    /**
     * Get Stripe account status
     * GET /api/housekeeper/stripe/status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->decommissionedJson();
    }

    /**
     * Get Stripe Express Dashboard link
     * GET /api/housekeeper/stripe/dashboard
     */
    public function getDashboardLink(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->decommissionedJson();
    }

    /**
     * Get account balance
     * GET /api/housekeeper/stripe/balance
     */
    public function getBalance(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'housekeeper') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->decommissionedJson();
    }
}
