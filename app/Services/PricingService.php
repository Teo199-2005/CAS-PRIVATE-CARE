<?php

namespace App\Services;

use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\ReferralCode;

class PricingService
{
    /**
     * Pricing breakdown constants - CAREGIVERS
     * 
     * Client pays $45/hr (without referral) or $42/hr (with referral)
     */
    const CAREGIVER_RATE = 28.00;           // Caregiver always gets $28/hr
    const TRAINING_CENTER_RATE = 0.50;       // Training center gets $0.50/hr (if applicable)
    const MARKETING_RATE = 1.00;             // Marketing gets $1/hr (if referral code used)
    
    // Agency rates depend on referral and training center
    const AGENCY_RATE_NO_REFERRAL = 16.50;           // $16.50/hr (no referral, has training)
    const AGENCY_RATE_NO_REFERRAL_NO_TRAINING = 17.00; // $17.00/hr (no referral, no training - gets training's $0.50)
    const AGENCY_RATE_WITH_REFERRAL = 10.50;          // $10.50/hr (with referral, has training)
    const AGENCY_RATE_WITH_REFERRAL_NO_TRAINING = 11.00; // $11.00/hr (with referral, no training - gets training's $0.50)
    
    const CLIENT_RATE_NO_REFERRAL = 45.00;   // Client pays $45/hr without referral
    const CLIENT_RATE_WITH_REFERRAL = 42.00; // Client pays $42/hr with referral
    const REFERRAL_DISCOUNT = 3.00;          // $3/hr discount with referral
    
    /**
     * Pricing breakdown constants - HOUSEKEEPERS
     * 
     * Client pays SAME as caregivers: $45/hr (without referral) or $42/hr (with referral)
     * Admin assigns the housekeeper's hourly rate when making assignment
     * Agency profit = Client Rate - Assigned Rate - Marketing (if referral)
     * Simpler structure: no training centers for housekeepers
     */
    const HOUSEKEEPER_DEFAULT_RATE = 20.00;            // Default housekeeper rate (admin can override)
    const HOUSEKEEPER_CLIENT_RATE_NO_REFERRAL = 45.00; // Client pays $45/hr (same as caregivers)
    const HOUSEKEEPER_CLIENT_RATE_WITH_REFERRAL = 42.00; // Client pays $42/hr (same as caregivers)
    const HOUSEKEEPER_REFERRAL_DISCOUNT = 3.00;        // $3/hr discount (same as caregivers)
    const HOUSEKEEPER_MARKETING_RATE = 1.00;           // Marketing gets $1/hr (if referral code used)
    
    /**
     * Calculate pricing breakdown for a booking/session
     * 
     * @param float $hours Number of hours worked
     * @param bool $hasReferral Whether a referral code was used
     * @param bool $hasTrainingCenter Whether the caregiver has a training center
     * @return array Breakdown of payments
     */
    public static function calculateBreakdown(float $hours, bool $hasReferral = false, bool $hasTrainingCenter = false): array
    {
        $clientRate = $hasReferral ? self::CLIENT_RATE_WITH_REFERRAL : self::CLIENT_RATE_NO_REFERRAL;
        $clientTotal = $hours * $clientRate;
        
        $caregiverTotal = $hours * self::CAREGIVER_RATE;
        
        // Training center gets $0.50/hr only if caregiver has a training center
        $trainingCenterTotal = $hasTrainingCenter ? ($hours * self::TRAINING_CENTER_RATE) : 0;
        
        // Marketing gets $1/hr only if referral code was used
        $marketingTotal = $hasReferral ? ($hours * self::MARKETING_RATE) : 0;
        
        // Agency rate depends on referral and training center
        if ($hasReferral) {
            $agencyRate = $hasTrainingCenter ? self::AGENCY_RATE_WITH_REFERRAL : self::AGENCY_RATE_WITH_REFERRAL_NO_TRAINING;
        } else {
            $agencyRate = $hasTrainingCenter ? self::AGENCY_RATE_NO_REFERRAL : self::AGENCY_RATE_NO_REFERRAL_NO_TRAINING;
        }
        $agencyTotal = $hours * $agencyRate;
        
        return [
            'hours' => $hours,
            'has_referral' => $hasReferral,
            'has_training_center' => $hasTrainingCenter,
            'client_rate' => $clientRate,
            'client_total' => round($clientTotal, 2),
            'breakdown' => [
                'caregiver' => [
                    'rate' => self::CAREGIVER_RATE,
                    'total' => round($caregiverTotal, 2),
                ],
                'agency' => [
                    'rate' => $agencyRate,
                    'total' => round($agencyTotal, 2),
                ],
                'marketing' => [
                    'rate' => $hasReferral ? self::MARKETING_RATE : 0,
                    'total' => round($marketingTotal, 2),
                ],
                'training_center' => [
                    'rate' => $hasTrainingCenter ? self::TRAINING_CENTER_RATE : 0,
                    'total' => round($trainingCenterTotal, 2),
                ],
            ],
            // Verification: all parts should equal client total
            'verification_total' => round($caregiverTotal + $agencyTotal + $marketingTotal + $trainingCenterTotal, 2),
        ];
    }
    
