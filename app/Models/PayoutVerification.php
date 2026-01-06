<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutVerification extends Model
{
    protected $fillable = [
        'payout_transaction_id',
        'verification_type',
        'passed',
        'checks_performed',
        'results',
        'notes',
        'verified_by',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'checks_performed' => 'array',
        'results' => 'array',
    ];

    public function payoutTransaction()
    {
        return $this->belongsTo(PayoutTransaction::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
