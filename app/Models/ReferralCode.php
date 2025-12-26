<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'discount_per_hour',
        'commission_per_hour',
        'is_active',
        'usage_count',
        'total_commission_earned',
    ];

    protected $casts = [
        'discount_per_hour' => 'decimal:2',
        'commission_per_hour' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the marketing staff user who owns this referral code
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all bookings that used this referral code
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Generate a unique referral code for a user
     */
    public static function generateCode($userId)
    {
        $prefix = 'STAFF';
        $code = $prefix . '-' . str_pad($userId, 3, '0', STR_PAD_LEFT);
        
        // Check if code exists, if so add random suffix
        $existingCode = self::where('code', $code)->first();
        if ($existingCode) {
            $code = $prefix . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        }
        
        return $code;
    }

    /**
     * Validate and find a referral code
     */
    public static function findValidCode($code)
    {
        return self::where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();
    }

    /**
     * Increment usage and update commission
     */
    public function recordUsage($hoursWorked)
    {
        $this->increment('usage_count');
        $commission = $hoursWorked * $this->commission_per_hour;
        $this->increment('total_commission_earned', $commission);
        return $commission;
    }
}
