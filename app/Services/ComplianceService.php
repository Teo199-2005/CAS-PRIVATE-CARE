<?php

namespace App\Services;

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\TimeTracking;
use App\Models\ComplianceCheck;
use App\Models\TaxDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ComplianceService
{
    /**
     * Run a full compliance check for a contractor
     */
    public function runComplianceCheck($userId, $checkType = 'automated', $checkedBy = null): array
    {
        $user = User::findOrFail($userId);
        
        $checks = [];
        $results = [];
        
        // Check 1: W9 Compliance
        $w9Check = $this->checkW9Compliance($user);
        $checks['w9'] = 'W9 form submission and verification';
        $results['w9'] = $w9Check;
        
        // Check 2: Bank Account Compliance
        $bankCheck = $this->checkBankCompliance($user);
        $checks['bank'] = 'Bank account connection status';
        $results['bank'] = $bankCheck;
        
        // Check 3: Background Check Compliance
        $bgCheck = $this->checkBackgroundCompliance($user);
        $checks['background'] = 'Background check completion';
        $results['background'] = $bgCheck;
        
        // Check 4: Certification Compliance
        $certCheck = $this->checkCertificationCompliance($user);
        $checks['certification'] = 'Required certifications verification';
        $results['certification'] = $certCheck;
        
        // Check 5: Work Pattern Compliance (1099 vs W2 check)
        $workCheck = $this->checkWorkPatternCompliance($user);
        $checks['work_pattern'] = 'Independent contractor work pattern analysis';
        $results['work_pattern'] = $workCheck;
        
        // Calculate overall compliance
        $overallCompliant = $w9Check['passed'] && $bankCheck['passed'] && 
                           $bgCheck['passed'] && $certCheck['passed'] && 
                           $workCheck['passed'];
        
        // Save compliance check
        $complianceCheck = ComplianceCheck::create([
            'user_id' => $userId,
            'check_date' => now()->toDateString(),
            'checks_performed' => $checks,
            'check_results' => $results,
            'overall_compliant' => $overallCompliant,
            'w9_compliant' => $w9Check['passed'],
            'bank_compliant' => $bankCheck['passed'],
            'background_check_compliant' => $bgCheck['passed'],
            'certification_compliant' => $certCheck['passed'],
            'work_pattern_compliant' => $workCheck['passed'],
            'average_weekly_hours' => $workCheck['average_weekly_hours'] ?? null,
            'unique_clients_count' => $workCheck['unique_clients'] ?? null,
            'check_type' => $checkType,
            'checked_by' => $checkedBy
        ]);
        
        return [
            'compliance_check_id' => $complianceCheck->id,
            'user_id' => $userId,
            'user_name' => $user->name,
            'checks' => $checks,
            'results' => $results,
            'overall_compliant' => $overallCompliant,
            'compliance_percentage' => $complianceCheck->compliance_percentage,
            'checked_at' => now()->toISOString()
        ];
    }
    
    /**
     * Check W9 compliance
     */
    protected function checkW9Compliance(User $user): array
    {
        $w9Submitted = $user->w9_submitted ?? false;
        $w9Verified = $user->w9_verified ?? false;
        
        // Check if there's a verified W9 document
        $verifiedW9 = TaxDocument::where('user_id', $user->id)
            ->where('document_type', 'w9')
            ->where('status', 'verified')
            ->exists();
        
        $passed = $w9Submitted && ($w9Verified || $verifiedW9);
        
        return [
            'passed' => $passed,
            'message' => $passed ? 'W9 form submitted and verified' : 'W9 form required',
            'details' => [
                'submitted' => $w9Submitted,
                'verified' => $w9Verified || $verifiedW9,
                'has_tax_id' => !empty($user->ssn_encrypted) || !empty($user->itin_encrypted)
            ]
        ];
    }
    
    /**
     * Check bank account compliance
     */
    protected function checkBankCompliance(User $user): array
    {
        $hasStripeConnect = !empty($user->stripe_connect_id);
        
        return [
            'passed' => $hasStripeConnect,
            'message' => $hasStripeConnect ? 'Bank account connected via Stripe' : 'Bank account not connected',
            'details' => [
                'stripe_connect_id' => $hasStripeConnect ? 'Connected' : null
            ]
        ];
    }
    
    /**
     * Check background check compliance
     */
    protected function checkBackgroundCompliance(User $user): array
    {
        $bgCompleted = false;
        
        // Check based on user type
        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            $bgCompleted = $caregiver?->background_check_completed ?? false;
        } elseif ($user->user_type === 'housekeeper') {
            $housekeeper = Housekeeper::where('user_id', $user->id)->first();
            $bgCompleted = $housekeeper?->background_check_completed ?? false;
        } else {
            // Marketing/Training centers may not require background check
            $bgCompleted = true;
        }
        
        return [
            'passed' => $bgCompleted,
            'message' => $bgCompleted ? 'Background check completed' : 'Background check pending',
            'details' => [
                'completed' => $bgCompleted
            ]
        ];
    }
    
    /**
     * Check certification compliance
     */
    protected function checkCertificationCompliance(User $user): array
    {
        // For caregivers, check if they have at least one certification
        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            
            if (!$caregiver) {
                return [
                    'passed' => false,
                    'message' => 'Caregiver profile not found',
                    'details' => []
                ];
            }
            
            $hasHHA = $caregiver->has_hha ?? false;
            $hasCNA = $caregiver->has_cna ?? false;
            $hasRN = $caregiver->has_rn ?? false;
            
            $passed = $hasHHA || $hasCNA || $hasRN;
            
            return [
                'passed' => $passed,
                'message' => $passed ? 'Has valid certifications' : 'No certifications on file',
                'details' => [
                    'has_hha' => $hasHHA,
                    'has_cna' => $hasCNA,
                    'has_rn' => $hasRN
                ]
            ];
        }
        
        // Other user types don't require specific certifications
        return [
            'passed' => true,
            'message' => 'Certifications not required for this role',
            'details' => []
        ];
    }
    
    /**
     * Check work pattern compliance (1099 classification test)
     */
    protected function checkWorkPatternCompliance(User $user): array
    {
        // Get contractor profile
        $providerId = null;
        $providerType = null;
        
        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            $providerId = $caregiver?->id;
            $providerType = 'caregiver_id';
        } elseif ($user->user_type === 'housekeeper') {
            $housekeeper = Housekeeper::where('user_id', $user->id)->first();
            $providerId = $housekeeper?->id;
            $providerType = 'housekeeper_id';
        }
        
        if (!$providerId) {
            return [
                'passed' => true,
                'message' => 'Work pattern analysis not applicable',
                'details' => []
            ];
        }
        
        // Calculate average weekly hours over last 12 weeks
        $startDate = now()->subWeeks(12);
        $timeTrackings = TimeTracking::where($providerType, $providerId)
            ->where('work_date', '>=', $startDate)
            ->whereNotNull('hours_worked')
            ->get();
        
        $totalHours = $timeTrackings->sum('hours_worked');
        $weeks = 12;
        $avgWeeklyHours = $totalHours / $weeks;
        
        // Count unique clients
        $uniqueClients = $timeTrackings->pluck('client_id')->unique()->count();
        
        // 1099 indicators:
        // - Average less than 35 hours/week (not full-time)
        // - Multiple clients (not economically dependent)
        $hoursOk = $avgWeeklyHours < 35;
        $clientsOk = $uniqueClients > 1 || $avgWeeklyHours < 20; // Low hours OR multiple clients
        
        $passed = $hoursOk && $clientsOk;
        
        $message = $passed 
            ? 'Work pattern consistent with independent contractor status'
            : 'Work pattern may indicate employee relationship - review recommended';
        
        return [
            'passed' => $passed,
            'message' => $message,
            'average_weekly_hours' => round($avgWeeklyHours, 1),
            'unique_clients' => $uniqueClients,
            'details' => [
                'total_hours_12_weeks' => round($totalHours, 1),
                'avg_weekly_hours' => round($avgWeeklyHours, 1),
                'unique_clients' => $uniqueClients,
                'hours_threshold_passed' => $hoursOk,
                'clients_threshold_passed' => $clientsOk
            ]
        ];
    }
    
    /**
     * Get compliance summary for a user
     */
    public function getComplianceSummary($userId): array
    {
        $latestCheck = ComplianceCheck::getLatestForUser($userId);
        
        if (!$latestCheck) {
            // Run a new check if none exists
            return $this->runComplianceCheck($userId);
        }
        
        // If check is older than 7 days, run a new one
        if ($latestCheck->check_date->lt(now()->subDays(7))) {
            return $this->runComplianceCheck($userId);
        }
        
        return [
            'compliance_check_id' => $latestCheck->id,
            'user_id' => $userId,
            'overall_compliant' => $latestCheck->overall_compliant,
            'compliance_percentage' => $latestCheck->compliance_percentage,
            'non_compliant_items' => $latestCheck->getNonCompliantItems(),
            'checked_at' => $latestCheck->check_date->toISOString(),
            'results' => $latestCheck->check_results
        ];
    }
    
    /**
     * Batch run compliance checks for all active contractors
     */
    public function runBatchComplianceChecks(): array
    {
        $contractors = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'Active')
            ->get();
        
        $results = [
            'total' => $contractors->count(),
            'compliant' => 0,
            'non_compliant' => 0,
            'errors' => []
        ];
        
        foreach ($contractors as $contractor) {
            try {
                $check = $this->runComplianceCheck($contractor->id);
                if ($check['overall_compliant']) {
                    $results['compliant']++;
                } else {
                    $results['non_compliant']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'user_id' => $contractor->id,
                    'error' => $e->getMessage()
                ];
                Log::error("Compliance check failed for user {$contractor->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }
}
