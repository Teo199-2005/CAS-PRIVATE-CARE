<?php

namespace App;

/**
 * User types that are fully decommissioned (no login, no new registration).
 */
final class DecommissionedUserTypes
{
    public const TYPES = ['housekeeper', 'training_center', 'training'];

    public static function isDecommissioned(?string $userType): bool
    {
        if ($userType === null || $userType === '') {
            return false;
        }

        return in_array(strtolower($userType), array_map('strtolower', self::TYPES), true);
    }
}
