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
     * Generate a unique referral code for a marketing user
     * Format: LASTNAME + 4 random digits (e.g., SMITH1234)
     */
    public static function generateCode($userId, $lastName = null)
    {
        // Get the user's last name if not provided
        if (!$lastName) {
            $user = \App\Models\User::find($userId);
            if ($user && $user->name) {
                // Extract last name from full name
                $nameParts = explode(' ', trim($user->name));
                $lastName = count($nameParts) > 1 ? end($nameParts) : $nameParts[0];
            }
        }
        
        // Clean the last name: remove special characters, convert to uppercase
        $cleanLastName = strtoupper(preg_replace('/[^a-zA-Z]/', '', $lastName ?? 'STAFF'));
        
        // Limit last name to 10 characters max
        $cleanLastName = substr($cleanLastName, 0, 10);
        
        // Generate 4 random digits
        $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        
        // Create the code
        $code = $cleanLastName . $randomDigits;
        
        // Ensure uniqueness - if code exists, regenerate with different digits
        $attempts = 0;
        while (self::where('code', $code)->exists() && $attempts < 10) {
            $randomDigits = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $code = $cleanLastName . $randomDigits;
            $attempts++;
        }
        
        // If still not unique after 10 attempts, add extra random characters
        if (self::where('code', $code)->exists()) {
            $code = $cleanLastName . $randomDigits . strtoupper(substr(md5(uniqid()), 0, 2));
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
