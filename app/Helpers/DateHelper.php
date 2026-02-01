<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Date/Time Helper
 * 
 * Provides common date and time formatting utilities.
 * 
 * @package App\Helpers
 */
class DateHelper
{
    /**
     * Format date for display
     *
     * @param mixed $date
     * @param string $format
     * @return string
     */
    public static function format($date, string $format = 'M j, Y'): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Format date and time for display
     *
     * @param mixed $datetime
     * @param string $format
     * @return string
     */
    public static function formatDateTime($datetime, string $format = 'M j, Y g:i A'): string
    {
        return self::format($datetime, $format);
    }

    /**
     * Format time for display
     *
     * @param mixed $time
     * @param string $format
     * @return string
     */
    public static function formatTime($time, string $format = 'g:i A'): string
    {
        return self::format($time, $format);
    }

    /**
     * Get human-readable relative time (e.g., "2 hours ago")
     *
     * @param mixed $date
     * @return string
     */
    public static function diffForHumans($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Carbon::parse($date)->diffForHumans();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get human-readable relative time with more detail
     *
     * @param mixed $date
     * @return string
     */
    public static function diffForHumansDetailed($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            $carbon = Carbon::parse($date);
            return $carbon->diffForHumans([
                'parts' => 2,
                'join' => true,
            ]);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Format a date range
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param string $format
     * @return string
     */
    public static function formatRange($startDate, $endDate, string $format = 'M j, Y'): string
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($start->isSameDay($end)) {
            return $start->format($format);
        }

        if ($start->isSameMonth($end)) {
            return $start->format('M j') . ' - ' . $end->format('j, Y');
        }

        if ($start->isSameYear($end)) {
            return $start->format('M j') . ' - ' . $end->format('M j, Y');
        }

        return $start->format($format) . ' - ' . $end->format($format);
    }

    /**
     * Format duration in hours and minutes
     *
     * @param int $minutes Total minutes
     * @return string
     */
    public static function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} min";
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($mins === 0) {
            return "{$hours} hr";
        }

        return "{$hours} hr {$mins} min";
    }

    /**
     * Format duration in hours (decimal)
     *
     * @param int $minutes
     * @return string
     */
    public static function formatHours(int $minutes): string
    {
        $hours = round($minutes / 60, 1);
        return number_format($hours, 1) . ' hours';
    }

    /**
     * Get day of week name
     *
     * @param mixed $date
     * @return string
     */
    public static function dayOfWeek($date): string
    {
        return self::format($date, 'l'); // Full name (Monday)
    }

    /**
     * Check if date is today
     *
     * @param mixed $date
     * @return bool
     */
    public static function isToday($date): bool
    {
        try {
            return Carbon::parse($date)->isToday();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if date is in the past
     *
     * @param mixed $date
     * @return bool
     */
    public static function isPast($date): bool
    {
        try {
            return Carbon::parse($date)->isPast();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if date is in the future
     *
     * @param mixed $date
     * @return bool
     */
    public static function isFuture($date): bool
    {
        try {
            return Carbon::parse($date)->isFuture();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get age from birthdate
     *
     * @param mixed $birthdate
     * @return int|null
     */
    public static function age($birthdate): ?int
    {
        if (empty($birthdate)) {
            return null;
        }

        try {
            return Carbon::parse($birthdate)->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get business days between two dates
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @return int
     */
    public static function businessDaysBetween($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = 0;

        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $days++;
            }
            $start->addDay();
        }

        return $days;
    }

    /**
     * Get start and end of week for a date
     *
     * @param mixed $date
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    public static function weekBounds($date = null): array
    {
        $carbon = $date ? Carbon::parse($date) : Carbon::now();

        return [
            'start' => $carbon->copy()->startOfWeek(),
            'end' => $carbon->copy()->endOfWeek(),
        ];
    }

    /**
     * Get start and end of month for a date
     *
     * @param mixed $date
     * @return array ['start' => Carbon, 'end' => Carbon]
     */
    public static function monthBounds($date = null): array
    {
        $carbon = $date ? Carbon::parse($date) : Carbon::now();

        return [
            'start' => $carbon->copy()->startOfMonth(),
            'end' => $carbon->copy()->endOfMonth(),
        ];
    }

    /**
     * Create Carbon instance from date parts
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return Carbon
     */
    public static function createDate(int $year, int $month, int $day): Carbon
    {
        return Carbon::createFromDate($year, $month, $day);
    }

    /**
     * Get timezone-aware now
     *
     * @param string|null $timezone
     * @return Carbon
     */
    public static function now(?string $timezone = null): Carbon
    {
        return $timezone ? Carbon::now($timezone) : Carbon::now();
    }

    /**
     * Format for ISO 8601 (API responses)
     *
     * @param mixed $date
     * @return string
     */
    public static function toIso8601($date): string
    {
        try {
            return Carbon::parse($date)->toIso8601String();
        } catch (\Exception $e) {
            return '';
        }
    }
}
