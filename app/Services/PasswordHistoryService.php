<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * ========================================================================
 * Password History Service
 * ========================================================================
 * 
 * Enforces password history to prevent users from reusing recent passwords.
 * This is a security best practice for compliance and account protection.
 * 
 * Configuration:
 * - HISTORY_COUNT: Number of previous passwords to check (default: 5)
 * 
 * @version 1.0.0
 * @since 2026-01-25
 */
class PasswordHistoryService
{
    /**
     * Number of previous passwords to store and check against
     */
    protected const HISTORY_COUNT = 5;

    /**
     * Table name for password history
     */
    protected const TABLE = 'password_histories';

    /**
     * Check if a password was used previously
     *
     * @param User $user
     * @param string $newPassword Plain text password
     * @return bool True if password was used before
     */
    public function wasUsedBefore(User $user, string $newPassword): bool
    {
        $histories = DB::table(self::TABLE)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(self::HISTORY_COUNT)
            ->pluck('password_hash');

        foreach ($histories as $oldHash) {
            if (Hash::check($newPassword, $oldHash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record a new password in history
     *
     * @param User $user
     * @param string $hashedPassword The hashed password to store
     * @return void
     */
    public function recordPassword(User $user, string $hashedPassword): void
    {
        // Insert new password hash
        DB::table(self::TABLE)->insert([
            'user_id' => $user->id,
            'password_hash' => $hashedPassword,
            'created_at' => now(),
        ]);

        // Clean up old entries beyond history count
        $this->pruneHistory($user);
    }

    /**
     * Record password from plain text (hashes it first)
     *
     * @param User $user
     * @param string $plainPassword
     * @return void
     */
    public function recordPlainPassword(User $user, string $plainPassword): void
    {
        $this->recordPassword($user, Hash::make($plainPassword));
    }

    /**
     * Remove old password history entries beyond the limit
     *
     * @param User $user
     * @return void
     */
    protected function pruneHistory(User $user): void
    {
        $historyIds = DB::table(self::TABLE)
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->skip(self::HISTORY_COUNT)
            ->limit(100)
            ->pluck('id');

        if ($historyIds->isNotEmpty()) {
            DB::table(self::TABLE)->whereIn('id', $historyIds)->delete();
        }
    }

    /**
     * Get count of password history for a user
     *
     * @param User $user
     * @return int
     */
    public function getHistoryCount(User $user): int
    {
        return DB::table(self::TABLE)
            ->where('user_id', $user->id)
            ->count();
    }

    /**
     * Clear all password history for a user (admin use only)
     *
     * @param User $user
     * @return int Number of records deleted
     */
    public function clearHistory(User $user): int
    {
        return DB::table(self::TABLE)
            ->where('user_id', $user->id)
            ->delete();
    }

    /**
     * Get validation error message
     *
     * @return string
     */
    public static function getValidationMessage(): string
    {
        return 'You cannot reuse any of your last ' . self::HISTORY_COUNT . ' passwords.';
    }

    /**
     * Get the history count configuration
     *
     * @return int
     */
    public static function getHistoryLimit(): int
    {
        return self::HISTORY_COUNT;
    }
}
