<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'caregiver_id',
        'housekeeper_id',
        'provider_type',
        'assigned_at',
        'start_time',
        'end_time',
        'status',
        'notes',
        'assignment_order',
        'is_active',
        'start_date',
        'end_date',
        'expected_days',
        'assigned_hourly_rate'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'assigned_hourly_rate' => 'decimal:2'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    /**
     * Get the housekeeper assigned to this booking.
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

    /**
     * Boot method to handle model events
     * 
     * Note: updateAssignmentStatus() is disabled as assignment_status 
     * column does not exist in current database schema
     */
    protected static function boot()
    {
        parent::boot();
        
        // Update booking assignment status when assignment is created, updated, or deleted
        // Temporarily disabled - assignment_status column not in production schema
        /*
        static::created(function ($assignment) {
            $assignment->booking->updateAssignmentStatus();
        });
        
        static::updated(function ($assignment) {
            $assignment->booking->updateAssignmentStatus();
        });
        
        static::deleted(function ($assignment) {
            $assignment->booking->updateAssignmentStatus();
        });
        */
    }
}