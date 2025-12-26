<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'caregiver_id',
        'type',
        'description',
        'amount',
        'status',
        'method'
    ];

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }
}
