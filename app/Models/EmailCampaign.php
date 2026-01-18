<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'content',
        'type',
        'target_audience',
        'inactive_days',
        'selected_client_ids',
        'status',
        'scheduled_at',
        'sent_at',
        'created_by',
        'total_recipients',
        'emails_sent',
        'emails_opened',
        'emails_clicked',
        'emails_failed',
    ];

    protected $casts = [
        'selected_client_ids' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Campaign type labels
     */
    public static $types = [
        'promotional' => 'Promotional',
        'reengagement' => 'Re-engagement',
        'seasonal' => 'Seasonal Campaign',
        'announcement' => 'Announcement',
        'reminder' => 'Reminder',
        'custom' => 'Custom',
    ];

    /**
     * Target audience labels
     */
    public static $targetAudiences = [
        'all_clients' => 'All Clients',
        'never_booked' => 'Never Booked',
        'inactive' => 'Inactive Clients',
        'active' => 'Active Clients',
        'selected' => 'Selected Clients',
    ];

    /**
     * Get the user who created the campaign
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get email logs for this campaign
     */
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'campaign_id');
    }

    /**
     * Get target recipients based on audience settings
     */
    public function getTargetRecipients()
    {
        $query = User::where('user_type', 'client');

        switch ($this->target_audience) {
            case 'never_booked':
                // Clients who have never made a booking
                $query->whereDoesntHave('bookings');
                break;

            case 'inactive':
                // Clients who haven't booked in X days
                $days = $this->inactive_days ?? 30;
                $query->where(function ($q) use ($days) {
                    $q->whereDoesntHave('bookings')
                      ->orWhereHas('bookings', function ($bq) use ($days) {
                          $bq->where('created_at', '<', now()->subDays($days));
                      });
                });
                break;

            case 'active':
                // Clients with recent bookings
                $query->whereHas('bookings', function ($bq) {
                    $bq->where('created_at', '>=', now()->subDays(90));
                });
                break;

            case 'selected':
                // Manually selected clients
                if (!empty($this->selected_client_ids)) {
                    $query->whereIn('id', $this->selected_client_ids);
                } else {
                    $query->whereRaw('1 = 0'); // Return empty
                }
                break;

            case 'all_clients':
            default:
                // All clients - no additional filter
                break;
        }

        return $query->whereNotNull('email')->get();
    }

    /**
     * Calculate open rate
     */
    public function getOpenRateAttribute()
    {
        if ($this->emails_sent === 0) return 0;
        return round(($this->emails_opened / $this->emails_sent) * 100, 1);
    }

    /**
     * Calculate click rate
     */
    public function getClickRateAttribute()
    {
        if ($this->emails_sent === 0) return 0;
        return round(($this->emails_clicked / $this->emails_sent) * 100, 1);
    }
}
