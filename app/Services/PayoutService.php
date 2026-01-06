<?php

namespace App\Services;

use App\Models\Caregiver;
use App\Models\TimeTracking;
use App\Models\PayoutTransaction;
use App\Models\FinancialLedger;
use App\Models\PayoutVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    /**
     * Process payout with full verification and audit trail
     */
    public function processCaregiverPayout($caregiverId, $amount, $adminUserId)
    {
        DB::beginTransaction();
        
        try {
            // Step 1: Pre-payment verification
            $verification = $this->prePaymentVerification($caregiverId, $amount);
            
            if (!$verification['passed']) {
                throw new \Exception('Pre-payment verification failed: ' . json_encode($verification['errors']));
            }
            
            $caregiver = Caregiver::with('user')->findOrFail($caregiverId);
            
            // Step 2: Get unpaid time tracking records
            $unpaidRecords = TimeTracking::where('caregiver_id', $caregiverId)
                ->whereNull('paid_at')
                ->get();
            
            if ($unpaidRecords->isEmpty()) {
                throw new \Exception('No unpaid records found');
            }
            
            $calculatedAmount = $unpaidRecords->sum('caregiver_earnings');
            
            // Verify amount matches (allow 1 cent difference for rounding)
            if (abs($calculatedAmount - $amount) > 0.01) {
                throw new \Exception("Amount mismatch: Expected $calculatedAmount, got $amount");
            }
            
            // Step 3: Create payout transaction record
            $payoutTransaction = PayoutTransaction::create([
                'caregiver_id' => $caregiverId,
                'admin_user_id' => $adminUserId,
                'amount' => $amount,
                'currency' => 'usd',
                'stripe_connect_id' => $caregiver->user->stripe_connect_id,
                'status' => 'processing',
                'time_tracking_ids' => $unpaidRecords->pluck('id')->toArray(),
                'sessions_count' => $unpaidRecords->count(),
                'total_hours' => $unpaidRecords->sum('hours_worked'),
                'initiated_at' => now(),
            ]);
            
            // Step 4: Create Stripe transfer
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $transfer = $stripe->transfers->create([
                    'amount' => intval($amount * 100), // Convert to cents
                    'currency' => 'usd',
                    'destination' => $caregiver->user->stripe_connect_id,
                    'description' => "Payout #{$payoutTransaction->id} - {$unpaidRecords->count()} sessions",
                    'metadata' => [
                        'payout_transaction_id' => $payoutTransaction->id,
                        'caregiver_id' => $caregiverId,
                        'sessions_count' => $unpaidRecords->count(),
                        'total_hours' => $unpaidRecords->sum('hours_worked'),
                    ]
                ]);
                
                // Update with Stripe transfer ID
                $payoutTransaction->update([
                    'stripe_transfer_id' => $transfer->id,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                
                Log::info('Stripe transfer created successfully', [
                    'payout_id' => $payoutTransaction->id,
                    'transfer_id' => $transfer->id,
                    'amount' => $amount,
                    'caregiver' => $caregiver->user->name,
                ]);
                
            } catch (\Exception $e) {
                // Stripe API failed
                $payoutTransaction->update([
                    'status' => 'failed',
                    'failure_reason' => $e->getMessage(),
                    'failed_at' => now(),
                ]);
                
                throw new \Exception('Stripe transfer failed: ' . $e->getMessage());
            }
            
            // Step 5: Mark time tracking records as paid
            foreach ($unpaidRecords as $record) {
                $record->update([
                    'paid_at' => now(),
                    'payment_status' => 'paid',
                    'payout_transaction_id' => $payoutTransaction->id, // Link to payout
                ]);
            }
            
            // Step 6: Create financial ledger entries (double-entry bookkeeping)
            $this->createLedgerEntries($payoutTransaction, $unpaidRecords);
            
            // Step 7: Post-payment verification
            $postVerification = $this->postPaymentVerification($payoutTransaction);
            
            DB::commit();
            
            // Step 8: Log verification results
            PayoutVerification::create([
                'payout_transaction_id' => $payoutTransaction->id,
                'verification_type' => 'post_payment',
                'passed' => $postVerification['passed'],
                'checks_performed' => $postVerification['checks'],
                'results' => $postVerification['results'],
                'verified_by' => $adminUserId,
            ]);
            
            return [
                'success' => true,
                'payout_transaction_id' => $payoutTransaction->id,
                'stripe_transfer_id' => $payoutTransaction->stripe_transfer_id,
                'amount' => $amount,
                'caregiver' => $caregiver->user->name,
                'sessions_paid' => $unpaidRecords->count(),
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Payout processing failed', [
                'caregiver_id' => $caregiverId,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Pre-payment verification checks
     */
    private function prePaymentVerification($caregiverId, $amount)
    {
        $errors = [];
        $checks = [];
        
        // Check 1: Caregiver exists and has bank connected
        $caregiver = Caregiver::with('user')->find($caregiverId);
        if (!$caregiver) {
            $errors[] = 'Caregiver not found';
        } elseif (empty($caregiver->user->stripe_connect_id)) {
            $errors[] = 'Bank account not connected';
        }
        $checks['caregiver_valid'] = empty($errors);
        
        // Check 2: Unpaid records exist
        $unpaidRecords = TimeTracking::where('caregiver_id', $caregiverId)
            ->whereNull('paid_at')
            ->get();
        if ($unpaidRecords->isEmpty()) {
            $errors[] = 'No unpaid time records';
        }
        $checks['unpaid_records_exist'] = !$unpaidRecords->isEmpty();
        
        // Check 3: Amount matches calculated earnings
        $calculatedAmount = $unpaidRecords->sum('caregiver_earnings');
        if (abs($calculatedAmount - $amount) > 0.01) {
            $errors[] = "Amount mismatch: Calculated $calculatedAmount, requested $amount";
        }
        $checks['amount_matches'] = abs($calculatedAmount - $amount) <= 0.01;
        
        // Check 4: No duplicate payments (check for records already being paid)
        $alreadyPaid = $unpaidRecords->whereNotNull('paid_at')->count();
        if ($alreadyPaid > 0) {
            $errors[] = "$alreadyPaid records already marked as paid";
        }
        $checks['no_duplicates'] = $alreadyPaid === 0;
        
        // Check 5: Amount is reasonable (not negative, not too large)
        if ($amount <= 0) {
            $errors[] = 'Amount must be positive';
        }
        if ($amount > 10000) { // Adjust limit as needed
            $errors[] = 'Amount exceeds safety limit ($10,000)';
        }
        $checks['amount_reasonable'] = $amount > 0 && $amount <= 10000;
        
        return [
            'passed' => empty($errors),
            'errors' => $errors,
            'checks' => $checks,
            'calculated_amount' => $calculatedAmount ?? 0,
            'sessions_count' => $unpaidRecords->count(),
        ];
    }
    
    /**
     * Post-payment verification
     */
    private function postPaymentVerification($payoutTransaction)
    {
        $checks = [];
        $results = [];
        
        // Verify all time tracking records were marked as paid
        $timeTrackingIds = $payoutTransaction->time_tracking_ids;
        $paidCount = TimeTracking::whereIn('id', $timeTrackingIds)
            ->whereNotNull('paid_at')
            ->count();
        
        $checks['all_records_marked_paid'] = $paidCount === count($timeTrackingIds);
        $results['expected_count'] = count($timeTrackingIds);
        $results['actual_paid_count'] = $paidCount;
        
        // Verify Stripe transfer ID was recorded
        $checks['stripe_id_recorded'] = !empty($payoutTransaction->stripe_transfer_id);
        $results['stripe_transfer_id'] = $payoutTransaction->stripe_transfer_id;
        
        // Verify status is completed
        $checks['status_completed'] = $payoutTransaction->status === 'completed';
        $results['status'] = $payoutTransaction->status;
        
        return [
            'passed' => !in_array(false, $checks),
            'checks' => $checks,
            'results' => $results,
        ];
    }
    
    /**
     * Create double-entry ledger records
     */
    private function createLedgerEntries($payoutTransaction, $unpaidRecords)
    {
        // Debit: Caregiver Payables (reduces what we owe)
        // Credit: Bank Account (money leaving)
        FinancialLedger::create([
            'transaction_type' => 'caregiver_payout',
            'related_id' => $payoutTransaction->id,
            'related_type' => 'App\\Models\\PayoutTransaction',
            'debit_account' => 'caregiver_payables',
            'credit_account' => 'bank_account',
            'amount' => $payoutTransaction->amount,
            'user_id' => $payoutTransaction->caregiver_id,
            'description' => "Payout to caregiver for {$unpaidRecords->count()} sessions",
            'metadata' => [
                'stripe_transfer_id' => $payoutTransaction->stripe_transfer_id,
                'sessions_count' => $unpaidRecords->count(),
                'time_tracking_ids' => $payoutTransaction->time_tracking_ids,
            ],
        ]);
    }
}
