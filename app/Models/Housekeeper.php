<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Housekeeper extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_connect_id',
        'stripe_charges_enabled',
        'stripe_payouts_enabled',
        'gender',
        'skills',
        'specializations',
        'years_experience',
        'hourly_rate',
        'certifications',
        'bio',
        'background_check_completed',
        'has_own_supplies',
        'available_for_transport',
        'availability_status',
        'rating',
        'total_reviews',
    ];

    protected $casts = [
        'skills' => 'array',
        'specializations' => 'array',
        'certifications' => 'array',
        'background_check_completed' => 'boolean',
        'has_own_supplies' => 'boolean',
        'available_for_transport' => 'boolean',
        'stripe_charges_enabled' => 'boolean',
        'stripe_payouts_enabled' => 'boolean',
        'rating' => 'float',
        'years_experience' => 'integer',
        'hourly_rate' => 'float',
        'total_reviews' => 'integer',
    ];

    /**
     * Get the user that owns the housekeeper profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assignments for this housekeeper.
     */
    public function assignments()
    {
        return $this->hasMany(BookingAssignment::class, 'housekeeper_id');
    }

    /**
     * Get the time trackings for this housekeeper.
     */
    public function timeTrackings()
    {
        return $this->hasMany(TimeTracking::class, 'housekeeper_id');
    }

    /**
     * Get the reviews for this housekeeper.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'housekeeper_id');
    }
}
