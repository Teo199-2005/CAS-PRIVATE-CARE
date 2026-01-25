<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Audit Log Service
 * 
 * Comprehensive audit logging for admin actions and sensitive operations.
 * Logs are stored in the database for compliance and security review.
 * 
 * @package App\Services
 */
class AuditLogService
{
    /**
     * Action types for categorization
     */
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_VIEW = 'view';
    public const ACTION_EXPORT = 'export';
    public const ACTION_APPROVE = 'approve';
    public const ACTION_REJECT = 'reject';
    public const ACTION_ASSIGN = 'assign';
    public const ACTION_UNASSIGN = 'unassign';
    public const ACTION_PAYMENT = 'payment';
    public const ACTION_REFUND = 'refund';
    public const ACTION_SETTINGS = 'settings';

    /**
     * Entity types for categorization
     */
    public const ENTITY_USER = 'user';
    public const ENTITY_BOOKING = 'booking';
    public const ENTITY_PAYMENT = 'payment';
    public const ENTITY_CAREGIVER = 'caregiver';
    public const ENTITY_HOUSEKEEPER = 'housekeeper';
    public const ENTITY_CLIENT = 'client';
    public const ENTITY_ASSIGNMENT = 'assignment';
    public const ENTITY_SETTINGS = 'settings';
    public const ENTITY_SESSION = 'session';

    /**
     * Log an admin action
     *
     * @param int|null $userId The user performing the action (null for system)
     * @param string $action The action type (use class constants)
     * @param string $entityType The type of entity affected
     * @param int|string|null $entityId The ID of the affected entity
     * @param array $details Additional context/details
     * @param string|null $ipAddress IP address of the request
     * @return bool
     */
    public static function log(
        ?int $userId,
        string $action,
        string $entityType,
        int|string|null $entityId = null,
        array $details = [],
        ?string $ipAddress = null
    ): bool {
        try {
            DB::table('audit_logs')->insert([
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'details' => json_encode($details),
                'ip_address' => $ipAddress ?? request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            // Also log to file for backup
            Log::channel('audit')->info("Audit: {$action} on {$entityType}", [
                'user_id' => $userId,
                'entity_id' => $entityId,
                'details' => $details,
                'ip' => $ipAddress ?? request()->ip(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to write audit log', [
                'error' => $e->getMessage(),
                'action' => $action,
                'entity_type' => $entityType,
            ]);
            return false;
        }
    }

    /**
     * Log user creation
     */
    public static function logUserCreated(User $user, ?int $createdBy = null, array $context = []): bool
    {
        return self::log(
            $createdBy,
            self::ACTION_CREATE,
            self::ENTITY_USER,
            $user->id,
            array_merge([
                'email' => $user->email,
                'user_type' => $user->user_type,
                'name' => $user->name,
            ], $context)
        );
    }

    /**
     * Log user update
     */
    public static function logUserUpdated(User $user, array $changes, ?int $updatedBy = null): bool
    {
        return self::log(
            $updatedBy,
            self::ACTION_UPDATE,
            self::ENTITY_USER,
            $user->id,
            [
                'email' => $user->email,
                'changes' => $changes,
            ]
        );
    }

    /**
     * Log user status change (approve/reject)
     */
    public static function logUserStatusChange(User $user, string $newStatus, ?int $changedBy = null, ?string $reason = null): bool
    {
        $action = $newStatus === 'Active' || $newStatus === 'approved' 
            ? self::ACTION_APPROVE 
            : self::ACTION_REJECT;

        return self::log(
            $changedBy,
            $action,
            self::ENTITY_USER,
            $user->id,
            [
                'email' => $user->email,
                'user_type' => $user->user_type,
                'new_status' => $newStatus,
                'reason' => $reason,
            ]
        );
    }

    /**
     * Log login attempt
     */
    public static function logLogin(User $user, bool $success, ?string $failureReason = null): bool
    {
        return self::log(
            $success ? $user->id : null,
            self::ACTION_LOGIN,
            self::ENTITY_SESSION,
            $user->id,
            [
                'email' => $user->email,
                'success' => $success,
                'failure_reason' => $failureReason,
                'user_type' => $user->user_type,
            ]
        );
    }

    /**
     * Log logout
     */
    public static function logLogout(User $user): bool
    {
        return self::log(
            $user->id,
            self::ACTION_LOGOUT,
            self::ENTITY_SESSION,
            $user->id,
            ['email' => $user->email]
        );
    }

    /**
     * Log booking assignment
     */
    public static function logBookingAssignment(int $bookingId, int $caregiverId, ?int $assignedBy = null): bool
    {
        return self::log(
            $assignedBy,
            self::ACTION_ASSIGN,
            self::ENTITY_BOOKING,
            $bookingId,
            ['caregiver_id' => $caregiverId]
        );
    }

    /**
     * Log booking unassignment
     */
    public static function logBookingUnassignment(int $bookingId, int $caregiverId, ?int $unassignedBy = null): bool
    {
        return self::log(
            $unassignedBy,
            self::ACTION_UNASSIGN,
            self::ENTITY_BOOKING,
            $bookingId,
            ['caregiver_id' => $caregiverId]
        );
    }

    /**
     * Log payment action
     */
    public static function logPayment(int $bookingId, float $amount, string $status, ?int $userId = null, array $context = []): bool
    {
        return self::log(
            $userId,
            self::ACTION_PAYMENT,
            self::ENTITY_PAYMENT,
            $bookingId,
            array_merge([
                'amount' => $amount,
                'status' => $status,
            ], $context)
        );
    }

    /**
     * Log refund action
     */
    public static function logRefund(int $bookingId, float $amount, ?int $processedBy = null, ?string $reason = null): bool
    {
        return self::log(
            $processedBy,
            self::ACTION_REFUND,
            self::ENTITY_PAYMENT,
            $bookingId,
            [
                'amount' => $amount,
                'reason' => $reason,
            ]
        );
    }

    /**
     * Log settings change
     */
    public static function logSettingsChange(string $settingKey, mixed $oldValue, mixed $newValue, ?int $changedBy = null): bool
    {
        return self::log(
            $changedBy,
            self::ACTION_SETTINGS,
            self::ENTITY_SETTINGS,
            null,
            [
                'setting' => $settingKey,
                'old_value' => $oldValue,
                'new_value' => $newValue,
            ]
        );
    }

    /**
     * Log data export
     */
    public static function logDataExport(string $exportType, ?int $exportedBy = null, array $filters = []): bool
    {
        return self::log(
            $exportedBy,
            self::ACTION_EXPORT,
            $exportType,
            null,
            ['filters' => $filters]
        );
    }

    /**
     * Get audit logs with filtering
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Support\Collection
     */
    public static function getLogs(array $filters = [], int $limit = 50, int $offset = 0)
    {
        $query = DB::table('audit_logs')
            ->orderBy('created_at', 'desc');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['entity_type'])) {
            $query->where('entity_type', $filters['entity_type']);
        }

        if (!empty($filters['entity_id'])) {
            $query->where('entity_id', $filters['entity_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        return $query->limit($limit)->offset($offset)->get();
    }
}
