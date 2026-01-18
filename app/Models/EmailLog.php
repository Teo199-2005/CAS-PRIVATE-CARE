<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'email_type',
        'subject',
        'status',
        'tracking_id',
        'sent_at',
        'opened_at',
        'clicked_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Boot method to generate tracking ID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->tracking_id) {
                $model->tracking_id = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the campaign this log belongs to
     */
    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * Get the recipient user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark email as opened
     */
    public function markAsOpened()
    {
        if (!$this->opened_at) {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);

            // Update campaign stats
            if ($this->campaign) {
                $this->campaign->increment('emails_opened');
            }
        }
    }

    /**
     * Mark email as clicked
     */
    public function markAsClicked()
    {
        // Also mark as opened if not already
        if (!$this->opened_at) {
            $this->markAsOpened();
        }

        if (!$this->clicked_at) {
            $this->update([
                'status' => 'clicked',
                'clicked_at' => now(),
            ]);

            // Update campaign stats
            if ($this->campaign) {
                $this->campaign->increment('emails_clicked');
            }
        }
    }

    /**
     * Mark email as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark email as failed
     */
    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);

        // Update campaign stats
        if ($this->campaign) {
            $this->campaign->increment('emails_failed');
        }
    }
}
