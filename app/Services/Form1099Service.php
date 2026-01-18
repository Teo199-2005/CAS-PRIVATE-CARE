<?php

namespace App\Services;

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\TimeTracking;
use App\Models\TaxForm1099;
use App\Models\TaxDocument;
use App\Models\PayoutSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Form1099Service
{
    /**
     * Generate 1099-NEC for a contractor
     */
    public function generate1099NEC($userId, $taxYear): array
    {
        $user = User::findOrFail($userId);
        
        // Calculate total compensation for the year
        $totalCompensation = $this->calculateYearlyCompensation($user, $taxYear);
        
        // Check if below threshold
        $threshold = PayoutSetting::get1099Threshold();
        if ($totalCompensation < $threshold) {
            return [
                'required' => false,
                'reason' => "Total compensation ($" . number_format($totalCompensation, 2) . ") is below the \$" . number_format($threshold, 2) . " threshold",
                'total_compensation' => $totalCompensation
            ];
        }
        
        // Get payer (company) info
        $payerInfo = $this->getPayerInfo();
        
        // Get recipient info
        $recipientInfo = $this->getRecipientInfo($user);
        
        // Check if 1099 already exists for this user/year
        $existing = TaxForm1099::where('user_id', $userId)
            ->where('tax_year', $taxYear)
            ->where('form_type', '1099-NEC')
            ->first();
        
        if ($existing) {
            // Update existing
            $existing->update([
                'box1_nonemployee_compensation' => $totalCompensation,
                'total_compensation' => $totalCompensation,
                'payer_info' => $payerInfo,
                'recipient_info' => $recipientInfo,
                'status' => 'generated',
                'generated_at' => now()
            ]);
            $taxForm = $existing;
        } else {
            // Create new
            $taxForm = TaxForm1099::create([
                'user_id' => $userId,
                'tax_year' => $taxYear,
                'form_type' => '1099-NEC',
                'box1_nonemployee_compensation' => $totalCompensation,
                'box4_federal_tax_withheld' => 0, // Typically 0 for 1099 contractors
                'box5_state_tax_withheld' => 0,
                'total_compensation' => $totalCompensation,
                'payer_info' => $payerInfo,
                'recipient_info' => $recipientInfo,
                'status' => 'generated',
                'generated_at' => now()
            ]);
        }
        
        // Generate PDF (placeholder - would use PDF library)
        $pdfPath = $this->generatePDF($taxForm);
        if ($pdfPath) {
            $taxForm->update(['pdf_path_copy_b' => $pdfPath]);
        }
        
        Log::info("Generated 1099-NEC for user {$userId}", [
            'tax_year' => $taxYear,
            'total_compensation' => $totalCompensation
        ]);
        
        return [
            'success' => true,
            'tax_form_id' => $taxForm->id,
            'user_id' => $userId,
            'user_name' => $user->name,
            'tax_year' => $taxYear,
            'total_compensation' => $totalCompensation,
            'form_type' => '1099-NEC',
            'status' => 'generated'
        ];
    }
    
    /**
     * Calculate yearly compensation for a contractor
     */
    protected function calculateYearlyCompensation(User $user, int $taxYear): float
    {
        $total = 0;
        
        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            if ($caregiver) {
                $total = TimeTracking::where('caregiver_id', $caregiver->id)
                    ->whereYear('work_date', $taxYear)
                    ->where('payment_status', 'paid')
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'housekeeper') {
            $housekeeper = Housekeeper::where('user_id', $user->id)->first();
            if ($housekeeper) {
                $total = TimeTracking::where('housekeeper_id', $housekeeper->id)
                    ->whereYear('work_date', $taxYear)
                    ->where('payment_status', 'paid')
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'marketing') {
            $total = TimeTracking::where('marketing_partner_id', $user->id)
                ->whereYear('work_date', $taxYear)
                ->where('payment_status', 'paid')
                ->sum('marketing_partner_commission');
        } elseif (in_array($user->user_type, ['training', 'training_center'])) {
            $total = TimeTracking::where('training_center_user_id', $user->id)
                ->whereYear('work_date', $taxYear)
                ->where('payment_status', 'paid')
                ->sum('training_center_commission');
        }
        
        return (float) $total;
    }
    
    /**
     * Get payer (company) information
     */
    protected function getPayerInfo(): array
    {
        $companyInfo = PayoutSetting::getCompanyInfo();
        
        return [
            'name' => $companyInfo['name'],
            'ein' => $companyInfo['ein'],
            'address' => $companyInfo['address'],
            'phone' => config('company.phone', ''),
        ];
    }
    
    /**
     * Get recipient (contractor) information
     */
    protected function getRecipientInfo(User $user): array
    {
        // Get TIN last 4 digits for display
        $tinLastFour = null;
        if (!empty($user->ssn_encrypted)) {
            // In real implementation, decrypt and get last 4
            $tinLastFour = '****'; // Placeholder
        } elseif (!empty($user->itin_encrypted)) {
            $tinLastFour = '****'; // Placeholder
        }
        
        return [
            'name' => $user->legal_name ?? $user->name,
            'address' => [
                'street' => $user->address,
                'city' => $user->city,
                'state' => $user->state,
                'zip' => $user->zip_code
            ],
            'tin_type' => $user->tax_id_type ?? 'ssn',
            'tin_last_four' => $tinLastFour,
            'email' => $user->email
        ];
    }
    
    /**
     * Generate PDF for 1099 form
     * Note: In production, use a proper PDF library like TCPDF, FPDF, or DomPDF
     */
    protected function generatePDF(TaxForm1099 $taxForm): ?string
    {
        // This is a placeholder. In production, you would:
        // 1. Use a PDF library to generate IRS-compliant 1099 form
        // 2. Fill in all the boxes with the data
        // 3. Save to storage
        // 4. Return the path
        
        try {
            $filename = "1099-NEC_{$taxForm->tax_year}_{$taxForm->user_id}.pdf";
            $path = "tax-documents/{$taxForm->tax_year}/{$filename}";
            
            // For now, just create a placeholder file
            // In production, this would be actual PDF generation
            Storage::put($path, "1099-NEC Form Placeholder - Tax Year: {$taxForm->tax_year}");
            
            Log::info("Generated 1099 PDF", ['path' => $path]);
            
            return $path;
        } catch (\Exception $e) {
            Log::error("Failed to generate 1099 PDF", ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Batch generate 1099s for all eligible contractors
     */
    public function batchGenerate1099s(int $taxYear): array
    {
        $threshold = PayoutSetting::get1099Threshold();
        
        // Get all contractor types
        $contractors = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->where('status', 'Active')
            ->get();
        
        $results = [
            'tax_year' => $taxYear,
            'threshold' => $threshold,
            'total_contractors' => $contractors->count(),
            'forms_generated' => 0,
            'below_threshold' => 0,
            'errors' => [],
            'generated' => []
        ];
        
        foreach ($contractors as $contractor) {
            try {
                $result = $this->generate1099NEC($contractor->id, $taxYear);
                
                if (isset($result['required']) && !$result['required']) {
                    $results['below_threshold']++;
                } elseif (isset($result['success']) && $result['success']) {
                    $results['forms_generated']++;
                    $results['generated'][] = [
                        'user_id' => $contractor->id,
                        'name' => $contractor->name,
                        'compensation' => $result['total_compensation']
                    ];
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'user_id' => $contractor->id,
                    'name' => $contractor->name,
                    'error' => $e->getMessage()
                ];
                Log::error("1099 generation failed for user {$contractor->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $results;
    }
    
    /**
     * Get 1099 form for a user
     */
    public function get1099ForUser($userId, $taxYear)
    {
        return TaxForm1099::where('user_id', $userId)
            ->where('tax_year', $taxYear)
            ->first();
    }
    
    /**
     * Get all 1099s for a tax year
     */
    public function getAll1099sForYear(int $taxYear)
    {
        return TaxForm1099::with('user')
            ->where('tax_year', $taxYear)
            ->orderBy('total_compensation', 'desc')
            ->get();
    }
    
    /**
     * Mark 1099 as sent to recipient
     */
    public function markAsSentToRecipient($taxFormId, $method, $email = null, $address = null): bool
    {
        $taxForm = TaxForm1099::findOrFail($taxFormId);
        $taxForm->markAsSentToRecipient($method, $email, $address);
        
        // Create tax document record
        TaxDocument::create([
            'user_id' => $taxForm->user_id,
            'document_type' => '1099_nec',
            'tax_year' => $taxForm->tax_year,
            'file_path' => $taxForm->pdf_path_copy_b,
            'form_data' => [
                'compensation' => $taxForm->total_compensation,
                'sent_via' => $method
            ],
            'status' => 'sent',
            'sent_at' => now()
        ]);
        
        return true;
    }
    
    /**
     * Get 1099 summary statistics for a tax year
     */
    public function get1099Summary(int $taxYear): array
    {
        $forms = TaxForm1099::where('tax_year', $taxYear)->get();
        
        return [
            'tax_year' => $taxYear,
            'total_forms' => $forms->count(),
            'total_compensation' => $forms->sum('total_compensation'),
            'status_breakdown' => [
                'draft' => $forms->where('status', 'draft')->count(),
                'generated' => $forms->where('status', 'generated')->count(),
                'sent_to_recipient' => $forms->where('status', 'sent_to_recipient')->count(),
                'sent_to_irs' => $forms->where('status', 'sent_to_irs')->count(),
            ],
            'deadline_info' => [
                'recipient_deadline' => 'January 31, ' . ($taxYear + 1),
                'irs_paper_deadline' => 'February 28, ' . ($taxYear + 1),
                'irs_electronic_deadline' => 'March 31, ' . ($taxYear + 1)
            ]
        ];
    }
    
    /**
     * Get all contractors who require a 1099 form (earned $600+ in tax year)
     */
    public function getContractorsRequiring1099(int $taxYear): array
    {
        $threshold = PayoutSetting::get1099Threshold();
        $startDate = Carbon::create($taxYear, 1, 1)->startOfDay();
        $endDate = Carbon::create($taxYear, 12, 31)->endOfDay();
        
        $contractors = [];
        
        // Get all contractor users
        $users = User::whereIn('user_type', ['caregiver', 'housekeeper', 'marketing', 'training_center'])
            ->get();
        
        foreach ($users as $user) {
            $totalEarnings = $this->calculateYearlyCompensation($user, $taxYear);
            
            if ($totalEarnings >= $threshold) {
                $contractors[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'total_earnings' => $totalEarnings,
                    'w9_submitted' => $user->w9_submitted ?? false,
                    'w9_verified' => $user->w9_verified ?? false,
                    'has_tin' => !empty($user->ssn_encrypted) || !empty($user->itin_encrypted) || !empty($user->ssn) || !empty($user->itin)
                ];
            }
        }
        
        // Sort by total earnings descending
        usort($contractors, fn($a, $b) => $b['total_earnings'] <=> $a['total_earnings']);
        
        return $contractors;
    }
}
