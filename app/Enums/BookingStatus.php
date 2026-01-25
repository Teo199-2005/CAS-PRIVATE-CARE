<?php

namespace App\Enums;

/**
 * Booking Status Enum
 * 
 * Centralized definition of all booking statuses in the system.
 */
class BookingStatus
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';
    public const UNPAID = 'unpaid';
    public const PAID = 'paid';

    /**
     * Get all valid statuses
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::IN_PROGRESS,
            self::COMPLETED,
            self::CANCELLED,
        ];
    }

    /**
     * Get active booking statuses
     */
    public static function activeStatuses(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::IN_PROGRESS,
        ];
    }

    /**
     * Get the display name for a status
     */
    public static function displayName(string $status): string
    {
        return match(strtolower($status)) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'unpaid' => 'Unpaid',
            'paid' => 'Paid',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    /**
     * Get badge color for status
     */
    public static function badgeColor(string $status): string
    {
        return match(strtolower($status)) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'in_progress' => 'primary',
            'completed', 'paid' => 'success',
            'cancelled' => 'error',
            'unpaid' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Check if a booking can be modified
     */
    public static function canModify(string $status): bool
    {
        return in_array(strtolower($status), [self::PENDING, self::CONFIRMED]);
    }

    /**
     * Check if a booking can be cancelled
     */
    public static function canCancel(string $status): bool
    {
        return in_array(strtolower($status), [self::PENDING, self::CONFIRMED]);
    }
}
