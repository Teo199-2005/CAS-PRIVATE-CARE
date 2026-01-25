<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Webhook Retry Service
 * 
 * Handles failed webhook processing with exponential backoff retry logic.
 * Stores failed webhooks in database for later retry processing.
 * 
 * Phase 2 Maintainability Enhancement
 */
class WebhookRetryService
{
    /**
     * Maximum number of retry attempts
     */
    const MAX_RETRIES = 5;

    /**
     * Base delay in seconds for exponential backoff
     */
    const BASE_DELAY_SECONDS = 60;

    /**
     * Queue a failed webhook for retry
     *
     * @param string $provider The webhook provider (stripe, etc.)
     * @param string $eventType The event type (e.g., payment_intent.succeeded)
     * @param array $payload The full webhook payload
     * @param string $errorMessage The error that caused the failure
     * @return int|null The queued webhook ID or null on failure
     */
    public function queueForRetry(string $provider, string $eventType, array $payload, string $errorMessage): ?int
    {
        try {
            $eventId = $payload['id'] ?? uniqid('webhook_');
            
            // Check if this event is already queued
            $existing = DB::table('webhook_retry_queue')
                ->where('event_id', $eventId)
                ->where('status', '!=', 'completed')
                ->first();

            if ($existing) {
                Log::info('Webhook already queued for retry', ['event_id' => $eventId]);
                return $existing->id;
            }

            $id = DB::table('webhook_retry_queue')->insertGetId([
                'provider' => $provider,
                'event_id' => $eventId,
                'event_type' => $eventType,
                'payload' => json_encode($payload),
                'error_message' => $errorMessage,
                'retry_count' => 0,
                'status' => 'pending',
                'next_retry_at' => Carbon::now()->addSeconds(self::BASE_DELAY_SECONDS),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            Log::warning('Webhook queued for retry', [
                'id' => $id,
                'provider' => $provider,
                'event_type' => $eventType,
                'event_id' => $eventId,
            ]);

            return $id;
        } catch (\Exception $e) {
            Log::error('Failed to queue webhook for retry', [
                'provider' => $provider,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get pending webhooks that are ready for retry
     *
     * @param int $limit Maximum number of webhooks to retrieve
     * @return array
     */
    public function getPendingRetries(int $limit = 50): array
    {
        return DB::table('webhook_retry_queue')
            ->where('status', 'pending')
            ->where('retry_count', '<', self::MAX_RETRIES)
            ->where('next_retry_at', '<=', Carbon::now())
            ->orderBy('next_retry_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $item->payload = json_decode($item->payload, true);
                return $item;
            })
            ->toArray();
    }

    /**
     * Mark a webhook retry as successful
     *
     * @param int $id The webhook queue ID
     * @return bool
     */
    public function markSuccess(int $id): bool
    {
        return DB::table('webhook_retry_queue')
            ->where('id', $id)
            ->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]) > 0;
    }

    /**
     * Mark a webhook retry as failed and schedule next attempt
     *
     * @param int $id The webhook queue ID
     * @param string $errorMessage The error message from this attempt
     * @return bool
     */
    public function markFailed(int $id, string $errorMessage): bool
    {
        $webhook = DB::table('webhook_retry_queue')->where('id', $id)->first();
        
        if (!$webhook) {
            return false;
        }

        $newRetryCount = $webhook->retry_count + 1;
        $status = $newRetryCount >= self::MAX_RETRIES ? 'failed' : 'pending';
        
        // Exponential backoff: 1min, 2min, 4min, 8min, 16min
        $nextRetryDelay = self::BASE_DELAY_SECONDS * pow(2, $newRetryCount - 1);
        $nextRetryAt = Carbon::now()->addSeconds($nextRetryDelay);

        $updated = DB::table('webhook_retry_queue')
            ->where('id', $id)
            ->update([
                'retry_count' => $newRetryCount,
                'status' => $status,
                'error_message' => $errorMessage,
                'next_retry_at' => $status === 'pending' ? $nextRetryAt : null,
                'updated_at' => Carbon::now(),
            ]);

        if ($status === 'failed') {
            Log::error('Webhook retry exhausted - marked as failed', [
                'id' => $id,
                'event_id' => $webhook->event_id,
                'retry_count' => $newRetryCount,
            ]);
        } else {
            Log::warning('Webhook retry scheduled', [
                'id' => $id,
                'event_id' => $webhook->event_id,
                'retry_count' => $newRetryCount,
                'next_retry_at' => $nextRetryAt->toIso8601String(),
            ]);
        }

        return $updated > 0;
    }

    /**
     * Get retry statistics
     *
     * @return array
     */
    public function getStats(): array
    {
        $stats = DB::table('webhook_retry_queue')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'pending' => $stats['pending'] ?? 0,
            'completed' => $stats['completed'] ?? 0,
            'failed' => $stats['failed'] ?? 0,
            'total' => array_sum($stats),
        ];
    }

    /**
     * Clean up old completed/failed webhooks
     *
     * @param int $daysToKeep Number of days to keep records
     * @return int Number of records deleted
     */
    public function cleanup(int $daysToKeep = 30): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);

        return DB::table('webhook_retry_queue')
            ->whereIn('status', ['completed', 'failed'])
            ->where('updated_at', '<', $cutoffDate)
            ->delete();
    }
}
