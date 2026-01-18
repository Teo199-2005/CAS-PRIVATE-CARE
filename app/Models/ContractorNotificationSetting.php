<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractorNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'new_assignment_email',
        'shift_reminder_email',
        'schedule_change_email',
        'cancellation_email',
        'weekly_summary_email',
        'payment_email',
        'shift_reminder_hours',
    ];

    protected $casts = [
        'new_assignment_email' => 'boolean',
        'shift_reminder_email' => 'boolean',
        'schedule_change_email' => 'boolean',
        'cancellation_email' => 'boolean',
        'weekly_summary_email' => 'boolean',
        'payment_email' => 'boolean',
    ];

    /**
     * Get the user this setting belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create settings for a user
     */
    public static function getOrCreate($userId)
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'new_assignment_email' => true,
                'shift_reminder_email' => true,
                'schedule_change_email' => true,
                'cancellation_email' => true,
                'weekly_summary_email' => true,
                'payment_email' => true,
                'shift_reminder_hours' => 24,
            ]
        );
    }
}
