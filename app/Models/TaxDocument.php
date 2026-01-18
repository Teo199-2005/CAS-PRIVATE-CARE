<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaxDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'tax_year',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'form_data',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
        'submitted_at',
        'sent_at',
        'irs_submission_id',
        'irs_submitted_at',
        'irs_status'
    ];

    protected $casts = [
        'form_data' => 'array',
        'verified_at' => 'datetime',
        'submitted_at' => 'datetime',
        'sent_at' => 'datetime',
        'irs_submitted_at' => 'datetime'
    ];

    /**
     * Get the user that owns this document
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who verified this document
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if document is verified
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if document is pending review
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['submitted', 'pending_review']);
    }

    /**
     * Mark as submitted
     */
    public function markAsSubmitted()
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now()
        ]);
    }

    /**
     * Mark as verified
     */
    public function markAsVerified($verifiedBy)
    {
        $this->update([
            'status' => 'verified',
            'verified_by' => $verifiedBy,
            'verified_at' => now()
        ]);
    }

    /**
     * Mark as rejected
     */
    public function markAsRejected($reason, $rejectedBy)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'verified_by' => $rejectedBy,
            'verified_at' => now()
        ]);
    }

    /**
     * Scope for W9 documents
     */
    public function scopeW9($query)
    {
        return $query->where('document_type', 'w9');
    }

    /**
     * Scope for 1099 documents
     */
    public function scope1099($query)
    {
        return $query->whereIn('document_type', ['1099_nec', '1099_misc']);
    }

    /**
     * Scope for a specific tax year
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('tax_year', $year);
    }

    /**
     * Scope for pending documents
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['submitted', 'pending_review']);
    }

    /**
     * Scope for verified documents
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }
}
