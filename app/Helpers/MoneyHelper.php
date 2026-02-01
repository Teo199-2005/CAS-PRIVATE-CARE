<?php

namespace App\Helpers;

/**
 * Money/Currency Helper
 * 
 * Provides utilities for formatting and calculating monetary values.
 * 
 * @package App\Helpers
 */
class MoneyHelper
{
    /**
     * Default currency
     */
    public const DEFAULT_CURRENCY = 'USD';

    /**
     * Currency symbols
     */
    protected const SYMBOLS = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'CAD' => 'CA$',
        'AUD' => 'A$',
    ];

    /**
     * Format amount as currency string
     *
     * @param float|int|string $amount Amount in dollars (or major currency unit)
     * @param string $currency Currency code (default: USD)
     * @param bool $showSymbol Whether to show currency symbol
     * @return string
     */
    public static function format($amount, string $currency = 'USD', bool $showSymbol = true): string
    {
        $numericAmount = floatval($amount);
        $symbol = self::SYMBOLS[$currency] ?? '$';

        $formatted = number_format($numericAmount, 2, '.', ',');

        if ($showSymbol) {
            return $numericAmount < 0
                ? '-' . $symbol . ltrim($formatted, '-')
                : $symbol . $formatted;
        }

        return $formatted;
    }

    /**
     * Format cents to dollars
     *
     * @param int $cents Amount in cents
     * @param string $currency Currency code
     * @return string
     */
    public static function formatCents(int $cents, string $currency = 'USD'): string
    {
        return self::format($cents / 100, $currency);
    }

    /**
     * Convert dollars to cents (for Stripe)
     *
     * @param float|int|string $dollars
     * @return int
     */
    public static function toCents($dollars): int
    {
        return (int) round(floatval($dollars) * 100);
    }

    /**
     * Convert cents to dollars
     *
     * @param int $cents
     * @return float
     */
    public static function toDollars(int $cents): float
    {
        return $cents / 100;
    }

    /**
     * Format as compact currency (e.g., $1.2K, $3.5M)
     *
     * @param float|int $amount
     * @param string $currency
     * @return string
     */
    public static function formatCompact($amount, string $currency = 'USD'): string
    {
        $symbol = self::SYMBOLS[$currency] ?? '$';
        $numericAmount = floatval($amount);
        $absAmount = abs($numericAmount);
        $sign = $numericAmount < 0 ? '-' : '';

        if ($absAmount >= 1000000000) {
            return $sign . $symbol . number_format($absAmount / 1000000000, 1) . 'B';
        }

        if ($absAmount >= 1000000) {
            return $sign . $symbol . number_format($absAmount / 1000000, 1) . 'M';
        }

        if ($absAmount >= 1000) {
            return $sign . $symbol . number_format($absAmount / 1000, 1) . 'K';
        }

        return self::format($numericAmount, $currency);
    }

    /**
     * Calculate percentage
     *
     * @param float|int $amount
     * @param float|int $percentage
     * @return float
     */
    public static function percentage($amount, $percentage): float
    {
        return floatval($amount) * (floatval($percentage) / 100);
    }

    /**
     * Add percentage to amount
     *
     * @param float|int $amount
     * @param float|int $percentage
     * @return float
     */
    public static function addPercentage($amount, $percentage): float
    {
        return floatval($amount) + self::percentage($amount, $percentage);
    }

    /**
     * Subtract percentage from amount
     *
     * @param float|int $amount
     * @param float|int $percentage
     * @return float
     */
    public static function subtractPercentage($amount, $percentage): float
    {
        return floatval($amount) - self::percentage($amount, $percentage);
    }

    /**
     * Calculate the percentage one amount is of another
     *
     * @param float|int $part
     * @param float|int $whole
     * @param int $decimals
     * @return float
     */
    public static function percentageOf($part, $whole, int $decimals = 1): float
    {
        if ($whole == 0) {
            return 0;
        }

        return round((floatval($part) / floatval($whole)) * 100, $decimals);
    }

    /**
     * Calculate hourly rate
     *
     * @param float|int $totalAmount
     * @param float|int $hours
     * @return float
     */
    public static function hourlyRate($totalAmount, $hours): float
    {
        if ($hours == 0) {
            return 0;
        }

        return round(floatval($totalAmount) / floatval($hours), 2);
    }

    /**
     * Calculate total from hourly rate and hours
     *
     * @param float|int $hourlyRate
     * @param float|int $hours
     * @return float
     */
    public static function calculateTotal($hourlyRate, $hours): float
    {
        return round(floatval($hourlyRate) * floatval($hours), 2);
    }

    /**
     * Round to nearest cent
     *
     * @param float|int $amount
     * @return float
     */
    public static function roundToCents($amount): float
    {
        return round(floatval($amount), 2);
    }

    /**
     * Parse currency string to float
     *
     * @param string $currencyString
     * @return float
     */
    public static function parse(string $currencyString): float
    {
        // Remove currency symbols and whitespace
        $cleaned = preg_replace('/[^\d.\-]/', '', $currencyString);
        return floatval($cleaned);
    }

    /**
     * Validate amount is positive
     *
     * @param mixed $amount
     * @return bool
     */
    public static function isPositive($amount): bool
    {
        return floatval($amount) > 0;
    }

    /**
     * Validate amount is non-negative
     *
     * @param mixed $amount
     * @return bool
     */
    public static function isNonNegative($amount): bool
    {
        return floatval($amount) >= 0;
    }

    /**
     * Get minimum of two amounts
     *
     * @param float|int $a
     * @param float|int $b
     * @return float
     */
    public static function min($a, $b): float
    {
        return min(floatval($a), floatval($b));
    }

    /**
     * Get maximum of two amounts
     *
     * @param float|int $a
     * @param float|int $b
     * @return float
     */
    public static function max($a, $b): float
    {
        return max(floatval($a), floatval($b));
    }

    /**
     * Sum an array of amounts
     *
     * @param array $amounts
     * @return float
     */
    public static function sum(array $amounts): float
    {
        return array_sum(array_map('floatval', $amounts));
    }

    /**
     * Average of amounts
     *
     * @param array $amounts
     * @return float
     */
    public static function average(array $amounts): float
    {
        if (empty($amounts)) {
            return 0;
        }

        return self::sum($amounts) / count($amounts);
    }
}
