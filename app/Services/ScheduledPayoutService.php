<?php

namespace App\Services;

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\TimeTracking;
use App\Models\PayoutTransaction;
use App\Models\ScheduledPayout;
use App\Models\PayoutSetting;
use App\Models\FinancialLedger;
use App\Services\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ScheduledPayoutService
{
    protected $payoutService;
    protected $complianceService;
    
    public function __construct(PayoutService $payoutService, ComplianceService $complianceService)
    {
        $this->payoutService = $payoutService;
        $this->complianceService = $complianceService;
    }
    
    /**
     * Process scheduled payouts for a given frequency
     */
    public function processScheduledPayouts(string $frequency, ?int $initiatedBy = null): array
    {
        $today = now()->toDateString();
        
        // Check if already processed today
        $existing = ScheduledPayout::where('scheduled_date', $today)
            ->where('frequency', $frequency)
            ->whereIn('status', ['processing', 'completed', 'partial'])
            ->first();
        
        if ($existing) {
            return [
                'success' => false,
                'message' => "Payouts for {$frequency} already processed today",
                'scheduled_payout_id' => $existing->id
            ];
        }
        
        // Get contractors due for payout
        $contractors = $this->getContractorsDueForPayout($frequency);
        
        if ($contractors->isEmpty()) {
            return [
                'success' => true,
                'message' => "No contractors due for {$frequency} payout",
                'total_contractors' => 0
            ];
        }
        
        // Create scheduled payout record
        $scheduledPayout = ScheduledPayout::create([
            'scheduled_date' => $today,
            'frequency' => $frequency,
            'status' => 'pending',
            'total_contractors' => $contractors->count(),
            'initiated_by' => $initiatedBy
        ]);
        
        $scheduledPayout->startProcessing();
        
        $successfulPayouts = 0;
        $failedPayouts = 0;
        $successfulAmount = 0;
        $failedAmount = 0;
        $totalAmount = 0;
        
        foreach ($contractors as $contractor) {
            try {
                $result = $this->processContractorPayout($contractor, $scheduledPayout->id);
                
                if ($result['success']) {
                    $successfulPayouts++;
                    $successfulAmount += $result['amount'];
                    
                    // Send payout confirmation email
                    $user = User::find($contractor['user_id']);
                    if ($user && $user->email) {
                        EmailService::sendPayoutConfirmationEmail(
                            $user,
                            $result['amount'],
                            $today,
                            $contractor['period_start'] ?? null,
                            $contractor['period_end'] ?? null,
                            $contractor['hours_worked'] ?? null,
                            $result['stripe_transfer_id'] ?? null,
                            'Direct Deposit'
                        );
                    }
                } else {
                    $failedPayouts++;
                    $failedAmount += $result['amount'] ?? 0;
                    $scheduledPayout->addError($contractor['user_id'], $result['error']);
                    
                    // Send payout failed email
                    $user = User::find($contractor['user_id']);
                    if ($user && $user->email) {
                        EmailService::sendPayoutFailedEmail(
                            $user,
                            $result['amount'] ?? 0,
                            $result['error'] ?? 'Payment processing failed',
                            'Please check your bank account details in your dashboard.'
                        );
                    }
                }
                
                $totalAmount += $result['amount'] ?? 0;
                
            } catch (\Exception $e) {
                $failedPayouts++;
                $scheduledPayout->addError($contractor['user_id'], $e->getMessage());
                Log::error("Payout failed for contractor", [
                    'user_id' => $contractor['user_id'],
                    'error' => $e->getMessage()
                ]);
                
                // Send payout failed email for exception
                $user = User::find($contractor['user_id']);
                if ($user && $user->email) {
                    EmailService::sendPayoutFailedEmail(
                        $user,
                        $contractor['pending_earnings'] ?? 0,
                        'An error occurred while processing your payment.',
                        'Our team has been notified and will resolve this issue. No action is required from you.'
                    );
                }
            }
        }
        
        // Update totals
        $scheduledPayout->update(['total_amount' => $totalAmount]);
        $scheduledPayout->completeProcessing($successfulPayouts, $failedPayouts, $successfulAmount, $failedAmount);
        
        return [
            'success' => true,
            'scheduled_payout_id' => $scheduledPayout->id,
            'frequency' => $frequency,
            'total_contractors' => $contractors->count(),
            'successful_payouts' => $successfulPayouts,
            'failed_payouts' => $failedPayouts,
            'total_amount' => $totalAmount,
            'successful_amount' => $successfulAmount,
            'failed_amount' => $failedAmount,
            'status' => $scheduledPayout->status
        ];
    }
    
    /**
     * Get contractors due for payout based on frequency
     */
    protected function getContractorsDueForPayout(string $frequency)
    {
        $minAmount = PayoutSetting::getMinimumPayoutAmount();
        $requireW9 = PayoutSetting::isW9RequiredBeforePayout();
        
        // Get all contractors with the specified payout frequency
        $query = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'Active')
            ->where('payout_frequency', $frequency)
            ->whereNotNull('stripe_connect_id'); // Must have bank connected
        
        // If W9 required, filter by W9 status
        if ($requireW9) {
            $query->where('w9_submitted', true);
        }
        
        $users = $query->get();
        
        // Filter and calculate pending earnings
        $contractorsDue = collect();
        
        foreach ($users as $user) {
            $pendingAmount = $this->getPendingEarnings($user);
            
            if ($pendingAmount >= $minAmount) {
                $contractorsDue->push([
                    'user_id' => $user->id,
                    'user' => $user,
                    'pending_amount' => $pendingAmount,
                    'payout_frequency' => $frequency
                ]);
            }
        }
        
        return $contractorsDue;
    }
    
    /**
     * Get pending earnings for a contractor
     */
    protected function getPendingEarnings(User $user): float
    {
        $pending = 0;
        
        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            if ($caregiver) {
                $pending = TimeTracking::where('caregiver_id', $caregiver->id)
                    ->whereNull('paid_at')
                    ->where('status', 'completed')
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'housekeeper') {
            $housekeeper = Housekeeper::where('user_id', $user->id)->first();
            if ($housekeeper) {
                $pending = TimeTracking::where('housekeeper_id', $housekeeper->id)
                    ->whereNull('paid_at')
                    ->where('status', 'completed')
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'marketing') {
            $pending = TimeTracking::where('marketing_partner_id', $user->id)
                ->whereNull('paid_at')
                ->where('status', 'completed')
                ->sum('marketing_partner_commission');
        } elseif (in_array($user->user_type, ['training', 'training_center'])) {
            $pending = TimeTracking::where('training_center_user_id', $user->id)
                ->whereNull('paid_at')
                ->where('status', 'completed')
                ->sum('training_center_commission');
        }
        
        return (float) $pending;
    }
    
    /**
     * Process payout for a single contractor
     */
    protected function processContractorPayout(array $contractorData, int $scheduledPayoutId): array
    {
        $user = $contractorData['user'];
        $amount = $contractorData['pending_amount'];
        
        // Compliance check
        $compliance = $this->complianceService->getComplianceSummary($user->id);
        if (!$compliance['overall_compliant']) {
            return [
                'success' => false,
                'error' => 'Contractor not compliant: ' . implode(', ', $compliance['non_compliant_items']),
                'amount' => $amount
            ];
        }
        
        // Check if auto-approve or needs manual approval
        $autoApproveThreshold = PayoutSetting::getAutoApproveThreshold();
        
        if ($amount > $autoApproveThreshold) {
            // Flag for manual approval
            return [
                'success' => false,
                'error' => "Amount \${$amount} exceeds auto-approve threshold of \${$autoApproveThreshold}",
                'amount' => $amount,
                'requires_approval' => true
            ];
        }
        
        // Get contractor ID based on type
        $contractorId = null;
        $contractorType = null;
        
        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            $contractorId = $caregiver?->id;
            $contractorType = 'caregiver';
        } elseif ($user->user_type === 'housekeeper') {
            $housekeeper = Housekeeper::where('user_id', $user->id)->first();
            $contractorId = $housekeeper?->id;
            $contractorType = 'housekeeper';
        } else {
            // Marketing/Training - use user ID
            $contractorId = $user->id;
            $contractorType = $user->user_type;
        }
        
        if (!$contractorId) {
            return [
                'success' => false,
                'error' => 'Contractor profile not found',
                'amount' => $amount
            ];
        }
        
        try {
            // Process the payout using PayoutService
            if ($contractorType === 'caregiver') {
                $result = $this->payoutService->processCaregiverPayout($contractorId, $amount, null);
            } else {
                // For other types, use direct transfer
                $result = $this->processDirectTransfer($user, $amount, $contractorType, $scheduledPayoutId);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'amount' => $amount
            ];
        }
    }
    
    /**
     * Process direct transfer for non-caregiver contractors
     */
    protected function processDirectTransfer(User $user, float $amount, string $contractorType, int $scheduledPayoutId): array
    {
        if (empty($user->stripe_connect_id)) {
            return [
                'success' => false,
                'error' => 'No Stripe Connect account',
                'amount' => $amount
            ];
        }
        
        DB::beginTransaction();
        
        try {
            // Create Stripe transfer with idempotency key to prevent duplicates
            $stripe = new \Stripe\StripeClient(config('stripe.secret'));
            
            // SECURITY: Idempotency key prevents duplicate transfers on network retries
            $idempotencyKey = 'scheduled_payout_' . $scheduledPayoutId . '_' . $user->id . '_' . now()->format('Ymd');
            
            $transfer = $stripe->transfers->create([
                'amount' => intval($amount * 100), // Convert to cents
                'currency' => 'usd',
                'destination' => $user->stripe_connect_id,
                'description' => "Scheduled payout - {$contractorType}",
                'metadata' => [
                    'user_id' => $user->id,
                    'contractor_type' => $contractorType,
                    'scheduled_payout_id' => $scheduledPayoutId
                ]
            ], [
                'idempotency_key' => $idempotencyKey
            ]);
            
            // Mark time trackings as paid
            if ($contractorType === 'marketing') {
                TimeTracking::where('marketing_partner_id', $user->id)
                    ->whereNull('paid_at')
                    ->where('status', 'completed')
                    ->update([
                        'paid_at' => now(),
                        'payment_status' => 'paid'
                    ]);
            } elseif (in_array($contractorType, ['training', 'training_center'])) {
                TimeTracking::where('training_center_user_id', $user->id)
                    ->whereNull('paid_at')
                    ->where('status', 'completed')
                    ->update([
                        'paid_at' => now(),
                        'payment_status' => 'paid'
                    ]);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'amount' => $amount,
                'stripe_transfer_id' => $transfer->id
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Get payout schedule for a contractor
     */
    public function getPayoutSchedule(int $userId): array
    {
        $user = User::findOrFail($userId);
        
        $frequency = $user->payout_frequency ?? 'weekly';
        $payoutDay = $user->payout_day ?? 5; // Friday
        
        $dayNames = ['', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        // Calculate next payout date
        $nextPayoutDate = $this->calculateNextPayoutDate($frequency, $payoutDay);
        
        // Get pending earnings
        $pendingEarnings = $this->getPendingEarnings($user);
        
        // Get last payout
        $lastPayout = PayoutTransaction::where('caregiver_id', function ($query) use ($user) {
            $query->select('id')
                ->from('caregivers')
                ->where('user_id', $user->id)
                ->limit(1);
        })->orderBy('completed_at', 'desc')->first();
        
        return [
            'frequency' => $frequency,
            'frequency_display' => ucfirst($frequency),
            'payout_day' => $payoutDay,
            'payout_day_name' => $dayNames[$payoutDay] ?? 'Friday',
            'next_payout_date' => $nextPayoutDate->format('Y-m-d'),
            'next_payout_display' => $nextPayoutDate->format('l, F j, Y'),
            'days_until_next_payout' => now()->diffInDays($nextPayoutDate),
            'pending_earnings' => $pendingEarnings,
            'minimum_payout' => PayoutSetting::getMinimumPayoutAmount(),
            'meets_minimum' => $pendingEarnings >= PayoutSetting::getMinimumPayoutAmount(),
            'last_payout' => $lastPayout ? [
                'date' => $lastPayout->completed_at->format('Y-m-d'),
                'amount' => $lastPayout->amount
            ] : null
        ];
    }
    
    /**
     * Calculate next payout date
     */
    protected function calculateNextPayoutDate(string $frequency, int $payoutDay): Carbon
    {
        $now = now();
        
        switch ($frequency) {
            case 'weekly':
                $next = $now->copy()->next($payoutDay - 1); // Carbon uses 0=Sunday
                if ($next->isPast()) {
                    $next->addWeek();
                }
                break;
                
            case 'biweekly':
                $next = $now->copy()->next($payoutDay - 1);
                // If less than a week away, add another week
                if ($now->diffInDays($next) < 7) {
                    $next->addWeek();
                }
                break;
                
            case 'monthly':
                // First occurrence of payout day in next month
                $next = $now->copy()->startOfMonth()->addMonth();
                while ($next->dayOfWeek !== ($payoutDay - 1)) {
                    $next->addDay();
                }
                break;
                
            default:
                $next = $now->copy()->next(Carbon::FRIDAY);
        }
        
        return $next;
    }
    
    /**
     * Update contractor payout preferences
     */
    public function updatePayoutPreferences(int $userId, array $preferences): array
    {
        $user = User::findOrFail($userId);
        
        $updateData = [];
        
        if (isset($preferences['frequency'])) {
            if (!in_array($preferences['frequency'], ['weekly', 'biweekly', 'monthly'])) {
                return ['success' => false, 'error' => 'Invalid frequency'];
            }
            $updateData['payout_frequency'] = $preferences['frequency'];
        }
        
        if (isset($preferences['payout_day'])) {
            if ($preferences['payout_day'] < 1 || $preferences['payout_day'] > 7) {
                return ['success' => false, 'error' => 'Invalid payout day'];
            }
            $updateData['payout_day'] = $preferences['payout_day'];
        }
        
        if (isset($preferences['minimum_amount'])) {
            $globalMin = PayoutSetting::getMinimumPayoutAmount();
            if ($preferences['minimum_amount'] < $globalMin) {
                return ['success' => false, 'error' => "Minimum must be at least \${$globalMin}"];
            }
            $updateData['minimum_payout_amount'] = $preferences['minimum_amount'];
        }
        
        $user->update($updateData);
        
        return [
            'success' => true,
            'message' => 'Payout preferences updated',
            'schedule' => $this->getPayoutSchedule($userId)
        ];
    }
    
    /**
     * Get payout history for a contractor
     */
    public function getPayoutHistory(int $userId, int $limit = 20): array
    {
        $user = User::findOrFail($userId);
        
        // Get caregiver ID if applicable
        $caregiverId = null;
        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            $caregiverId = $caregiver?->id;
        }
        
        $payouts = [];
        
        if ($caregiverId) {
            $payouts = PayoutTransaction::where('caregiver_id', $caregiverId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($payout) {
                    return [
                        'id' => $payout->id,
                        'date' => $payout->completed_at?->format('Y-m-d'),
                        'amount' => $payout->amount,
                        'status' => $payout->status,
                        'sessions_count' => $payout->sessions_count,
                        'total_hours' => $payout->total_hours,
                        'stripe_transfer_id' => $payout->stripe_transfer_id
                    ];
                });
        }
        
        return [
            'user_id' => $userId,
            'payouts' => $payouts,
            'total_count' => $payouts instanceof \Illuminate\Support\Collection ? $payouts->count() : count($payouts)
        ];
    }
}
