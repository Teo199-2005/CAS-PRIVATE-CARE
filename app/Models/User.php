<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'status',
        'phone',
        'date_of_birth',
        'address',
        'city',
        'county',
        'borough',
        'state',
        'zip_code',
        'ssn',
        'itin',
        'department',
        'role',
        'avatar',
        'stripe_customer_id',
        'stripe_account_id',
        'stripe_connect_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function caregiver()
    {
        return $this->hasOne(Caregiver::class);
    }

    public function referralCode()
    {
        return $this->hasOne(ReferralCode::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Booking::class, 'client_id', 'booking_id');
    }

    /**
     * Get the housekeeper profile associated with the user.
     */
    public function housekeeper()
    {
        return $this->hasOne(Housekeeper::class);
    }
}
