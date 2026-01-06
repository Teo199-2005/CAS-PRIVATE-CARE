<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutTransaction extends Model
{
    protected $fillable = [
        'caregiver_id',
        'admin_user_id',
        'amount',
        'currency',
        'stripe_transfer_id',
        'stripe_connect_id',
        'status',
        'failure_reason',
        'time_tracking_ids',
        'sessions_count',
        'total_hours',
        'initiated_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'time_tracking_ids' => 'array',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function verifications()
    {
        return $this->hasMany(PayoutVerification::class);
    }

    public function ledgerEntries()
    {
        return $this->morphMany(FinancialLedger::class, 'related');
    }
}
