<?php

namespace App\Enums;

/**
 * User Types Enum
 * 
 * Centralized definition of all user types in the system.
 * Use these constants instead of magic strings throughout the application.
 */
class UserType
{
    public const CLIENT = 'client';
    public const CAREGIVER = 'caregiver';
    public const HOUSEKEEPER = 'housekeeper';
    public const ADMIN = 'admin';
    public const MARKETING = 'marketing';
    public const TRAINING_CENTER = 'training_center';
    public const TRAINING = 'training';

    /**
     * Get all partner/contractor types
     */
    public static function partnerTypes(): array
    {
        return [
            self::CAREGIVER,
            self::HOUSEKEEPER,
            self::MARKETING,
            self::TRAINING_CENTER,
        ];
    }

    /**
     * Get all valid user types
     */
    public static function all(): array
    {
        return [
            self::CLIENT,
            self::CAREGIVER,
            self::HOUSEKEEPER,
            self::ADMIN,
            self::MARKETING,
            self::TRAINING_CENTER,
            self::TRAINING,
        ];
    }

    /**
     * Check if a type is a partner/contractor type
     */
    public static function isPartner(string $type): bool
    {
        return in_array($type, self::partnerTypes());
    }

    /**
     * Get the display name for a user type
     */
    public static function displayName(string $type): string
    {
        return match($type) {
            self::CLIENT => 'Client',
            self::CAREGIVER => 'Caregiver',
            self::HOUSEKEEPER => 'Housekeeper',
            self::ADMIN => 'Administrator',
            self::MARKETING => 'Marketing Partner',
            self::TRAINING_CENTER, self::TRAINING => 'Training Center',
            default => ucfirst($type),
        };
    }

    /**
     * Get the dashboard route for a user type
     */
    public static function dashboardRoute(string $type, ?string $role = null): string
    {
        if ($type === self::ADMIN && $role === 'Admin Staff') {
            return '/admin-staff/dashboard-vue';
        }

        return match($type) {
            self::ADMIN => '/admin/dashboard-vue',
            self::CAREGIVER => '/caregiver/dashboard-vue',
            self::HOUSEKEEPER => '/housekeeper/dashboard-vue',
            self::MARKETING => '/marketing/dashboard-vue',
            self::TRAINING_CENTER, self::TRAINING => '/training/dashboard-vue',
            self::CLIENT => '/client/dashboard-vue',
            default => '/client/dashboard-vue',
        };
    }
}
