<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\Booking;
use App\Models\TimeTracking;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Admin Payment Management Controller
 * 
 * Extracted from AdminController for better maintainability.
 * Handles payment stats, transactions, and payouts.
 * 
 * Phase 2 Refactoring: Controller Extraction
 */
class AdminPaymentController extends Controller
{
    protected StripePaymentService $stripeService;

    public function __construct(StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Get payment statistics for dashboard
     */
    public function getStats()
    {
        try {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();
            $thisYear = Carbon::now()->startOfYear();

            // Calculate revenue from bookings
            $monthlyRevenue = Booking::where('status', 'confirmed')
                ->where('created_at', '>=', $thisMonth)
                ->sum('total_cost');

            $yearlyRevenue = Booking::where('status', 'confirmed')
                ->where('created_at', '>=', $thisYear)
                ->sum('total_cost');

            // Calculate pending payments
            $pendingPayments = Booking::where('payment_status', 'pending')
                ->where('status', 'confirmed')
                ->count();

            // Calculate contractor payouts this month
            $caregiverPayouts = DB::table('caregiver_payments')
                ->where('created_at', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('amount');

            $housekeeperPayouts = DB::table('housekeeper_payments')
                ->where('created_at', '>=', $thisMonth)
                ->where('status', 'completed')
                ->sum('amount');

            return response()->json([
                'success' => true,
                'stats' => [
                    'monthly_revenue' => $monthlyRevenue,
                    'yearly_revenue' => $yearlyRevenue,
                    'pending_payments' => $pendingPayments,
                    'caregiver_payouts' => $caregiverPayouts,
                    'housekeeper_payouts' => $housekeeperPayouts,
                    'total_contractor_payouts' => $caregiverPayouts + $housekeeperPayouts,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch payment stats', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment statistics'
            ], 500);
        }
    }

    /**
     * Get recent transactions
     */
    public function getTransactions(Request $request)
    {
        try {
            $limit = $request->get('limit', 50);
            $type = $request->get('type', 'all'); // all, incoming, outgoing

            $transactions = collect();

            // Get incoming payments (from clients)
            if ($type === 'all' || $type === 'incoming') {
                $bookingPayments = Booking::with('client')
                    ->where('payment_status', 'paid')
                    ->orderBy('payment_date', 'desc')
                    ->limit($limit)
                    ->get()
                    ->map(function ($booking) {
                        return [
                            'id' => 'booking_' . $booking->id,
                            'type' => 'incoming',
                            'amount' => $booking->total_cost,
                            'description' => 'Booking payment from ' . ($booking->client->name ?? 'Unknown'),
                            'date' => $booking->payment_date,
                            'status' => 'completed',
                        ];
                    });
                $transactions = $transactions->merge($bookingPayments);
            }

            // Get outgoing payments (to contractors)
            if ($type === 'all' || $type === 'outgoing') {
                if (DB::getSchemaBuilder()->hasTable('caregiver_payments')) {
                    $caregiverPayments = DB::table('caregiver_payments')
                        ->join('users', 'caregiver_payments.user_id', '=', 'users.id')
                        ->select('caregiver_payments.*', 'users.name')
                        ->orderBy('caregiver_payments.created_at', 'desc')
                        ->limit($limit)
                        ->get()
                        ->map(function ($payment) {
                            return [
                                'id' => 'caregiver_' . $payment->id,
                                'type' => 'outgoing',
                                'amount' => $payment->amount,
                                'description' => 'Caregiver payout to ' . $payment->name,
                                'date' => $payment->created_at,
                                'status' => $payment->status,
                            ];
                        });
                    $transactions = $transactions->merge($caregiverPayments);
                }
            }

            // Sort by date descending
            $transactions = $transactions->sortByDesc('date')->take($limit)->values();

            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch transactions', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch transactions'
            ], 500);
        }
    }

    /**
     * Get caregiver salaries/earnings
     */
    public function getCaregiverSalaries()
    {
        try {
            $caregivers = Caregiver::with('user')
                ->get()
                ->map(function ($caregiver) {
                    // Calculate earnings from time tracking
                    $hoursWorked = TimeTracking::where('user_id', $caregiver->user_id)
                        ->whereNotNull('clock_out_time')
                        ->sum('hours_worked');

                    $hourlyRate = $caregiver->hourly_rate ?? 28.00;
                    $totalEarnings = $hoursWorked * $hourlyRate;

                    // Get paid amount
                    $paidAmount = DB::table('caregiver_payments')
                        ->where('user_id', $caregiver->user_id)
                        ->where('status', 'completed')
                        ->sum('amount');

                    return [
                        'id' => $caregiver->id,
                        'user_id' => $caregiver->user_id,
                        'name' => $caregiver->user->name ?? 'Unknown',
                        'email' => $caregiver->user->email ?? '',
                        'hours_worked' => round($hoursWorked, 2),
                        'hourly_rate' => $hourlyRate,
                        'total_earnings' => round($totalEarnings, 2),
                        'paid_amount' => round($paidAmount, 2),
                        'pending_amount' => round($totalEarnings - $paidAmount, 2),
                        'stripe_connected' => !empty($caregiver->user->stripe_connect_id),
                    ];
                });

            return response()->json([
                'success' => true,
                'caregivers' => $caregivers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch caregiver salaries', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch caregiver salaries'
            ], 500);
        }
    }

    /**
     * Get housekeeper salaries/earnings
     */
    public function getHousekeeperSalaries()
    {
        try {
            $housekeepers = Housekeeper::with('user')
                ->get()
                ->map(function ($housekeeper) {
                    $hoursWorked = TimeTracking::where('user_id', $housekeeper->user_id)
                        ->whereNotNull('clock_out_time')
                        ->sum('hours_worked');

                    $hourlyRate = $housekeeper->hourly_rate ?? 25.00;
                    $totalEarnings = $hoursWorked * $hourlyRate;

                    $paidAmount = DB::table('housekeeper_payments')
                        ->where('user_id', $housekeeper->user_id)
                        ->where('status', 'completed')
                        ->sum('amount');

                    return [
                        'id' => $housekeeper->id,
                        'user_id' => $housekeeper->user_id,
                        'name' => $housekeeper->user->name ?? 'Unknown',
                        'email' => $housekeeper->user->email ?? '',
                        'hours_worked' => round($hoursWorked, 2),
                        'hourly_rate' => $hourlyRate,
                        'total_earnings' => round($totalEarnings, 2),
                        'paid_amount' => round($paidAmount, 2),
                        'pending_amount' => round($totalEarnings - $paidAmount, 2),
                        'stripe_connected' => !empty($housekeeper->user->stripe_connect_id),
                    ];
                });

            return response()->json([
                'success' => true,
                'housekeepers' => $housekeepers
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch housekeeper salaries', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch housekeeper salaries'
            ], 500);
        }
    }

    /**
     * Process caregiver payout
     */
    public function payCaregiver(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);

            if (empty($user->stripe_connect_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Caregiver has not connected their Stripe account'
                ], 400);
            }

