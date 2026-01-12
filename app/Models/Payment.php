<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'client_id',
        'user_id',
        'caregiver_id',
        'housekeeper_id',
        'provider_type',
        'amount',
        'platform_fee',
        'caregiver_amount',
        'housekeeper_amount',
        'payment_method',
        'status',
        'transaction_id',
        'stripe_payment_intent_id',
        'currency',
        'description',
        'paid_at',
        'notes',
        'processing_fee',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'caregiver_amount' => 'decimal:2',
        'housekeeper_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'processing_fee' => 'decimal:2',
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

    /**
     * Get the provider amount (caregiver_amount or housekeeper_amount)
     */
    public function getProviderAmountAttribute()
    {
        if ($this->provider_type === 'housekeeper') {
            return $this->housekeeper_amount;
        }
        return $this->caregiver_amount;
    }
}