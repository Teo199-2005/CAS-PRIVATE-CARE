<?php

namespace App\Services;

use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\TimeTracking;
use Illuminate\Support\Facades\Log;

class TaxEstimationService
{
    /**
     * 2024-2025 Tax Constants
     * Note: These are ESTIMATES for educational purposes only
     */
    
    // Self-Employment Tax Rate (Social Security 12.4% + Medicare 2.9%)
    private const SELF_EMPLOYMENT_TAX_RATE = 0.153;
    
    // Self-employment tax deduction (half of SE tax is deductible)
    private const SE_TAX_DEDUCTION_RATE = 0.5;
    
    // Federal Tax Brackets (2024 - Single Filer)
    private const FEDERAL_TAX_BRACKETS = [
        ['min' => 0, 'max' => 11600, 'rate' => 0.10],
        ['min' => 11600, 'max' => 47150, 'rate' => 0.12],
        ['min' => 47150, 'max' => 100525, 'rate' => 0.22],
        ['min' => 100525, 'max' => 191950, 'rate' => 0.24],
        ['min' => 191950, 'max' => 243725, 'rate' => 0.32],
        ['min' => 243725, 'max' => 609350, 'rate' => 0.35],
        ['min' => 609350, 'max' => PHP_INT_MAX, 'rate' => 0.37],
    ];
    
    // Standard deduction for single filer (2024)
    private const STANDARD_DEDUCTION_SINGLE = 14600;
    
    // NY State Tax Rates (simplified average)
    private const NY_STATE_TAX_BRACKETS = [
        ['min' => 0, 'max' => 8500, 'rate' => 0.04],
        ['min' => 8500, 'max' => 11700, 'rate' => 0.045],
        ['min' => 11700, 'max' => 13900, 'rate' => 0.0525],
        ['min' => 13900, 'max' => 80650, 'rate' => 0.0585],
        ['min' => 80650, 'max' => 215400, 'rate' => 0.0625],
        ['min' => 215400, 'max' => 1077550, 'rate' => 0.0685],
        ['min' => 1077550, 'max' => PHP_INT_MAX, 'rate' => 0.0882],
    ];
    
    // NYC Local Tax (for NYC residents)
    private const NYC_TAX_RATE = 0.03876; // Approximate average rate
    
    /**
     * Estimate taxes for a contractor
     */
    public function estimateTaxes(float $grossEarnings, string $state = 'NY', bool $isNYCResident = true): array
    {
        if ($grossEarnings <= 0) {
            return $this->getEmptyEstimate();
        }
        
        // Step 1: Calculate Self-Employment Tax
        $selfEmploymentTax = $this->calculateSelfEmploymentTax($grossEarnings);
        
        // Step 2: Calculate SE Tax Deduction (half of SE tax)
        $seTaxDeduction = $selfEmploymentTax * self::SE_TAX_DEDUCTION_RATE;
        
        // Step 3: Calculate Adjusted Gross Income
        $adjustedGrossIncome = $grossEarnings - $seTaxDeduction;
        
        // Step 4: Calculate Taxable Income (after standard deduction)
        $taxableIncome = max(0, $adjustedGrossIncome - self::STANDARD_DEDUCTION_SINGLE);
        
        // Step 5: Calculate Federal Income Tax
        $federalTax = $this->calculateBracketedTax($taxableIncome, self::FEDERAL_TAX_BRACKETS);
        
        // Step 6: Calculate State Tax
        $stateTax = 0;
        if (strtoupper($state) === 'NY') {
            $stateTax = $this->calculateBracketedTax($taxableIncome, self::NY_STATE_TAX_BRACKETS);
            
            // Add NYC local tax if applicable
            if ($isNYCResident) {
                $stateTax += $taxableIncome * self::NYC_TAX_RATE;
            }
        }
        
        // Step 7: Calculate Total Estimated Tax
        $totalTax = $selfEmploymentTax + $federalTax + $stateTax;
        
        // Step 8: Calculate quarterly estimate
        $quarterlyEstimate = $totalTax / 4;
        
        // Step 9: Calculate effective tax rate
        $effectiveRate = $grossEarnings > 0 ? ($totalTax / $grossEarnings) * 100 : 0;
        
        return [
            'gross_earnings' => round($grossEarnings, 2),
            'adjusted_gross_income' => round($adjustedGrossIncome, 2),
            'taxable_income' => round($taxableIncome, 2),
            
            'self_employment_tax' => round($selfEmploymentTax, 2),
            'se_tax_deduction' => round($seTaxDeduction, 2),
            'federal_tax_estimate' => round($federalTax, 2),
            'state_tax_estimate' => round($stateTax, 2),
            
            'total_tax_estimate' => round($totalTax, 2),
            'net_after_taxes' => round($grossEarnings - $totalTax, 2),
            'effective_tax_rate' => round($effectiveRate, 1),
            
            'quarterly_payment' => round($quarterlyEstimate, 2),
            
            'tax_breakdown' => [
                'self_employment' => [
                    'social_security' => round($grossEarnings * 0.124, 2),
                    'medicare' => round($grossEarnings * 0.029, 2),
                    'total' => round($selfEmploymentTax, 2)
                ],
                'federal' => round($federalTax, 2),
                'state' => round($stateTax, 2)
            ],
            
            'deductions_used' => [
                'standard_deduction' => self::STANDARD_DEDUCTION_SINGLE,
                'se_tax_deduction' => round($seTaxDeduction, 2),
                'total_deductions' => round(self::STANDARD_DEDUCTION_SINGLE + $seTaxDeduction, 2)
            ],
            
            'disclaimer' => 'This is an ESTIMATE only for educational purposes. Actual tax liability may vary based on personal circumstances, deductions, credits, and filing status. Please consult a qualified tax professional for accurate tax advice.',
            
            'assumptions' => [
                'filing_status' => 'Single',
                'state' => $state,
                'nyc_resident' => $isNYCResident,
                'tax_year' => date('Y'),
                'no_other_income' => true
            ]
        ];
    }
    
