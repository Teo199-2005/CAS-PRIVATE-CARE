<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caregiver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'training_center_id',
        'has_training_center',
        'training_center_approval_status',
        'first_name',
        'last_name',
        'gender',
        'skills',
        'specializations',
        'years_experience',
        'training_certificate',
        'hourly_rate',
        'license_number',
        'certifications',
        'bio',
        'background_check_completed',
        'available_for_transport',
        'availability_status',
        'rating',
        'total_reviews',
        'has_hha',
        'hha_number',
        'has_cna',
        'cna_number',
        'has_rn',
        'rn_number',
        'preferred_hourly_rate_min',
        'preferred_hourly_rate_max'
    ];

    protected $casts = [
        'skills' => 'array',
        'specializations' => 'array',
        'certifications' => 'array',
        'background_check_completed' => 'boolean',
        'available_for_transport' => 'boolean',
        'has_training_center' => 'boolean',
        'has_hha' => 'boolean',
        'has_cna' => 'boolean',
        'has_rn' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'preferred_hourly_rate_min' => 'decimal:2',
        'preferred_hourly_rate_max' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainingCenter()
    {
        return $this->belongsTo(User::class, 'training_center_id');
    }

    public function bookingAssignments()
    {
        return $this->hasMany(BookingAssignment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function timeTrackings()
    {
        return $this->hasMany(TimeTracking::class);
    }

    public function getCurrentTimeTracking()
    {
        return $this->timeTrackings()
            ->where('status', 'active')
            ->whereNull('clock_out_time')
            ->first();
    }

    /**
     * Check if a given rate is within the caregiver's preferred range
     */
    public function isRateWithinPreferredRange($rate)
    {
        $min = $this->preferred_hourly_rate_min ?? 20;
        $max = $this->preferred_hourly_rate_max ?? 50;
        return $rate >= $min && $rate <= $max;
    }

    /**
     * Get the preferred rate range as a formatted string
     */
    public function getPreferredRangeAttribute()
    {
        $min = $this->preferred_hourly_rate_min ?? 20;
        $max = $this->preferred_hourly_rate_max ?? 50;
        return "$" . number_format($min, 0) . " - $" . number_format($max, 0);
    }
}