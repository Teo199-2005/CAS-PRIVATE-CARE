<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ScheduledPayout;
use App\Models\PayoutSetting;
use App\Models\PayoutTransaction;
use App\Services\ScheduledPayoutService;
use App\Services\TaxEstimationService;
use App\Services\ComplianceService;

class PayrollController extends Controller
{
    protected $scheduledPayoutService;
    protected $taxEstimationService;
    protected $complianceService;
    
    public function __construct(
        ScheduledPayoutService $scheduledPayoutService,
        TaxEstimationService $taxEstimationService,
        ComplianceService $complianceService
    ) {
        $this->scheduledPayoutService = $scheduledPayoutService;
        $this->taxEstimationService = $taxEstimationService;
        $this->complianceService = $complianceService;
    }
    
    /**
     * Get payout schedule for current user
     */
    public function getPayoutSchedule()
    {
        $user = Auth::user();
        
        $schedule = $this->scheduledPayoutService->getPayoutSchedule($user->id);
        
        return response()->json([
            'success' => true,
            'schedule' => $schedule
        ]);
    }
    
    /**
     * Update payout preferences for current user
     */
    public function updatePayoutPreferences(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'frequency' => 'sometimes|in:weekly,biweekly,monthly',
            'payout_day' => 'sometimes|integer|min:1|max:7',
            'minimum_amount' => 'sometimes|numeric|min:0'
        ]);
        
        $result = $this->scheduledPayoutService->updatePayoutPreferences($user->id, $validated);
        
        return response()->json($result);
    }
    
    /**
     * Get payout history for current user
     */
    public function getPayoutHistory(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 20);
        
        $history = $this->scheduledPayoutService->getPayoutHistory($user->id, $limit);
        
        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }
    
    /**
     * Get tax estimate for current user
     */
    public function getTaxEstimate()
    {
        $user = Auth::user();
        
        $estimate = $this->taxEstimationService->getYTDEstimate($user->id);
        $quarterlyInfo = $this->taxEstimationService->getQuarterlyPaymentInfo();
        
        return response()->json([
            'success' => true,
            'tax_estimate' => $estimate,
            'quarterly_info' => $quarterlyInfo
        ]);
    }
    
    /**
     * Get compliance status for current user
     */
    public function getComplianceStatus()
    {
        $user = Auth::user();
        
        $compliance = $this->complianceService->getComplianceSummary($user->id);
        
        return response()->json([
            'success' => true,
            'compliance' => $compliance
        ]);
    }
    
    /**
     * Get onboarding status for current user
     */
    public function getOnboardingStatus()
    {
        $user = Auth::user();
        
        $steps = [
            'profile' => [
                'complete' => !empty($user->name) && !empty($user->email) && !empty($user->phone),
                'label' => 'Complete Profile'
            ],
            'tax_info' => [
                'complete' => $user->w9_submitted ?? false,
                'verified' => $user->w9_verified ?? false,
                'label' => 'Submit W9 Form'
            ],
            'bank_account' => [
                'complete' => !empty($user->stripe_connect_id),
                'label' => 'Connect Bank Account'
            ]
        ];
        
        // Check certification for caregivers
        if ($user->user_type === 'caregiver') {
            $caregiver = $user->caregiver;
            $steps['certification'] = [
                'complete' => $caregiver && ($caregiver->has_hha || $caregiver->has_cna || $caregiver->has_rn),
                'label' => 'Add Certifications'
            ];
            $steps['background_check'] = [
                'complete' => $caregiver && $caregiver->background_check_completed,
                'label' => 'Background Check'
            ];
        }
        
        $allComplete = collect($steps)->every(fn($step) => $step['complete']);
        
        return response()->json([
            'success' => true,
            'steps' => $steps,
            'all_complete' => $allComplete,
            'completion_percentage' => $this->calculateCompletionPercentage($steps)
        ]);
    }
    
    protected function calculateCompletionPercentage(array $steps): int
    {
        $total = count($steps);
        $complete = collect($steps)->filter(fn($step) => $step['complete'])->count();
        return (int) round(($complete / $total) * 100);
    }
    
    // ==========================================
    // ADMIN ENDPOINTS
    // ==========================================
    
    /**
     * ADMIN: Get all payout settings
     */
    public function adminGetPayoutSettings()
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $settings = PayoutSetting::getAllAsArray();
        
        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }
    
    /**
     * ADMIN: Update payout settings
     */
    public function adminUpdatePayoutSettings(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'settings' => 'required|array'
        ]);
        
        foreach ($validated['settings'] as $key => $value) {
            $existing = PayoutSetting::where('key', $key)->first();
            if ($existing) {
                PayoutSetting::setValue($key, $value, $existing->type, $existing->description);
            }
        }
        
        Log::info("Payout settings updated by admin", [
            'admin_id' => $admin->id,
            'settings' => array_keys($validated['settings'])
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Payout settings updated'
        ]);
    }
    
    /**
     * ADMIN: Get scheduled payouts
     */
    public function adminGetScheduledPayouts(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $query = ScheduledPayout::with('initiator')
            ->orderBy('scheduled_date', 'desc');
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('frequency')) {
            $query->where('frequency', $request->frequency);
        }
        
        $payouts = $query->paginate(20);
        
        return response()->json([
            'success' => true,
            'payouts' => $payouts
        ]);
    }
    
    /**
     * ADMIN: Manually trigger scheduled payout
     */
    public function adminTriggerPayout(Request $request)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'frequency' => 'required|in:weekly,biweekly,monthly'
        ]);
        
        $result = $this->scheduledPayoutService->processScheduledPayouts(
            $validated['frequency'],
            $admin->id
        );
        
        Log::info("Manual payout triggered", [
            'admin_id' => $admin->id,
            'frequency' => $validated['frequency'],
            'result' => $result
        ]);
        
        return response()->json($result);
    }
    
    /**
     * ADMIN: Get payout details
     */
    public function adminGetPayoutDetails($payoutId)
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $payout = ScheduledPayout::with('initiator')->findOrFail($payoutId);
        
        // Get associated transactions
        $transactions = PayoutTransaction::where('created_at', '>=', $payout->started_at)
            ->where('created_at', '<=', $payout->completed_at ?? now())
            ->with('caregiver.user')
            ->get();
        
        return response()->json([
            'success' => true,
            'payout' => $payout,
            'transactions' => $transactions
        ]);
    }
    
    /**
     * ADMIN: Get contractors pending payout
     */
    public function adminGetContractorsPendingPayout()
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $minAmount = PayoutSetting::getMinimumPayoutAmount();
        
        // Get all active contractors with their pending earnings
        $contractors = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'Active')
            ->get()
            ->map(function ($user) {
                $pendingEarnings = $this->getPendingEarningsForUser($user);
                
                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'payout_frequency' => $user->payout_frequency ?? 'weekly',
                    'pending_earnings' => $pendingEarnings,
                    'w9_submitted' => $user->w9_submitted ?? false,
                    'w9_verified' => $user->w9_verified ?? false,
                    'bank_connected' => !empty($user->stripe_connect_id),
                    'can_payout' => $pendingEarnings > 0 && !empty($user->stripe_connect_id)
                ];
            })
            ->filter(fn($c) => $c['pending_earnings'] > 0)
            ->sortByDesc('pending_earnings')
            ->values();
        
        return response()->json([
            'success' => true,
            'minimum_payout' => $minAmount,
            'total_pending' => $contractors->sum('pending_earnings'),
            'contractors' => $contractors
        ]);
    }
    
    protected function getPendingEarningsForUser(User $user): float
    {
        if ($user->user_type === 'caregiver') {
            $caregiver = $user->caregiver;
            if ($caregiver) {
                return (float) \App\Models\TimeTracking::where('caregiver_id', $caregiver->id)
                    ->whereNull('paid_at')
                    ->where('status', 'completed')
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'housekeeper') {
            $housekeeper = $user->housekeeper;
            if ($housekeeper) {
                return (float) \App\Models\TimeTracking::where('housekeeper_id', $housekeeper->id)
                    ->whereNull('paid_at')
                    ->where('status', 'completed')
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'marketing') {
            return (float) \App\Models\TimeTracking::where('marketing_partner_id', $user->id)
                ->whereNull('paid_at')
                ->where('status', 'completed')
                ->sum('marketing_partner_commission');
        } elseif (in_array($user->user_type, ['training', 'training_center'])) {
            return (float) \App\Models\TimeTracking::where('training_center_user_id', $user->id)
                ->whereNull('paid_at')
                ->where('status', 'completed')
                ->sum('training_center_commission');
        }
        
        return 0;
    }
    
    /**
     * ADMIN: Run compliance checks for all contractors
     */
    public function adminRunComplianceChecks()
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $results = $this->complianceService->runBatchComplianceChecks();
        
        Log::info("Batch compliance checks run by admin", [
            'admin_id' => $admin->id,
            'results' => $results
        ]);
        
        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }
    
    /**
     * ADMIN: Get compliance summary
     */
    public function adminGetComplianceSummary()
    {
        $admin = Auth::user();
        if ($admin->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Get latest compliance check for each contractor
        $contractors = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'Active')
            ->get();
        
        $compliantCount = 0;
        $nonCompliantCount = 0;
        $notCheckedCount = 0;
        $nonCompliantItems = [];
        
        foreach ($contractors as $contractor) {
            $latestCheck = \App\Models\ComplianceCheck::getLatestForUser($contractor->id);
            
            if (!$latestCheck) {
                $notCheckedCount++;
            } elseif ($latestCheck->overall_compliant) {
                $compliantCount++;
            } else {
                $nonCompliantCount++;
                $nonCompliantItems[] = [
                    'user_id' => $contractor->id,
                    'name' => $contractor->name,
                    'email' => $contractor->email,
                    'user_type' => $contractor->user_type,
                    'issues' => $latestCheck->getNonCompliantItems(),
                    'checked_at' => $latestCheck->check_date
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'summary' => [
                'total_contractors' => $contractors->count(),
                'compliant' => $compliantCount,
                'non_compliant' => $nonCompliantCount,
                'not_checked' => $notCheckedCount,
                'compliance_rate' => $contractors->count() > 0 
                    ? round(($compliantCount / $contractors->count()) * 100, 1) 
                    : 0
            ],
            'non_compliant_contractors' => $nonCompliantItems
        ]);
    }
}
