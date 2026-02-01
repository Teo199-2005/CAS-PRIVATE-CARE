<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\AdminRefundRequest;
use App\Services\Stripe\StripeAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin Stripe Controller
 * 
 * Handles admin-only Stripe operations:
 * - View payments
 * - Process refunds
 * - Manage connected accounts
 * - Dashboard statistics
 * 
 * @package App\Http\Controllers\Stripe
 */
class AdminStripeController extends Controller
{
    public function __construct(
        private readonly StripeAdminService $adminService
    ) {}

    /**
     * Check if user is admin or staff
     */
    private function isAdminOrStaff(): bool
    {
        $user = Auth::user();
    return $user && in_array($user->user_type, ['admin', 'staff', 'adminstaff']);
    }

    /**
     * Get payment details
     * GET /api/admin/stripe/payments/{paymentIntentId}
     */
    public function getPayment(Request $request, string $paymentIntentId): JsonResponse
    {
        if (!$this->isAdminOrStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->adminService->getPaymentDetails($paymentIntentId);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * List recent payments
     * GET /api/admin/stripe/payments
     */
    public function listPayments(Request $request): JsonResponse
    {
        if (!$this->isAdminOrStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = min((int) $request->get('limit', 25), 100);
        $result = $this->adminService->listRecentPayments($limit);

        return response()->json($result);
    }

    /**
     * Process a refund
     * POST /api/admin/stripe/refund
     */
    public function processRefund(AdminRefundRequest $request): JsonResponse
    {
        $result = $this->adminService->processRefund(
            $request->payment_intent_id,
            $request->amount,
            $request->reason ?? 'requested_by_customer'
        );

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * List connected accounts
     * GET /api/admin/stripe/accounts
     */
    public function listConnectedAccounts(Request $request): JsonResponse
    {
        if (!$this->isAdminOrStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = min((int) $request->get('limit', 100), 100);
        $result = $this->adminService->listConnectedAccounts($limit);

        return response()->json($result);
    }

    /**
     * Get connected account details
     * GET /api/admin/stripe/accounts/{accountId}
     */
    public function getConnectedAccount(Request $request, string $accountId): JsonResponse
    {
        if (!$this->isAdminOrStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->adminService->getConnectedAccountDetails($accountId);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Sync connected account status
     * POST /api/admin/stripe/accounts/{accountId}/sync
     */
    public function syncAccountStatus(Request $request, string $accountId): JsonResponse
    {
        if (!$this->isAdminOrStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->adminService->syncConnectedAccountStatus($accountId);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Get platform balance
     * GET /api/admin/stripe/balance
     */
    public function getPlatformBalance(Request $request): JsonResponse
    {
        if (!$this->isAdminOrStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $result = $this->adminService->getPlatformBalance();

        return response()->json($result);
    }

    /**
     * List recent transfers
     * GET /api/admin/stripe/transfers
     */
    public function listTransfers(Request $request): JsonResponse
    {
        if (!$this->isAdminOrStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $limit = min((int) $request->get('limit', 25), 100);
        $result = $this->adminService->listRecentTransfers($limit);

        return response()->json($result);
    }

    /**
     * Get dashboard statistics
     * GET /api/admin/stripe/stats
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        if (!$this->isAdminOrStaff()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $result = $this->adminService->getDashboardStats($startDate, $endDate);

        return response()->json($result);
    }
}
