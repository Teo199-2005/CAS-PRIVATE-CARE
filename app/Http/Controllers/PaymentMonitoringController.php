<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\TimeTracking;
use App\Models\PayoutTransaction;
use App\Models\Caregiver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentMonitoringController extends Controller
{
    /**
     * Get complete money flow overview
     */
    public function getMoneyFlowDashboard()
    {
        try {
            // Get today's data
            $today = Carbon::today();
            
            // CLIENT PAYMENTS IN
            $todayPaymentsIn = Payment::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('amount');
            
            $totalPaymentsIn = Payment::where('status', 'completed')
                ->sum('amount');
            
            // CONTRACTOR PAYOUTS OUT
            $todayPayoutsOut = TimeTracking::whereDate('paid_at', $today)
                ->where('payment_status', 'paid')
                ->sum('caregiver_earnings');
            
            $totalPayoutsOut = TimeTracking::where('payment_status', 'paid')
                ->sum('caregiver_earnings');
            
            // PENDING PAYOUTS
            $pendingPayouts = TimeTracking::where('payment_status', 'pending')
                ->sum('caregiver_earnings');
            
            // COMMISSION BREAKDOWN (simplified for current database structure)
            $pendingMarketingCommission = 0; // Will be tracked when marketing partner system is implemented
            $pendingTrainingCommission = 0; // Will be tracked when training center system is implemented
            
            $platformCommission = TimeTracking::where('payment_status', 'paid')
                ->sum('agency_commission');
            
            // BALANCE CALCULATION
            $expectedBalance = $totalPaymentsIn - $totalPayoutsOut;
            
            // GET STRIPE BALANCE (if configured)
            $stripeBalance = $this->getStripeBalance();
            
            // RECENT TRANSACTIONS
            $recentPayments = Payment::with('booking.client')
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'client_name' => $payment->booking->client->name ?? 'Unknown',
                        'stripe_payment_intent_id' => $payment->stripe_payment_intent_id,
                        'created_at' => $payment->created_at->format('M d, Y g:i A'),
                        'type' => 'payment_in'
                    ];
                });
            
            $recentPayouts = TimeTracking::with('caregiver.user')
                ->where('payment_status', 'paid')
                ->whereNotNull('paid_at')
                ->orderBy('paid_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($tracking) {
                    return [
                        'id' => $tracking->id,
                        'amount' => $tracking->caregiver_earnings,
                        'caregiver_name' => $tracking->caregiver->user->name ?? 'Unknown',
                        'stripe_transfer_id' => $tracking->stripe_transfer_id ?? 'N/A',
                        'created_at' => $tracking->paid_at->format('M d, Y g:i A'),
                        'type' => 'payout_out'
                    ];
                });
            
            // MERGE AND SORT BY DATE
            $recentTransactions = $recentPayments->concat($recentPayouts)
                ->sortByDesc(function($transaction) {
                    return Carbon::parse($transaction['created_at']);
                })
                ->take(20)
                ->values();
            
            // FAILED PAYOUTS (simplified - will use PayoutTransaction table when migrated)
            $failedPayouts = [];
            
            // CAREGIVER BALANCES
            $caregiverBalances = Caregiver::with(['user', 'timeTrackings'])
                ->get()
                ->map(function($caregiver) {
                    $pendingEarnings = $caregiver->timeTrackings()
                        ->where('payment_status', 'pending')
                        ->sum('caregiver_earnings');
                    
                    $totalPaid = $caregiver->timeTrackings()
                        ->where('payment_status', 'paid')
                        ->sum('caregiver_earnings');
                    
                    $lastPayment = $caregiver->timeTrackings()
                        ->where('payment_status', 'paid')
                        ->whereNotNull('paid_at')
                        ->orderBy('paid_at', 'desc')
                        ->first();
                    
                    return [
                        'id' => $caregiver->id,
                        'name' => $caregiver->user->name ?? 'Unknown',
                        'email' => $caregiver->user->email ?? 'N/A',
                        'pending_balance' => $pendingEarnings,
                        'total_paid' => $totalPaid,
                        'last_payment_date' => $lastPayment ? $lastPayment->paid_at->format('M d, Y') : null,
                        'last_payment_amount' => $lastPayment ? $lastPayment->caregiver_earnings : 0,
                        'stripe_account_id' => $caregiver->stripe_connect_id ?? null,
                        'bank_connected' => !empty($caregiver->stripe_connect_id)
                    ];
                })
                ->sortByDesc('pending_balance')
                ->values();
            
            return response()->json([
                'success' => true,
                'today' => [
                    'payments_in' => $todayPaymentsIn,
                    'payouts_out' => $todayPayoutsOut,
                    'net_change' => $todayPaymentsIn - $todayPayoutsOut
                ],
                'totals' => [
                    'total_payments_in' => $totalPaymentsIn,
                    'total_payouts_out' => $totalPayoutsOut,
                    'pending_payouts' => $pendingPayouts,
                    'expected_balance' => $expectedBalance,
                    'stripe_balance' => $stripeBalance,
                    'balance_difference' => $stripeBalance ? ($stripeBalance - $expectedBalance) : null
                ],
                'commissions' => [
                    'pending_marketing' => $pendingMarketingCommission,
                    'pending_training' => $pendingTrainingCommission,
                    'platform_total' => $platformCommission
                ],
                'recent_transactions' => $recentTransactions,
                'failed_payouts' => $failedPayouts,
                'caregiver_balances' => $caregiverBalances
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load monitoring dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get detailed payout verification
     */
    public function verifyPayoutDetails($payoutId)
    {
        try {
            $tracking = TimeTracking::with(['caregiver.user', 'booking.client'])
                ->findOrFail($payoutId);
            
            // Check if Stripe transfer exists
            $stripeVerified = false;
            $stripeStatus = null;
            
            if ($tracking->stripe_transfer_id) {
                $stripeVerified = true;
                $stripeStatus = $this->verifyStripeTransfer($tracking->stripe_transfer_id);
            }
            
            return response()->json([
                'success' => true,
                'payout' => [
                    'id' => $tracking->id,
                    'amount' => $tracking->caregiver_earnings,
                    'payment_status' => $tracking->payment_status,
                    'paid_at' => $tracking->paid_at ? $tracking->paid_at->format('M d, Y g:i A') : null,
                    'caregiver' => [
                        'name' => $tracking->caregiver->user->name ?? 'Unknown',
                        'email' => $tracking->caregiver->user->email ?? 'N/A',
                        'stripe_account_id' => $tracking->caregiver->stripe_connect_id ?? null
                    ],
                    'stripe' => [
                        'transfer_id' => $tracking->stripe_transfer_id,
                        'verified' => $stripeVerified,
                        'status' => $stripeStatus
                    ],
                    'work_details' => [
                        'hours_worked' => $tracking->hours_worked,
                        'hourly_rate' => $tracking->hourly_rate,
                        'clock_in' => $tracking->clock_in_time ? $tracking->clock_in_time->format('M d, Y g:i A') : null,
                        'clock_out' => $tracking->clock_out_time ? $tracking->clock_out_time->format('M d, Y g:i A') : null
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify payout: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get reconciliation report
     */
    public function getReconciliationReport(Request $request)
    {
        try {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfMonth();
            
            // CLIENT PAYMENTS
            $clientPayments = Payment::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'completed')
                ->sum('amount');
            
            // CONTRACTOR PAYOUTS
            $contractorPayouts = TimeTracking::whereBetween('paid_at', [$startDate, $endDate])
                ->where('payment_status', 'paid')
                ->sum('caregiver_earnings');
            
            // MARKETING COMMISSIONS (simplified for current database)
            $marketingCommissions = 0; // Will be tracked when marketing_commission_paid_at column exists
            
            // TRAINING COMMISSIONS (simplified for current database)
            $trainingCommissions = 0; // Will be tracked when training_commission_paid_at column exists
            
            // PLATFORM FEES
            $platformFees = TimeTracking::whereBetween('paid_at', [$startDate, $endDate])
                ->where('payment_status', 'paid')
                ->sum('agency_commission');
            
            // CALCULATIONS
            $totalOut = $contractorPayouts + $marketingCommissions + $trainingCommissions;
            $netBalance = $clientPayments - $totalOut;
            
            // PENDING OBLIGATIONS (simplified)
            $pendingCaregiver = TimeTracking::where('payment_status', 'pending')
                ->sum('caregiver_earnings');
            
            $pendingMarketing = 0; // Future implementation
            $pendingTraining = 0; // Future implementation
            
            return response()->json([
                'success' => true,
                'period' => [
                    'start' => $startDate->format('M d, Y'),
                    'end' => $endDate->format('M d, Y')
                ],
                'money_in' => [
                    'client_payments' => $clientPayments
                ],
                'money_out' => [
                    'caregiver_payouts' => $contractorPayouts,
                    'marketing_commissions' => $marketingCommissions,
                    'training_commissions' => $trainingCommissions,
                    'total_out' => $totalOut
                ],
                'net_balance' => $netBalance,
                'platform_fees' => $platformFees,
                'pending_obligations' => [
                    'caregivers' => $pendingCaregiver,
                    'marketing' => $pendingMarketing,
                    'training' => $pendingTraining,
                    'total_pending' => $pendingCaregiver + $pendingMarketing + $pendingTraining
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reconciliation report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get Stripe balance
     */
    private function getStripeBalance()
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $balance = $stripe->balance->retrieve();
            
            // Get available balance in dollars
            $availableBalance = 0;
            foreach ($balance->available as $balanceItem) {
                if ($balanceItem->currency === 'usd') {
                    $availableBalance = $balanceItem->amount / 100; // Convert cents to dollars
                }
            }
            
            return $availableBalance;
            
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve Stripe balance: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verify Stripe transfer status
     */
    private function verifyStripeTransfer($transferId)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $transfer = $stripe->transfers->retrieve($transferId);
            
            return [
                'status' => $transfer->status,
                'amount' => $transfer->amount / 100,
                'destination' => $transfer->destination,
                'created' => Carbon::createFromTimestamp($transfer->created)->format('M d, Y g:i A')
            ];
            
        } catch (\Exception $e) {
            \Log::error('Failed to verify Stripe transfer: ' . $e->getMessage());
            return null;
        }
    }
}
