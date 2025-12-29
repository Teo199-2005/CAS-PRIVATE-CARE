<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'card_type',
        'last_four',
        'card_holder_name',
        'expiry_month',
        'expiry_year',
        'bank_name',
        'account_type',
        'account_last_four',
        'routing_last_four',
        'is_default',
        'stripe_payment_method_id',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user that owns the payment method.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted expiry date
     */
    public function getFormattedExpiryAttribute()
    {
        if ($this->type === 'card' && $this->expiry_month && $this->expiry_year) {
            return $this->expiry_month . '/' . substr($this->expiry_year, -2);
        }
        return null;
    }

    /**
     * Get masked card number
     */
    public function getMaskedCardNumberAttribute()
    {
        if ($this->type === 'card' && $this->last_four) {
            return '•••• •••• •••• ' . $this->last_four;
        }
        return null;
    }

    /**
     * Get masked account number
     */
    public function getMaskedAccountNumberAttribute()
    {
        if ($this->type === 'bank_account' && $this->account_last_four) {
            return '••••••••' . $this->account_last_four;
        }
        return null;
    }

    /**
     * Get masked routing number
     */
    public function getMaskedRoutingNumberAttribute()
    {
        if ($this->type === 'bank_account' && $this->routing_last_four) {
            return '••••••' . $this->routing_last_four;
        }
        return null;
    }
}