    /**
     * Get the client hourly rate
     */
    public static function getClientRate(bool $hasReferral = false): float
    {
        return $hasReferral ? self::CLIENT_RATE_WITH_REFERRAL : self::CLIENT_RATE_NO_REFERRAL;
    }
    
    /**
     * Get caregiver hourly rate (always fixed)
     */
    public static function getCaregiverRate(): float
    {
        return self::CAREGIVER_RATE;
    }
    
    /**
     * Get agency hourly rate
     */
    public static function getAgencyRate(bool $hasReferral = false, bool $hasTrainingCenter = false): float
    {
        if ($hasReferral) {
            return $hasTrainingCenter ? self::AGENCY_RATE_WITH_REFERRAL : self::AGENCY_RATE_WITH_REFERRAL_NO_TRAINING;
        }
        return $hasTrainingCenter ? self::AGENCY_RATE_NO_REFERRAL : self::AGENCY_RATE_NO_REFERRAL_NO_TRAINING;
    }
    
    /**
     * Get training center rate (0 if caregiver doesn't have one)
     */
    public static function getTrainingCenterRate(bool $hasTrainingCenter = false): float
    {
        return $hasTrainingCenter ? self::TRAINING_CENTER_RATE : 0;
    }
    
    /**
     * Get marketing rate (0 if no referral)
     */
    public static function getMarketingRate(bool $hasReferral = false): float
    {
        return $hasReferral ? self::MARKETING_RATE : 0;
    }
    
    /**
     * Check if caregiver has a training center
     */
    public static function caregiverHasTrainingCenter(int $caregiverId): bool
    {
        $caregiver = Caregiver::find($caregiverId);
        return $caregiver ? (bool) $caregiver->has_training_center : false;
    }
    
    /**
     * Get pricing summary for display
     */
    public static function getPricingSummary(bool $hasReferral = false, bool $hasTrainingCenter = false): array
    {
        $clientRate = self::getClientRate($hasReferral);
        
        return [
            'client_rate' => $clientRate,
            'breakdown' => [
                'Caregiver' => '$' . number_format(self::CAREGIVER_RATE, 2) . '/hr',
                'Agency' => '$' . number_format(self::getAgencyRate($hasReferral, $hasTrainingCenter), 2) . '/hr',
                'Marketing' => $hasReferral ? ('$' . number_format(self::MARKETING_RATE, 2) . '/hr') : 'N/A',
                'Training Center' => $hasTrainingCenter ? ('$' . number_format(self::TRAINING_CENTER_RATE, 2) . '/hr') : 'Included in Agency',
            ],
            'total' => '$' . number_format($clientRate, 2) . '/hr',
        ];
    }
    
    // ============================================
    // HOUSEKEEPER PRICING METHODS
    // ============================================
    
