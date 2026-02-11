<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TimeTracking extends Model
{
    use HasFactory;
    
    protected $table = 'time_trackings'; // Explicitly set table name
    
    protected $fillable = [
        'caregiver_id',
        'housekeeper_id',
        'provider_type',
        'client_id',
        'clock_in_time',
        'clock_out_time',
        'hours_worked',
        'location',
        'status',
        'work_date',
        'caregiver_earnings',
        'marketing_partner_id',
        'marketing_partner_commission',
        'marketing_commission_paid_at',
        'marketing_commission_stripe_transfer_id',
        'marketing_paid',
        'training_center_user_id',
        'training_center_commission',
        'training_commission_paid_at',
        'training_commission_stripe_transfer_id',
        'training_paid',
        'agency_commission',
        'total_client_charge',
        'paid_at',
        'payment_status',
        'booking_id',
        'stripe_charge_id',
        'client_charged_at',
        'stripe_transfer_id',
        'actual_minutes_worked',
        'scheduled_minutes',
        'late_minutes',
        'is_late',
        'minutes_difference'
    ];

    protected $casts = [
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'work_date' => 'date',
        'hours_worked' => 'decimal:2',
        'caregiver_earnings' => 'decimal:2',
        'marketing_partner_commission' => 'decimal:2',
        'training_center_commission' => 'decimal:2',
        'agency_commission' => 'decimal:2',
        'total_client_charge' => 'decimal:2',
        'paid_at' => 'datetime',
        'client_charged_at' => 'datetime',
        'marketing_commission_paid_at' => 'datetime',
        'training_commission_paid_at' => 'datetime',
        'is_late' => 'boolean',
        'marketing_paid' => 'boolean',
        'training_paid' => 'boolean'
    ];

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    /**
     * Get the housekeeper for this time tracking.
     */
    public function housekeeper()
    {
        return $this->belongsTo(Housekeeper::class);
    }

    /**
     * Get the provider (caregiver or housekeeper).
     */
    public function provider()
    {
        if ($this->provider_type === 'housekeeper' && $this->housekeeper_id) {
            return $this->housekeeper;
        }
        return $this->caregiver;
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function marketingPartner()
    {
        return $this->belongsTo(User::class, 'marketing_partner_id');
    }

    public function trainingCenter()
    {
        return $this->belongsTo(User::class, 'training_center_user_id');
    }

    public function calculateHours()
    {
        if ($this->clock_in_time && $this->clock_out_time) {
            $clockIn = Carbon::parse($this->clock_in_time);
            $clockOut = Carbon::parse($this->clock_out_time);
            $this->hours_worked = $clockOut->diffInHours($clockIn, true);
            $this->save();
        }
    }

    public function getCurrentDuration()
    {
        if ($this->clock_in_time && !$this->clock_out_time) {
            $clockIn = Carbon::parse($this->clock_in_time);
            return $clockIn->diffInHours(Carbon::now(), true);
        }
        return $this->hours_worked ?? 0;
    }
}