    /**
     * Calculate self-employment tax
     */
    protected function calculateSelfEmploymentTax(float $earnings): float
    {
        // SE tax is calculated on 92.35% of net earnings
        $netEarnings = $earnings * 0.9235;
        
        // Social Security cap for 2024 is $168,600
        $ssCap = 168600;
        $ssEarnings = min($netEarnings, $ssCap);
        
        // Social Security (12.4%) + Medicare (2.9%)
        $ssTax = $ssEarnings * 0.124;
        $medicareTax = $netEarnings * 0.029;
        
        // Additional Medicare tax (0.9%) on earnings over $200,000
        $additionalMedicare = 0;
        if ($netEarnings > 200000) {
            $additionalMedicare = ($netEarnings - 200000) * 0.009;
        }
        
        return $ssTax + $medicareTax + $additionalMedicare;
    }
    
    /**
     * Calculate tax using brackets
     */
    protected function calculateBracketedTax(float $taxableIncome, array $brackets): float
    {
        $tax = 0;
        $remainingIncome = $taxableIncome;
        
        foreach ($brackets as $bracket) {
            if ($remainingIncome <= 0) break;
            
            $bracketSize = $bracket['max'] - $bracket['min'];
            $taxableInBracket = min($remainingIncome, $bracketSize);
            
            $tax += $taxableInBracket * $bracket['rate'];
            $remainingIncome -= $taxableInBracket;
        }
        
        return $tax;
    }
    
    /**
     * Get empty estimate for $0 earnings
     */
    protected function getEmptyEstimate(): array
    {
        return [
            'gross_earnings' => 0,
            'adjusted_gross_income' => 0,
            'taxable_income' => 0,
            'self_employment_tax' => 0,
            'se_tax_deduction' => 0,
            'federal_tax_estimate' => 0,
            'state_tax_estimate' => 0,
            'total_tax_estimate' => 0,
            'net_after_taxes' => 0,
            'effective_tax_rate' => 0,
            'quarterly_payment' => 0,
            'disclaimer' => 'No earnings to estimate taxes on.'
        ];
    }
    
    /**
     * Get year-to-date tax estimate for a contractor
     */
    public function getYTDEstimate($userId): array
    {
        $user = User::findOrFail($userId);
        
        // Get YTD earnings based on user type
        $ytdEarnings = 0;
        
        if ($user->user_type === 'caregiver') {
            $caregiver = Caregiver::where('user_id', $user->id)->first();
            if ($caregiver) {
                $ytdEarnings = TimeTracking::where('caregiver_id', $caregiver->id)
                    ->whereYear('work_date', now()->year)
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'housekeeper') {
            $housekeeper = Housekeeper::where('user_id', $user->id)->first();
            if ($housekeeper) {
                $ytdEarnings = TimeTracking::where('housekeeper_id', $housekeeper->id)
                    ->whereYear('work_date', now()->year)
                    ->sum('caregiver_earnings');
            }
        } elseif ($user->user_type === 'marketing') {
            // Marketing partner commissions
            $ytdEarnings = TimeTracking::where('marketing_partner_id', $user->id)
                ->whereYear('work_date', now()->year)
                ->sum('marketing_partner_commission');
        } elseif (in_array($user->user_type, ['training', 'training_center'])) {
            // Training center commissions
            $ytdEarnings = TimeTracking::where('training_center_user_id', $user->id)
                ->whereYear('work_date', now()->year)
                ->sum('training_center_commission');
        }
        
        $estimate = $this->estimateTaxes($ytdEarnings);
        
        // Add YTD context
        $estimate['period'] = [
            'type' => 'year_to_date',
            'year' => now()->year,
            'as_of' => now()->format('M d, Y'),
            'days_remaining' => now()->endOfYear()->diffInDays(now())
        ];
        
        return $estimate;
    }
    
    /**
     * Get quarterly tax payment reminder
     */
    public function getQuarterlyPaymentInfo(): array
    {
        $now = now();
        $year = $now->year;
        
        // IRS quarterly payment due dates
        $quarterlyDates = [
            'Q1' => ['period' => 'Jan 1 - Mar 31', 'due' => "$year-04-15", 'label' => 'April 15'],
            'Q2' => ['period' => 'Apr 1 - May 31', 'due' => "$year-06-15", 'label' => 'June 15'],
            'Q3' => ['period' => 'Jun 1 - Aug 31', 'due' => "$year-09-15", 'label' => 'September 15'],
            'Q4' => ['period' => 'Sep 1 - Dec 31', 'due' => ($year + 1) . "-01-15", 'label' => 'January 15']
        ];
        
        // Find next due date
        $nextDue = null;
        $currentQuarter = null;
        
        foreach ($quarterlyDates as $quarter => $info) {
            $dueDate = \Carbon\Carbon::parse($info['due']);
            if ($dueDate->gte($now)) {
                $nextDue = $info;
                $nextDue['quarter'] = $quarter;
                $nextDue['days_until'] = $now->diffInDays($dueDate);
                $currentQuarter = $quarter;
                break;
            }
        }
        
        return [
            'quarterly_dates' => $quarterlyDates,
            'next_payment' => $nextDue,
            'current_quarter' => $currentQuarter,
            'note' => 'As a 1099 contractor, you may need to make quarterly estimated tax payments to avoid penalties.'
        ];
    }
}
