<?php

namespace App\Services;

use App\Models\Caregiver;
use App\Models\Housekeeper;

class PricingService
{
    /**
     * Pricing breakdown constants - CAREGIVERS
     *
     * Client pays $45/hr (without referral) or $43.50/hr (with referral = $1.50/hr discount).
     * Agency hourly rate is the remainder after caregiver + typical marketing (Silver $1/hr) for display.
     * Live session splits use tiered marketing in TimeTrackingController; agency is always remainder.
     */
    const CAREGIVER_RATE = 28.00;

    /** @deprecated Training center commissions removed; kept 0 for any legacy reads */
    const TRAINING_CENTER_RATE = 0.00;

    /** Typical marketing rate for static breakdown display (Silver tier); actual payouts use MarketingTierService */
    const MARKETING_RATE = 1.00;

    const CLIENT_RATE_NO_REFERRAL = 45.00;
    const CLIENT_RATE_WITH_REFERRAL = 43.50;
    const REFERRAL_DISCOUNT = 1.50;

    /**
     * Pricing breakdown constants - HOUSEKEEPERS (legacy / decommissioned flows)
     */
    const HOUSEKEEPER_DEFAULT_RATE = 20.00;
    const HOUSEKEEPER_CLIENT_RATE_NO_REFERRAL = 45.00;
    const HOUSEKEEPER_CLIENT_RATE_WITH_REFERRAL = 43.50;
    const HOUSEKEEPER_REFERRAL_DISCOUNT = 1.50;
    const HOUSEKEEPER_MARKETING_RATE = 1.00;

    /**
     * Agency rate for caregiver display: client − caregiver − typical marketing (no training slice).
     */
    public static function getCaregiverAgencyRateForDisplay(bool $hasReferral): float
    {
        $client = $hasReferral ? self::CLIENT_RATE_WITH_REFERRAL : self::CLIENT_RATE_NO_REFERRAL;
        $marketing = $hasReferral ? self::MARKETING_RATE : 0;

        return round($client - self::CAREGIVER_RATE - $marketing, 2);
    }

    /**
     * @param bool $hasTrainingCenter Ignored; training center payouts removed
     */
    public static function calculateBreakdown(float $hours, bool $hasReferral = false, bool $hasTrainingCenter = false): array
    {
        $clientRate = $hasReferral ? self::CLIENT_RATE_WITH_REFERRAL : self::CLIENT_RATE_NO_REFERRAL;
        $clientTotal = $hours * $clientRate;

        $caregiverTotal = $hours * self::CAREGIVER_RATE;

        $marketingRate = $hasReferral ? self::MARKETING_RATE : 0;
        $marketingTotal = $hours * $marketingRate;

        $agencyRate = self::getCaregiverAgencyRateForDisplay($hasReferral);
        $agencyTotal = $hours * $agencyRate;

        return [
            'hours' => $hours,
            'has_referral' => $hasReferral,
            'has_training_center' => false,
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
                    'rate' => $marketingRate,
                    'total' => round($marketingTotal, 2),
                ],
                'training_center' => [
                    'rate' => 0,
                    'total' => 0,
                ],
            ],
            'verification_total' => round($caregiverTotal + $agencyTotal + $marketingTotal, 2),
        ];
    }

    public static function getClientRate(bool $hasReferral = false): float
    {
        return $hasReferral ? self::CLIENT_RATE_WITH_REFERRAL : self::CLIENT_RATE_NO_REFERRAL;
    }

    public static function getCaregiverRate(): float
    {
        return self::CAREGIVER_RATE;
    }

    /**
     * @param bool $hasTrainingCenter Ignored
     */
    public static function getAgencyRate(bool $hasReferral = false, bool $hasTrainingCenter = false): float
    {
        return self::getCaregiverAgencyRateForDisplay($hasReferral);
    }

    public static function getTrainingCenterRate(bool $hasTrainingCenter = false): float
    {
        return 0;
    }

    public static function getMarketingRate(bool $hasReferral = false): float
    {
        return $hasReferral ? self::MARKETING_RATE : 0;
    }

    public static function caregiverHasTrainingCenter(int $caregiverId): bool
    {
        return false;
    }

    /**
     * @param bool $hasTrainingCenter Ignored
     */
    public static function getPricingSummary(bool $hasReferral = false, bool $hasTrainingCenter = false): array
    {
        $clientRate = self::getClientRate($hasReferral);
        $agencyRate = self::getCaregiverAgencyRateForDisplay($hasReferral);

        return [
            'client_rate' => $clientRate,
            'breakdown' => [
                'Caregiver' => '$' . number_format(self::CAREGIVER_RATE, 2) . '/hr',
                'Agency' => '$' . number_format($agencyRate, 2) . '/hr',
                'Marketing' => $hasReferral ? ('$' . number_format(self::MARKETING_RATE, 2) . '/hr (Silver tier; may vary)') : 'N/A',
            ],
            'total' => '$' . number_format($clientRate, 2) . '/hr',
        ];
    }

    public static function calculateHousekeeperBreakdown(float $hours, float $assignedRate, bool $hasReferral = false): array
    {
        $clientRate = $hasReferral ? self::HOUSEKEEPER_CLIENT_RATE_WITH_REFERRAL : self::HOUSEKEEPER_CLIENT_RATE_NO_REFERRAL;
        $clientTotal = $hours * $clientRate;

        $housekeeperTotal = $hours * $assignedRate;

        $marketingTotal = $hasReferral ? ($hours * self::HOUSEKEEPER_MARKETING_RATE) : 0;

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
            'verification_total' => round($housekeeperTotal + $agencyTotal + $marketingTotal, 2),
        ];
    }

    public static function getHousekeeperClientRate(bool $hasReferral = false): float
    {
        return $hasReferral ? self::HOUSEKEEPER_CLIENT_RATE_WITH_REFERRAL : self::HOUSEKEEPER_CLIENT_RATE_NO_REFERRAL;
    }

    public static function getHousekeeperDefaultRate(): float
    {
        return self::HOUSEKEEPER_DEFAULT_RATE;
    }

    public static function getHousekeeperAgencyRate(float $assignedRate, bool $hasReferral = false): float
    {
        $clientRate = self::getHousekeeperClientRate($hasReferral);
        $marketingRate = $hasReferral ? self::HOUSEKEEPER_MARKETING_RATE : 0;

        return $clientRate - $assignedRate - $marketingRate;
    }

    public static function getHousekeeperReferralDiscount(): float
    {
        return self::HOUSEKEEPER_REFERRAL_DISCOUNT;
    }

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
