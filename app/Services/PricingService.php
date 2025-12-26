<?php

namespace App\Services;

use App\Models\Caregiver;
use App\Models\ReferralCode;

class PricingService
{
    /**
     * Pricing breakdown constants
     * 
     * Client pays $45/hr (without referral) or $40/hr (with referral)
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
    const CLIENT_RATE_WITH_REFERRAL = 40.00; // Client pays $40/hr with referral
    const REFERRAL_DISCOUNT = 5.00;          // $5/hr discount with referral
    
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
}
