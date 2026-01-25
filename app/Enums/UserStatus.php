<?php

namespace App\Enums;

/**
 * User Status Enum
 * 
 * Centralized definition of all user statuses in the system.
 */
class UserStatus
{
    public const ACTIVE = 'Active';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';
    public const SUSPENDED = 'suspended';

    /**
     * Get all valid statuses
     */
    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::PENDING,
            self::APPROVED,
            self::REJECTED,
            self::SUSPENDED,
        ];
    }

    /**
     * Check if status allows login
     */
    public static function canLogin(string $status): bool
    {
        return $status !== self::REJECTED;
    }

    /**
     * Get the display name for a status
     */
    public static function displayName(string $status): string
    {
        return match(strtolower($status)) {
            'active' => 'Active',
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'suspended' => 'Suspended',
            default => ucfirst($status),
        };
    }

    /**
     * Get badge color for status
     */
    public static function badgeColor(string $status): string
    {
        return match(strtolower($status)) {
            'active', 'approved' => 'success',
            'pending' => 'warning',
            'rejected', 'suspended' => 'error',
            default => 'secondary',
        };
    }
}
