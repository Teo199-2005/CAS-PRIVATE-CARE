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
}