    /**
     * Calculate pricing breakdown for a housekeeping booking/session
     * Agency profit = Client Rate - Assigned Rate - Marketing (if referral)
     * 
     * @param float $hours Number of hours worked
     * @param float $assignedRate The hourly rate assigned by admin (what housekeeper earns)
     * @param bool $hasReferral Whether a referral code was used
     * @return array Breakdown of payments
     */
    public static function calculateHousekeeperBreakdown(float $hours, float $assignedRate, bool $hasReferral = false): array
    {
        $clientRate = $hasReferral ? self::HOUSEKEEPER_CLIENT_RATE_WITH_REFERRAL : self::HOUSEKEEPER_CLIENT_RATE_NO_REFERRAL;
        $clientTotal = $hours * $clientRate;
        
        $housekeeperTotal = $hours * $assignedRate;
        
        // Marketing gets $1/hr only if referral code was used
        $marketingTotal = $hasReferral ? ($hours * self::HOUSEKEEPER_MARKETING_RATE) : 0;
        
        // Agency gets remainder: Client Rate - Assigned Rate - Marketing
        $agencyRate = $clientRate - $assignedRate - ($hasReferral ? self::HOUSEKEEPER_MARKETING_RATE : 0);
        $agencyTotal = $hours * $agencyRate;
        
        return [
            'hours' => $hours,
            'has_referral' => $hasReferral,
            'client_rate' => $clientRate,
            'client_total' => round($clientTotal, 2),
            'breakdown' => [
                'housekeeper' => [
                    'rate' => $assignedRate,
                    'total' => round($housekeeperTotal, 2),
                ],
                'agency' => [
                    'rate' => round($agencyRate, 2),
                    'total' => round($agencyTotal, 2),
                ],
                'marketing' => [
                    'rate' => $hasReferral ? self::HOUSEKEEPER_MARKETING_RATE : 0,
                    'total' => round($marketingTotal, 2),
                ],
            ],
            // Verification: all parts should equal client total
            'verification_total' => round($housekeeperTotal + $agencyTotal + $marketingTotal, 2),
        ];
    }
    
    /**
     * Get the housekeeper client hourly rate (SAME as caregivers: $45 or $42 with referral)
     */
    public static function getHousekeeperClientRate(bool $hasReferral = false): float
    {
        return $hasReferral ? self::HOUSEKEEPER_CLIENT_RATE_WITH_REFERRAL : self::HOUSEKEEPER_CLIENT_RATE_NO_REFERRAL;
    }
    
    /**
     * Get default housekeeper hourly rate (admin can assign different rate)
     */
    public static function getHousekeeperDefaultRate(): float
    {
        return self::HOUSEKEEPER_DEFAULT_RATE;
    }
    
    /**
     * Calculate agency rate based on assigned housekeeper rate
     * Agency gets: Client Rate - Assigned Rate - Marketing (if referral)
     */
    public static function getHousekeeperAgencyRate(float $assignedRate, bool $hasReferral = false): float
    {
        $clientRate = self::getHousekeeperClientRate($hasReferral);
        $marketingRate = $hasReferral ? self::HOUSEKEEPER_MARKETING_RATE : 0;
        return $clientRate - $assignedRate - $marketingRate;
    }
    
    /**
     * Get housekeeper referral discount amount (same as caregivers)
     */
    public static function getHousekeeperReferralDiscount(): float
    {
        return self::HOUSEKEEPER_REFERRAL_DISCOUNT;
    }
    
    /**
     * Get housekeeper pricing summary for display
     */
    public static function getHousekeeperPricingSummary(float $assignedRate = null, bool $hasReferral = false): array
    {
        $rate = $assignedRate ?? self::HOUSEKEEPER_DEFAULT_RATE;
        $clientRate = self::getHousekeeperClientRate($hasReferral);
        $agencyRate = self::getHousekeeperAgencyRate($rate, $hasReferral);
        
        return [
            'client_rate' => $clientRate,
            'breakdown' => [
                'Housekeeper' => '$' . number_format($rate, 2) . '/hr (admin assigned)',
                'Agency' => '$' . number_format($agencyRate, 2) . '/hr',
                'Marketing' => $hasReferral ? ('$' . number_format(self::HOUSEKEEPER_MARKETING_RATE, 2) . '/hr') : 'N/A',
            ],
            'total' => '$' . number_format($clientRate, 2) . '/hr',
            'discount' => $hasReferral ? ('$' . number_format(self::HOUSEKEEPER_REFERRAL_DISCOUNT, 2) . '/hr') : 'None',
        ];
    }
}
