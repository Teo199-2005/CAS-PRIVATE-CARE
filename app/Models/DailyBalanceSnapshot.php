<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyBalanceSnapshot extends Model
{
    protected $fillable = [
        'snapshot_date',
        'total_revenue',
        'caregiver_payables',
        'caregiver_paid',
        'marketing_commission_payables',
        'marketing_commission_paid',
        'training_commission_payables',
        'training_commission_paid',
        'platform_revenue',
        'stripe_balance',
        'stripe_pending',
        'stripe_reconciled',
        'discrepancies',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'total_revenue' => 'decimal:2',
        'caregiver_payables' => 'decimal:2',
        'caregiver_paid' => 'decimal:2',
        'marketing_commission_payables' => 'decimal:2',
        'marketing_commission_paid' => 'decimal:2',
        'training_commission_payables' => 'decimal:2',
        'training_commission_paid' => 'decimal:2',
        'platform_revenue' => 'decimal:2',
        'stripe_balance' => 'decimal:2',
        'stripe_pending' => 'decimal:2',
        'stripe_reconciled' => 'boolean',
    ];
}
