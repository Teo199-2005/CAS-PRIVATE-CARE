<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduledPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'scheduled_date',
        'frequency',
        'status',
        'total_contractors',
        'successful_payouts',
        'failed_payouts',
        'total_amount',
        'successful_amount',
        'failed_amount',
        'started_at',
        'completed_at',
        'processing_duration_seconds',
        'initiated_by',
        'error_log',
        'notes'
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'error_log' => 'array',
        'total_amount' => 'decimal:2',
        'successful_amount' => 'decimal:2',
        'failed_amount' => 'decimal:2'
    ];

    /**
     * Get the user who initiated this payout (null = automated)
     */
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get associated payout transactions
     */
    public function payoutTransactions()
    {
        return $this->hasMany(PayoutTransaction::class);
    }

    /**
     * Check if this is an automated payout
     */
    public function isAutomated(): bool
    {
        return is_null($this->initiated_by);
    }

    /**
     * Check if payout is still pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payout is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if payout is complete
     */
    public function isComplete(): bool
    {
        return in_array($this->status, ['completed', 'partial']);
    }

    /**
     * Start processing
     */
    public function startProcessing()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now()
        ]);
    }

    /**
     * Complete processing
     */
    public function completeProcessing($successful, $failed, $successfulAmount, $failedAmount)
    {
        $status = $failed > 0 ? ($successful > 0 ? 'partial' : 'failed') : 'completed';
        
        $this->update([
            'status' => $status,
            'successful_payouts' => $successful,
            'failed_payouts' => $failed,
            'successful_amount' => $successfulAmount,
            'failed_amount' => $failedAmount,
            'completed_at' => now(),
            'processing_duration_seconds' => now()->diffInSeconds($this->started_at)
        ]);
    }

    /**
     * Add error to log
     */
    public function addError($contractorId, $error)
    {
        $errorLog = $this->error_log ?? [];
        $errorLog[] = [
            'contractor_id' => $contractorId,
            'error' => $error,
            'timestamp' => now()->toISOString()
        ];
        $this->update(['error_log' => $errorLog]);
    }

    /**
     * Get success rate as percentage
     */
    public function getSuccessRateAttribute()
    {
        if ($this->total_contractors == 0) return 0;
        return round(($this->successful_payouts / $this->total_contractors) * 100, 1);
    }

    /**
     * Scope for pending payouts
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed payouts
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['completed', 'partial']);
    }

    /**
     * Scope for payouts by frequency
     */
    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Scope for payouts on a specific date
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('scheduled_date', $date);
    }
}
