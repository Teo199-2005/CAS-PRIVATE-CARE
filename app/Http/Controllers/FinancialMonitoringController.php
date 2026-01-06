<?php

namespace App\Http\Controllers;

use App\Models\PayoutTransaction;
use App\Models\DailyBalanceSnapshot;
use App\Models\FinancialLedger;
use App\Models\TimeTracking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialMonitoringController extends Controller
{
    /**
     * Get financial monitoring dashboard data
     */
    public function getDashboardData()
    {
        // Today's payouts
        $todayPayouts = PayoutTransaction::with('caregiver.user', 'adminUser')
            ->whereDate('completed_at', today())
            ->where('status', 'completed')
            ->get();
        
        // Failed transactions (last 30 days)
        $failedTransactions = PayoutTransaction::with('caregiver.user')
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Pending payouts
        $pendingPayouts = PayoutTransaction::with('caregiver.user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Latest balance snapshot
        $latestSnapshot = DailyBalanceSnapshot::orderBy('snapshot_date', 'desc')->first();
        
        // Calculate current outstanding amounts
        $caregiverPayables = TimeTracking::whereNull('paid_at')->sum('caregiver_earnings');
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $totalPaidOut = PayoutTransaction::where('status', 'completed')->sum('amount');
        
        // Monthly stats
        $monthlyPayouts = PayoutTransaction::whereMonth('completed_at', now()->month)
            ->where('status', 'completed')
            ->sum('amount');
        
        $monthlyCaregiversPaid = PayoutTransaction::whereMonth('completed_at', now()->month)
            ->where('status', 'completed')
            ->distinct('caregiver_id')
            ->count('caregiver_id');
        
        // Discrepancy alerts
        $alerts = [];
        
        if ($latestSnapshot && $latestSnapshot->discrepancies) {
            $alerts[] = [
                'type' => 'error',
                'message' => 'Balance discrepancy detected',
                'details' => json_decode($latestSnapshot->discrepancies),
                'date' => $latestSnapshot->snapshot_date,
            ];
        }
        
        if ($failedTransactions->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$failedTransactions->count()} failed payouts in last 30 days",
                'action' => 'Review failed transactions',
            ];
        }
        
        if ($caregiverPayables > 5000) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'High outstanding payables: $' . number_format($caregiverPayables, 2),
                'action' => 'Consider processing payments',
            ];
        }
        
        return response()->json([
            'success' => true,
            'summary' => [
                'total_revenue' => number_format($totalRevenue, 2),
                'total_paid_out' => number_format($totalPaidOut, 2),
                'platform_revenue' => number_format($totalRevenue - $totalPaidOut, 2),
                'outstanding_payables' => number_format($caregiverPayables, 2),
                'monthly_payouts' => number_format($monthlyPayouts, 2),
                'monthly_caregivers_paid' => $monthlyCaregiversPaid,
            ],
            'todays_payouts' => $todayPayouts->map(function($payout) {
                return [
                    'id' => $payout->id,
                    'caregiver' => $payout->caregiver->user->name,
                    'amount' => number_format($payout->amount, 2),
                    'stripe_id' => $payout->stripe_transfer_id,
                    'sessions' => $payout->sessions_count,
                    'hours' => $payout->total_hours,
                    'approved_by' => $payout->adminUser->name ?? 'System',
                    'completed_at' => $payout->completed_at->format('g:i A'),
                ];
            }),
            'failed_transactions' => $failedTransactions->map(function($payout) {
                return [
                    'id' => $payout->id,
                    'caregiver' => $payout->caregiver->user->name,
                    'amount' => number_format($payout->amount, 2),
                    'reason' => $payout->failure_reason,
                    'failed_at' => $payout->failed_at->format('M d, Y g:i A'),
                ];
            }),
            'pending_payouts' => $pendingPayouts->map(function($payout) {
                return [
                    'id' => $payout->id,
                    'caregiver' => $payout->caregiver->user->name,
                    'amount' => number_format($payout->amount, 2),
                    'initiated_at' => $payout->initiated_at->diffForHumans(),
                ];
            }),
            'latest_snapshot' => $latestSnapshot ? [
                'date' => $latestSnapshot->snapshot_date->format('M d, Y'),
                'total_revenue' => number_format($latestSnapshot->total_revenue, 2),
                'caregiver_paid' => number_format($latestSnapshot->caregiver_paid, 2),
                'caregiver_payables' => number_format($latestSnapshot->caregiver_payables, 2),
                'platform_revenue' => number_format($latestSnapshot->platform_revenue, 2),
                'stripe_balance' => $latestSnapshot->stripe_balance ? number_format($latestSnapshot->stripe_balance, 2) : null,
                'stripe_reconciled' => $latestSnapshot->stripe_reconciled,
                'discrepancies' => $latestSnapshot->discrepancies,
            ] : null,
            'alerts' => $alerts,
        ]);
    }
    
    /**
     * Get detailed payout history
     */
    public function getPayoutHistory(Request $request)
    {
        $query = PayoutTransaction::with('caregiver.user', 'adminUser');
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->caregiver_id) {
            $query->where('caregiver_id', $request->caregiver_id);
        }
        
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $payouts = $query->orderBy('created_at', 'desc')->paginate(50);
        
        return response()->json($payouts);
    }
    
    /**
     * Get payout verification details
     */
    public function getPayoutDetails($id)
    {
        $payout = PayoutTransaction::with([
            'caregiver.user',
            'adminUser',
            'verifications.verifiedBy',
            'ledgerEntries'
        ])->findOrFail($id);
        
        // Get the time tracking records that were paid
        $timeTrackings = TimeTracking::whereIn('id', $payout->time_tracking_ids)->get();
        
        return response()->json([
            'payout' => $payout,
            'time_trackings' => $timeTrackings->map(function($tt) {
                return [
                    'id' => $tt->id,
                    'work_date' => $tt->work_date->format('M d, Y'),
                    'hours' => $tt->hours_worked,
                    'earnings' => number_format($tt->caregiver_earnings, 2),
                    'paid_at' => $tt->paid_at ? $tt->paid_at->format('M d, Y g:i A') : null,
                ];
            }),
        ]);
    }
}
