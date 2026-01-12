<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHousekeeperAssignment extends Model
{
    use HasFactory;

    protected $table = 'booking_housekeeper_assignments';

    protected $fillable = [
        'booking_id',
        'housekeeper_id',
        'assigned_at',
        'status',
        'assignment_order',
        'is_active',
        'start_date',
        'end_date',
        'expected_days',
        'assigned_hourly_rate',
        'notes',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'expected_days' => 'integer',
        'assigned_hourly_rate' => 'decimal:2',
    ];

    /**
     * Get the booking that this assignment belongs to
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the housekeeper for this assignment
     */
    public function housekeeper(): BelongsTo
    {
        return $this->belongsTo(Housekeeper::class);
    }
}
