<?php

namespace App\Services;

/**
 * Payment Fee Service
 * 
 * Centralized service for calculating Stripe processing fees.
 * This eliminates code duplication across controllers.
 * 
 * Fee Structure (as of 2026):
 * - Domestic (US cards): 2.9% + $0.30
 * - International cards: 4.9% + $0.30
 * 
 * @package App\Services
 */
class PaymentFeeService
{
    /**
     * Stripe fee rate for domestic (US) cards
     */
    private const DOMESTIC_RATE = 0.029;

    /**
     * Stripe fee rate for international cards
     */
    private const INTERNATIONAL_RATE = 0.049;

    /**
     * Fixed fee per transaction (in dollars)
     */
    private const FIXED_FEE = 0.30;

    /**
     * Calculate the processing fee to pass through to customer
     * so that the business receives the exact target amount after Stripe fees.
     * 
     * Formula: gross = (target + fixed) / (1 - rate)
     *          fee = gross - target
     *
     * @param float $targetAmount The net amount the business should receive
     * @param string $cardCountry ISO 2-letter country code (default: US)
     * @return float The processing fee (rounded to 2 decimal places)
     */
    public static function calculateProcessingFee(float $targetAmount, string $cardCountry = 'US'): float
    {
        $rate = self::getRate($cardCountry);
        $gross = ($targetAmount + self::FIXED_FEE) / (1 - $rate);
        $fee = $gross - $targetAmount;

        return round($fee, 2);
    }

    /**
     * Calculate the total amount to charge (target + processing fee)
     *
     * @param float $targetAmount The net amount the business should receive
     * @param string $cardCountry ISO 2-letter country code (default: US)
     * @return float The total amount to charge (rounded to 2 decimal places)
     */
    public static function calculateAdjustedTotal(float $targetAmount, string $cardCountry = 'US'): float
    {
        return round($targetAmount + self::calculateProcessingFee($targetAmount, $cardCountry), 2);
    }

    /**
     * Convert amount to cents for Stripe API
     *
     * @param float $amount Amount in dollars
     * @return int Amount in cents
     */
    public static function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Convert cents to dollars
     *
     * @param int $cents Amount in cents
     * @return float Amount in dollars
     */
    public static function toDollars(int $cents): float
    {
        return round($cents / 100, 2);
    }

    /**
     * Get the fee rate based on card country
     *
     * @param string $cardCountry ISO 2-letter country code
     * @return float Fee rate (percentage as decimal)
     */
    public static function getRate(string $cardCountry): float
    {
        return strtoupper($cardCountry) === 'US' 
            ? self::DOMESTIC_RATE 
            : self::INTERNATIONAL_RATE;
    }

    /**
     * Get fee breakdown for display purposes
     *
     * @param float $targetAmount The net amount
     * @param string $cardCountry ISO 2-letter country code
     * @return array{target: float, fee: float, total: float, rate: float, fixed: float}
     */
    public static function getFeeBreakdown(float $targetAmount, string $cardCountry = 'US'): array
    {
        $fee = self::calculateProcessingFee($targetAmount, $cardCountry);
        
        return [
            'target' => $targetAmount,
            'fee' => $fee,
            'total' => $targetAmount + $fee,
            'rate' => self::getRate($cardCountry) * 100, // As percentage
            'fixed' => self::FIXED_FEE,
        ];
    }
}
