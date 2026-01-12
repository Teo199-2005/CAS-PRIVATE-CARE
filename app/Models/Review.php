<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'client_id',
        'caregiver_id',
        'housekeeper_id',
        'provider_type',
        'rating',
        'comment',
        'recommend'
    ];

    protected $casts = [
        'recommend' => 'boolean'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function housekeeper()
    {
        return $this->belongsTo(Housekeeper::class);
    }

    /**
     * Get the provider (caregiver or housekeeper) based on provider_type
     */
    public function provider()
    {
        if ($this->provider_type === 'housekeeper' && $this->housekeeper_id) {
            return $this->housekeeper;
        }
        return $this->caregiver;
    }
}