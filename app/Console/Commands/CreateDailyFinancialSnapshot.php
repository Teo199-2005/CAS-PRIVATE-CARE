<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TimeTracking;
use App\Models\PayoutTransaction;
use App\Models\DailyBalanceSnapshot;
use App\Models\Payment;
use Carbon\Carbon;

class CreateDailyFinancialSnapshot extends Command
{
    protected $signature = 'finance:snapshot {date?}';
    protected $description = 'Create daily financial snapshot for reconciliation';

    public function handle()
    {
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : Carbon::yesterday();
        
        $this->info("Creating financial snapshot for {$date->format('Y-m-d')}...");
        
        // Calculate all financial metrics
        $totalRevenue = Payment::where('status', 'completed')
            ->whereDate('created_at', '<=', $date)
            ->sum('amount');
        
        $caregiverPaid = PayoutTransaction::where('status', 'completed')
            ->whereDate('completed_at', '<=', $date)
            ->sum('amount');
        
        $caregiverPayables = TimeTracking::whereNull('paid_at')
            ->whereDate('work_date', '<=', $date)
            ->sum('caregiver_earnings');
        
        $marketingPaid = TimeTracking::whereNotNull('marketing_commission_paid_at')
            ->whereDate('marketing_commission_paid_at', '<=', $date)
            ->sum('marketing_partner_commission');
        
        $marketingPayables = TimeTracking::whereNull('marketing_commission_paid_at')
            ->whereNotNull('marketing_partner_id')
            ->whereDate('work_date', '<=', $date)
            ->sum('marketing_partner_commission');
        
        $trainingPaid = TimeTracking::whereNotNull('training_commission_paid_at')
            ->whereDate('training_commission_paid_at', '<=', $date)
            ->sum('training_center_commission');
        
        $trainingPayables = TimeTracking::whereNull('training_commission_paid_at')
            ->whereNotNull('training_center_user_id')
            ->whereDate('work_date', '<=', $date)
            ->sum('training_center_commission');
        
        $platformRevenue = $totalRevenue - ($caregiverPaid + $marketingPaid + $trainingPaid + $caregiverPayables + $marketingPayables + $trainingPayables);
        
        // Get Stripe balance (if API keys configured)
        $stripeBalance = null;
        $stripePending = null;
        $stripeReconciled = false;
        
        try {
            if (config('stripe.secret')) {
                $stripe = new \Stripe\StripeClient(config('stripe.secret'));
                $balance = $stripe->balance->retrieve();
                $stripeBalance = $balance->available[0]->amount / 100; // Convert from cents
                $stripePending = $balance->pending[0]->amount / 100;
                $stripeReconciled = true;
            }
        } catch (\Exception $e) {
            $this->warn("Could not fetch Stripe balance: " . $e->getMessage());
        }
        
        // Check for discrepancies
        $discrepancies = [];
        $expectedBalance = $totalRevenue - $caregiverPaid - $marketingPaid - $trainingPaid;
        
        if ($stripeBalance !== null && abs($expectedBalance - $stripeBalance) > 1) {
            $discrepancies[] = "Stripe balance mismatch: Expected $expectedBalance, Actual $stripeBalance";
        }
        
        // Create snapshot
        $snapshot = DailyBalanceSnapshot::updateOrCreate(
            ['snapshot_date' => $date->format('Y-m-d')],
            [
                'total_revenue' => $totalRevenue,
                'caregiver_payables' => $caregiverPayables,
                'caregiver_paid' => $caregiverPaid,
                'marketing_commission_payables' => $marketingPayables,
                'marketing_commission_paid' => $marketingPaid,
                'training_commission_payables' => $trainingPayables,
                'training_commission_paid' => $trainingPaid,
                'platform_revenue' => $platformRevenue,
                'stripe_balance' => $stripeBalance,
                'stripe_pending' => $stripePending,
                'stripe_reconciled' => $stripeReconciled,
                'discrepancies' => !empty($discrepancies) ? json_encode($discrepancies) : null,
            ]
        );
        
        $this->info("✅ Snapshot created successfully!");
        $this->table(
            ['Metric', 'Amount'],
            [
                ['Total Revenue', '$' . number_format($totalRevenue, 2)],
                ['Caregiver Paid', '$' . number_format($caregiverPaid, 2)],
                ['Caregiver Payables', '$' . number_format($caregiverPayables, 2)],
                ['Marketing Paid', '$' . number_format($marketingPaid, 2)],
                ['Marketing Payables', '$' . number_format($marketingPayables, 2)],
                ['Platform Revenue', '$' . number_format($platformRevenue, 2)],
                ['Stripe Balance', $stripeBalance ? '$' . number_format($stripeBalance, 2) : 'N/A'],
            ]
        );
        
        if (!empty($discrepancies)) {
            $this->error("⚠️  DISCREPANCIES FOUND:");
            foreach ($discrepancies as $discrepancy) {
                $this->error("  - $discrepancy");
            }
        }
    }
}
