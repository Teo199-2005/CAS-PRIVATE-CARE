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
 * Caregiver Stripe Connect Controller
 * 
 * Handles caregiver Stripe Connect operations:
 * - Account creation/onboarding
 * - Callback handling
 * - Status checking
 * - Dashboard access
 * 
 * @package App\Http\Controllers\Stripe
 */
class CaregiverStripeController extends Controller
{
    public function __construct(
        private readonly StripeConnectService $connectService
    ) {}

    /**
     * Start Stripe Connect onboarding for caregiver
     * POST /api/caregiver/stripe/onboard
     */
    public function startOnboarding(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->connectService->createCaregiverAccount($user);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'url' => $result['url']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Handle Stripe Connect callback after onboarding
     * GET /caregiver/stripe/callback
     */
    public function handleCallback(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect('/login')->with('error', 'Please log in to continue');
        }

        $result = $this->connectService->handleCaregiverCallback($user);

        if ($result['success'] && $result['status'] === 'active') {
            return redirect('/caregiver/dashboard')
                ->with('success', 'Stripe account connected successfully! You can now receive payments.');
        }

        if ($result['status'] === 'pending') {
            return redirect('/caregiver/dashboard')
                ->with('info', 'Stripe account setup is pending verification.');
        }

        return redirect('/caregiver/dashboard')
            ->with('warning', 'Stripe setup incomplete. Please complete all required information.');
    }

    /**
     * Handle Stripe Connect refresh (re-authentication)
     * GET /caregiver/stripe/refresh
     */
    public function handleRefresh(Request $request): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return redirect('/login');
        }

        $result = $this->connectService->createCaregiverAccount($user);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return redirect($result['url']);
        }

        return redirect('/caregiver/dashboard')
            ->with('error', 'Failed to refresh Stripe onboarding. Please try again.');
    }

    /**
     * Get Stripe account status
     * GET /api/caregiver/stripe/status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->connectService->getCaregiverAccountStatus($user);

        return response()->json($result);
    }

    /**
     * Get Stripe Express Dashboard link
     * GET /api/caregiver/stripe/dashboard
     */
    public function getDashboardLink(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->connectService->getDashboardLink($user);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'url' => $result['url']
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error']
        ], 400);
    }

    /**
     * Get account balance
     * GET /api/caregiver/stripe/balance
     */
    public function getBalance(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || $user->user_type !== 'caregiver' || !$user->stripe_account_id) {
            return response()->json([
                'success' => false,
                'error' => 'No Stripe account connected'
            ], 400);
        }

        $result = $this->connectService->getAccountBalance($user->stripe_account_id);

        return response()->json($result);
    }
}
