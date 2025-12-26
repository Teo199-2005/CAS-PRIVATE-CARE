<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'mobility_level',
        'medical_conditions',
        'allergies',
        'medications',
        'special_needs',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'insurance_provider',
        'insurance_policy_number',
        'preferred_language',
        'account_type',
        'two_factor_enabled',
        'notification_preferences',
        'timezone',
        'bio',
        'profile_photo',
        'preferred_caregivers',
        'blocked_caregivers',
        'verified'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'medical_conditions' => 'array',
        'allergies' => 'array',
        'medications' => 'array',
        'notification_preferences' => 'array',
        'preferred_caregivers' => 'array',
        'blocked_caregivers' => 'array',
        'two_factor_enabled' => 'boolean',
        'verified' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        // Bookings are linked to the User, not the Client record directly
        // The booking's client_id references users.id, not clients.id
        return $this->hasManyThrough(
            Booking::class,
            User::class,
            'id',           // Foreign key on users table (users.id)
            'client_id',    // Foreign key on bookings table (bookings.client_id)
            'user_id',      // Local key on clients table (clients.user_id)
            'id'            // Local key on users table for bookings
        );
    }
    
    /**
     * Get bookings directly by user_id (simpler approach)
     */
    public function userBookings()
    {
        return Booking::where('client_id', $this->user_id);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}