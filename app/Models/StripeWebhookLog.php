<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Stripe Webhook Log Model
 * 
 * Tracks all incoming Stripe webhook events for:
 * - Idempotency (prevent duplicate processing)
 * - Debugging and auditing
 * - Retry tracking for failed webhooks
 * 
 * @property int $id
 * @property string $event_id Stripe event ID
 * @property string $event_type e.g., payment_intent.succeeded
 * @property string $status received, processing, processed, failed, skipped
 * @property array|null $payload Full event payload
 * @property string|null $error_message Error details if failed
 * @property int $retry_count Number of processing attempts
 * @property \Carbon\Carbon|null $processed_at When processing completed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class StripeWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_type',
        'status',
        'payload',
        'error_message',
        'retry_count',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'encrypted:array',
        'processed_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    /**
     * Status constants
     */
    const STATUS_RECEIVED = 'received';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';
    const STATUS_SKIPPED = 'skipped';

    /**
     * Check if an event has already been processed (idempotency)
     */
    public static function hasBeenProcessed(string $eventId): bool
    {
        return self::where('event_id', $eventId)
            ->where('status', self::STATUS_PROCESSED)
            ->exists();
    }

    /**
     * Log a new webhook event
     */
    public static function logEvent(string $eventId, string $eventType, array $payload): self
    {
        return self::create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'status' => self::STATUS_RECEIVED,
            'payload' => $payload,
        ]);
    }

    /**
     * Mark event as processing
     */
    public function markProcessing(): self
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
        ]);
        return $this;
    }

    /**
     * Mark event as successfully processed
     */
    public function markProcessed(): self
    {
        $this->update([
            'status' => self::STATUS_PROCESSED,
            'processed_at' => now(),
        ]);
        return $this;
    }

    /**
     * Mark event as failed with error message
     */
    public function markFailed(string $error): self
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
            'retry_count' => $this->retry_count + 1,
        ]);
        return $this;
    }

    /**
     * Mark event as skipped (duplicate or irrelevant)
     */
    public function markSkipped(string $reason = null): self
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
            'error_message' => $reason,
            'processed_at' => now(),
        ]);
        return $this;
    }

    /**
     * Get failed events that can be retried
     */
    public static function getRetryableEvents(int $maxRetries = 3): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('status', self::STATUS_FAILED)
            ->where('retry_count', '<', $maxRetries)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Scope: Filter by event type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Events from today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope: Recent events (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}
