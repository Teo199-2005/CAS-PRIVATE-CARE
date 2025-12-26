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
        'assigned_at',
        'start_time',
        'end_time',
        'status',
        'notes'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
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
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();
        
        // Update booking assignment status when assignment is created, updated, or deleted
        static::created(function ($assignment) {
            $assignment->booking->updateAssignmentStatus();
        });
        
        static::updated(function ($assignment) {
            $assignment->booking->updateAssignmentStatus();
        });
        
        static::deleted(function ($assignment) {
            $assignment->booking->updateAssignmentStatus();
        });
    }
}