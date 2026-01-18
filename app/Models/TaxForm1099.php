<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxForm1099 extends Model
{
    use HasFactory;

    protected $table = 'tax_forms_1099';

    protected $fillable = [
        'user_id',
        'tax_year',
        'form_type',
        'box1_nonemployee_compensation',
        'box4_federal_tax_withheld',
        'box5_state_tax_withheld',
        'total_compensation',
        'payer_info',
        'recipient_info',
        'pdf_path_copy_b',
        'pdf_path_copy_c',
        'status',
        'correction_required',
        'correction_notes',
        'generated_at',
        'sent_to_recipient_at',
        'sent_to_irs_at',
        'delivery_method',
        'delivery_email',
        'delivery_address'
    ];

    protected $casts = [
        'payer_info' => 'array',
        'recipient_info' => 'array',
        'delivery_address' => 'array',
        'generated_at' => 'datetime',
        'sent_to_recipient_at' => 'datetime',
        'sent_to_irs_at' => 'datetime',
        'correction_required' => 'boolean',
        'box1_nonemployee_compensation' => 'decimal:2',
        'box4_federal_tax_withheld' => 'decimal:2',
        'box5_state_tax_withheld' => 'decimal:2',
        'total_compensation' => 'decimal:2'
    ];

    /**
     * Get the user/contractor for this 1099
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if 1099 has been sent to recipient
     */
    public function isSentToRecipient(): bool
    {
        return !is_null($this->sent_to_recipient_at);
    }

    /**
     * Check if 1099 has been filed with IRS
     */
    public function isFiledWithIRS(): bool
    {
        return !is_null($this->sent_to_irs_at);
    }

    /**
     * Mark as generated
     */
    public function markAsGenerated()
    {
        $this->update([
            'status' => 'generated',
            'generated_at' => now()
        ]);
    }

    /**
     * Mark as sent to recipient
     */
    public function markAsSentToRecipient($method, $email = null, $address = null)
    {
        $this->update([
            'status' => 'sent_to_recipient',
            'sent_to_recipient_at' => now(),
            'delivery_method' => $method,
            'delivery_email' => $email,
            'delivery_address' => $address
        ]);
    }

    /**
     * Mark as sent to IRS
     */
    public function markAsSentToIRS()
    {
        $this->update([
            'status' => 'sent_to_irs',
            'sent_to_irs_at' => now()
        ]);
    }

    /**
     * Scope for a specific tax year
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('tax_year', $year);
    }

    /**
     * Scope for 1099-NEC forms
     */
    public function scopeNEC($query)
    {
        return $query->where('form_type', '1099-NEC');
    }

    /**
     * Scope for forms not yet sent to recipients
     */
    public function scopeNotSentToRecipient($query)
    {
        return $query->whereNull('sent_to_recipient_at');
    }

    /**
     * Scope for forms requiring correction
     */
    public function scopeNeedsCorrection($query)
    {
        return $query->where('correction_required', true);
    }

    /**
     * Get formatted recipient info for display
     */
    public function getRecipientDisplayNameAttribute()
    {
        if ($this->recipient_info && isset($this->recipient_info['name'])) {
            return $this->recipient_info['name'];
        }
        return $this->user?->name ?? 'Unknown';
    }

    /**
     * Get masked TIN for display
     */
    public function getMaskedTinAttribute()
    {
        if ($this->recipient_info && isset($this->recipient_info['tin_last_four'])) {
            return '***-**-' . $this->recipient_info['tin_last_four'];
        }
        return '***-**-****';
    }
}
