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
        'page_permissions',
        // Session enforcement for admin single session
        'session_token',
        'session_started_at',
        // Contractor onboarding fields
        'w9_submitted',
        'w9_verified',
        'w9_submitted_at',
        'w9_verified_at',
        'onboarding_step',
        'onboarding_completed',
        'onboarding_completed_at',
        'payout_frequency',
        'payout_minimum_amount',
        'payout_day',
        'tax_classification',
        'business_name',
        'ein',
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
            'w9_submitted' => 'boolean',
            'w9_verified' => 'boolean',
            'w9_submitted_at' => 'datetime',
            'w9_verified_at' => 'datetime',
            'onboarding_completed' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'payout_minimum_amount' => 'decimal:2',
            'session_started_at' => 'datetime',
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
    
    /**
     * Get tax documents for the user.
     */
    public function taxDocuments()
    {
        return $this->hasMany(TaxDocument::class);
    }
    
    /**
     * Get 1099 forms for the user.
     */
    public function taxForms1099()
    {
        return $this->hasMany(TaxForm1099::class);
    }
    
    /**
     * Get compliance checks for the user.
     */
    public function complianceChecks()
    {
        return $this->hasMany(ComplianceCheck::class);
    }
    
    /**
     * Get latest compliance check.
     */
    public function latestComplianceCheck()
    {
        return $this->hasOne(ComplianceCheck::class)->latestOfMany('check_date');
    }
    
    /**
     * Check if user is a contractor type.
     */
    public function isContractor(): bool
    {
        return in_array($this->user_type, ['caregiver', 'housekeeper', 'marketing', 'training_center']);
    }
    
    /**
     * Check if contractor onboarding is complete.
     */
    public function isOnboardingComplete(): bool
    {
        if (!$this->isContractor()) {
            return true;
        }
        
        return $this->w9_submitted 
            && $this->w9_verified 
            && !empty($this->stripe_connect_id);
    }
    
    /**
     * Get contractor's effective payout frequency.
     */
    public function getPayoutFrequency(): string
    {
        return $this->payout_frequency ?? 'weekly';
    }
}
