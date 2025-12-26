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
        'caregiver_id',
        'amount',
        'platform_fee',
        'caregiver_amount',
        'payment_method',
        'status',
        'transaction_id',
        'paid_at',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'caregiver_amount' => 'decimal:2',
        'paid_at' => 'datetime'
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
}