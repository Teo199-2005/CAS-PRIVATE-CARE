<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeTracking extends Model
{
    protected $fillable = [
        'caregiver_id',
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
        'training_center_user_id',
        'training_center_commission',
        'agency_commission',
        'total_client_charge',
        'paid_at',
        'payment_status',
        'booking_id'
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
        'paid_at' => 'datetime'
    ];

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
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
