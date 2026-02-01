<?php

namespace App\Http\Controllers\Stripe;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\StripePaymentService;
use App\Services\AuditLogService;
use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\TimeTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles admin payment operations
 * 
 * Endpoints:
 * - POST /api/admin/stripe/process-payment - Process a booking payment
 * - POST /api/admin/stripe/process-payouts - Process weekly payouts
 * - GET /api/admin/stripe/payment-history/{userId} - Get user payment history
 * - POST /api/admin/stripe/refund - Process refund
 */
class AdminPaymentController extends Controller
{
    use ApiResponseTrait;

    protected StripePaymentService $stripeService;
    protected AuditLogService $auditLog;

    public function __construct(StripePaymentService $stripeService, AuditLogService $auditLog)
    {
        $this->stripeService = $stripeService;
        $this->auditLog = $auditLog;
    }

    /**
     * Process payment for a booking (admin)
     * POST /api/admin/stripe/process-payment
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|string'
        ]);

        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->user_type, ['admin', 'super_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $booking = Booking::with('user')->findOrFail($request->booking_id);

        try {
            DB::beginTransaction();

            $result = $this->stripeService->chargePayment(
                $booking->user,
                $request->amount,
                $request->payment_method_id,
                [
                    'booking_id' => $booking->id,
                    'service_type' => $booking->service_type,
                    'processed_by' => $admin->id
                ]
            );

            if (!$result['success']) {
                DB::rollBack();
                return $this->errorResponse($result['error'] ?? 'Payment failed', 400);
            }

            // Update booking payment status
            $booking->payment_status = 'paid';
            $booking->payment_intent_id = $result['payment_intent_id'];
            $booking->save();

            // Create payment record
            Payment::create([
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'amount' => $request->amount,
                'payment_intent_id' => $result['payment_intent_id'],
                'payment_method_id' => $request->payment_method_id,
                'status' => 'succeeded',
                'processed_by' => $admin->id
            ]);

            // Audit log
            $this->auditLog->log('payment_processed', [
                'booking_id' => $booking->id,
                'amount' => $request->amount,
                'admin_id' => $admin->id
            ]);

            DB::commit();

            Log::info('Admin processed payment', [
                'booking_id' => $booking->id,
                'amount' => $request->amount,
                'admin_id' => $admin->id
            ]);

            return $this->successResponse([
                'message' => 'Payment processed successfully',
                'payment_intent_id' => $result['payment_intent_id']
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admin payment processing failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Payment processing failed');
        }
    }

    /**
     * Process weekly payouts to caregivers/housekeepers
     * POST /api/admin/stripe/process-payouts
     */
    public function processWeeklyPayouts(Request $request)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->user_type, ['admin', 'super_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $request->validate([
            'week_ending' => 'nullable|date',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        try {
            $weekEnding = $request->week_ending ?? now()->endOfWeek()->format('Y-m-d');
            $weekStart = now()->parse($weekEnding)->startOfWeek()->format('Y-m-d');

            // Get time tracking records for the week
            $query = TimeTracking::whereBetween('date', [$weekStart, $weekEnding])
                ->where('payout_status', 'pending')
                ->with(['user', 'booking']);

            if ($request->user_ids) {
                $query->whereIn('user_id', $request->user_ids);
            }

            $timeRecords = $query->get()->groupBy('user_id');

            $results = [];
            $errors = [];

            foreach ($timeRecords as $userId => $records) {
                $user = User::find($userId);
                $profile = $this->getStaffProfile($user);

                if (!$profile || !$profile->stripe_account_id) {
                    $errors[] = [
                        'user_id' => $userId,
                        'error' => 'Stripe account not set up'
                    ];
                    continue;
                }

                $totalAmount = $records->sum('amount');
                
                if ($totalAmount < 1) {
                    continue;
                }

                $result = $this->stripeService->createTransfer(
                    $profile->stripe_account_id,
                    $totalAmount,
                    [
                        'user_id' => $userId,
                        'week_ending' => $weekEnding,
                        'record_count' => $records->count()
                    ]
                );

                if ($result['success']) {
                    // Update time records
                    TimeTracking::whereIn('id', $records->pluck('id'))
                        ->update([
                            'payout_status' => 'paid',
                            'payout_id' => $result['transfer_id'],
                            'paid_at' => now()
                        ]);

                    $results[] = [
                        'user_id' => $userId,
                        'amount' => $totalAmount,
                        'transfer_id' => $result['transfer_id']
                    ];
                } else {
                    $errors[] = [
                        'user_id' => $userId,
                        'error' => $result['error']
                    ];
                }
            }

            // Audit log
            $this->auditLog->log('weekly_payouts_processed', [
                'week_ending' => $weekEnding,
                'successful' => count($results),
                'failed' => count($errors),
                'admin_id' => $admin->id
            ]);

            return $this->successResponse([
                'message' => 'Payouts processed',
                'week_ending' => $weekEnding,
                'successful' => $results,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Weekly payout processing failed', [
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Payout processing failed');
        }
    }

    /**
     * Get payment history for a user
     * GET /api/admin/stripe/payment-history/{userId}
     */
    public function getPaymentHistory(Request $request, int $userId)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->user_type, ['admin', 'super_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $user = User::findOrFail($userId);

        try {
            $payments = Payment::where('user_id', $userId)
                ->with(['booking', 'processedBy'])
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            // Get Stripe payment intents if customer exists
            $stripePayments = [];
            if ($user->stripe_customer_id) {
                $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
                $paymentIntents = $stripe->paymentIntents->all([
                    'customer' => $user->stripe_customer_id,
                    'limit' => 20
                ]);

                $stripePayments = array_map(function ($pi) {
                    return [
                        'id' => $pi->id,
                        'amount' => $pi->amount / 100,
                        'status' => $pi->status,
                        'created' => date('Y-m-d H:i:s', $pi->created),
                        'metadata' => $pi->metadata->toArray()
                    ];
                }, $paymentIntents->data);
            }

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'stripe_customer_id' => $user->stripe_customer_id
                ],
                'payments' => $payments,
                'stripe_payments' => $stripePayments
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch payment history', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to fetch payment history');
        }
    }

