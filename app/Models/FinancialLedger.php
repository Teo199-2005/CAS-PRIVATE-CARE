<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialLedger extends Model
{
    protected $table = 'financial_ledger';
    
    protected $fillable = [
        'transaction_type',
        'related_id',
        'related_type',
        'debit_account',
        'credit_account',
        'amount',
        'user_id',
        'description',
        'metadata',
        'reconciled',
        'reconciled_at',
        'reconciled_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'reconciled' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    public function related()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reconciledBy()
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }
}
