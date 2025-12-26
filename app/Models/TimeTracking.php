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
        'work_date'
    ];

    protected $casts = [
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'work_date' => 'date',
        'hours_worked' => 'decimal:2'
    ];

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
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