    /**
     * Process refund
     * POST /api/admin/stripe/refund
     */
    public function processRefund(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'amount' => 'nullable|numeric|min:0.50',
            'reason' => 'nullable|string|in:duplicate,fraudulent,requested_by_customer'
        ]);

        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->user_type, ['admin', 'super_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            $refundParams = [
                'payment_intent' => $request->payment_intent_id
            ];

            if ($request->amount) {
                $refundParams['amount'] = (int)($request->amount * 100);
            }

            if ($request->reason) {
                $refundParams['reason'] = $request->reason;
            }

            $refund = $stripe->refunds->create($refundParams);

            // Update payment record
            $payment = Payment::where('payment_intent_id', $request->payment_intent_id)->first();
            if ($payment) {
                $payment->status = $refund->status === 'succeeded' ? 'refunded' : 'partial_refund';
                $payment->refund_id = $refund->id;
                $payment->refund_amount = $refund->amount / 100;
                $payment->save();
            }

            // Audit log
            $this->auditLog->log('refund_processed', [
                'payment_intent_id' => $request->payment_intent_id,
                'amount' => $refund->amount / 100,
                'reason' => $request->reason,
                'admin_id' => $admin->id
            ]);

            Log::info('Refund processed', [
                'payment_intent_id' => $request->payment_intent_id,
                'refund_id' => $refund->id,
                'admin_id' => $admin->id
            ]);

            return $this->successResponse([
                'message' => 'Refund processed successfully',
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount / 100
            ]);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Refund processing failed');
        }
    }

    /**
     * Get payout history for staff
     * GET /api/admin/stripe/payout-history/{userId}
     */
    public function getPayoutHistory(Request $request, int $userId)
    {
        $admin = Auth::user();
        
        if (!$admin || !in_array($admin->user_type, ['admin', 'super_admin'])) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $user = User::findOrFail($userId);
        $profile = $this->getStaffProfile($user);

        if (!$profile || !$profile->stripe_account_id) {
            return $this->successResponse([
                'payouts' => [],
                'message' => 'No Stripe account found for this user'
            ]);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('stripe.secret_key'));
            
            $transfers = $stripe->transfers->all([
                'destination' => $profile->stripe_account_id,
                'limit' => 50
            ]);

            $formatted = array_map(function ($transfer) {
                return [
                    'id' => $transfer->id,
                    'amount' => $transfer->amount / 100,
                    'currency' => $transfer->currency,
                    'created' => date('Y-m-d H:i:s', $transfer->created),
                    'status' => $transfer->reversed ? 'reversed' : 'completed',
                    'metadata' => $transfer->metadata->toArray()
                ];
            }, $transfers->data);

            return $this->successResponse([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'stripe_account_id' => $profile->stripe_account_id
                ],
                'payouts' => $formatted
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch payout history', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse('Failed to fetch payout history');
        }
    }

    /**
     * Get staff profile (caregiver or housekeeper)
     */
    protected function getStaffProfile($user)
    {
        if ($user->user_type === 'caregiver') {
            return Caregiver::where('user_id', $user->id)->first();
        } elseif ($user->user_type === 'housekeeper') {
            return Housekeeper::where('user_id', $user->id)->first();
        }
        
        return null;
    }
}