            // Process payout via Stripe
            $result = $this->stripeService->transferToCaregiver(
                $user,
                $validated['amount'],
                'Caregiver salary payment'
            );

            if ($result) {
                // Record payment
                DB::table('caregiver_payments')->insert([
                    'user_id' => $user->id,
                    'amount' => $validated['amount'],
                    'status' => 'completed',
                    'stripe_transfer_id' => $result['transfer_id'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info('Caregiver paid successfully', [
                    'user_id' => $user->id,
                    'amount' => $validated['amount']
                ]);

                return response()->json(['success' => true, 'message' => 'Payment processed successfully']);
            }

            return response()->json(['success' => false, 'message' => 'Payment failed'], 500);
        } catch (\Exception $e) {
            Log::error('Caregiver payment failed', [
                'user_id' => $validated['user_id'],
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process housekeeper payout
     */
    public function payHousekeeper(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);

            if (empty($user->stripe_connect_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Housekeeper has not connected their Stripe account'
                ], 400);
            }

            $result = $this->stripeService->transferToCaregiver(
                $user,
                $validated['amount'],
                'Housekeeper salary payment'
            );

            if ($result) {
                DB::table('housekeeper_payments')->insert([
                    'user_id' => $user->id,
                    'amount' => $validated['amount'],
                    'status' => 'completed',
                    'stripe_transfer_id' => $result['transfer_id'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info('Housekeeper paid successfully', [
                    'user_id' => $user->id,
                    'amount' => $validated['amount']
                ]);

                return response()->json(['success' => true, 'message' => 'Payment processed successfully']);
            }

            return response()->json(['success' => false, 'message' => 'Payment failed'], 500);
        } catch (\Exception $e) {
            Log::error('Housekeeper payment failed', [
                'user_id' => $validated['user_id'],
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
