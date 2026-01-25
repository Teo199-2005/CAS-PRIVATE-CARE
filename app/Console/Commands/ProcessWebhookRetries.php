<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WebhookRetryService;
use Illuminate\Support\Facades\Log;

/**
 * Process pending webhook retries
 * 
 * Phase 2 Maintainability Enhancement
 * Run via: php artisan webhooks:retry
 * Schedule: Every 5 minutes in production
 */
class ProcessWebhookRetries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhooks:retry 
                            {--limit=50 : Maximum webhooks to process per run}
                            {--cleanup : Also cleanup old completed/failed webhooks}
                            {--days=30 : Days to keep when cleaning up}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending webhook retries with exponential backoff';

    protected WebhookRetryService $retryService;

    public function __construct(WebhookRetryService $retryService)
    {
        parent::__construct();
        $this->retryService = $retryService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        
        // Show stats first
        $stats = $this->retryService->getStats();
        $this->info("Webhook Retry Queue Stats:");
        $this->table(
            ['Status', 'Count'],
            [
                ['Pending', $stats['pending']],
                ['Completed', $stats['completed']],
                ['Failed', $stats['failed']],
                ['Total', $stats['total']],
            ]
        );

        // Get pending retries
        $pending = $this->retryService->getPendingRetries($limit);
        
        if (empty($pending)) {
            $this->info('No pending webhook retries to process.');
            
            if ($this->option('cleanup')) {
                $this->cleanup();
            }
            
            return Command::SUCCESS;
        }

        $this->info("Processing {$limit} pending webhook retries...");
        $bar = $this->output->createProgressBar(count($pending));
        $bar->start();

        $processed = 0;
        $succeeded = 0;
        $failed = 0;

        foreach ($pending as $webhook) {
            try {
                $result = $this->processWebhook($webhook);
                
                if ($result) {
                    $this->retryService->markSuccess($webhook->id);
                    $succeeded++;
                } else {
                    $this->retryService->markFailed($webhook->id, 'Processing returned false');
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->retryService->markFailed($webhook->id, $e->getMessage());
                $failed++;
                
                Log::error('Webhook retry failed', [
                    'webhook_id' => $webhook->id,
                    'event_type' => $webhook->event_type,
                    'error' => $e->getMessage(),
                ]);
            }
            
            $processed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("Processed: {$processed}, Succeeded: {$succeeded}, Failed: {$failed}");
        
        if ($this->option('cleanup')) {
            $this->cleanup();
        }

        return Command::SUCCESS;
    }

    /**
     * Process a single webhook retry
     */
    protected function processWebhook(object $webhook): bool
    {
        $payload = $webhook->payload;
        
        if ($webhook->provider !== 'stripe') {
            Log::warning('Unsupported webhook provider for retry', [
                'provider' => $webhook->provider,
            ]);
            return false;
        }

        // Dispatch to appropriate Stripe handler
        $controller = app(\App\Http\Controllers\StripeWebhookController::class);
        
        // Create a mock event object similar to Stripe's structure
        $eventData = (object) [
            'type' => $webhook->event_type,
            'id' => $webhook->event_id,
            'data' => (object) [
                'object' => (object) ($payload['data']['object'] ?? []),
            ],
        ];

        // Call the appropriate handler method based on event type
        $handlerMethod = $this->getHandlerMethod($webhook->event_type);
        
        if (!$handlerMethod || !method_exists($controller, $handlerMethod)) {
            Log::warning('No handler found for webhook event type', [
                'event_type' => $webhook->event_type,
            ]);
            return true; // Mark as success since we can't process it
        }

        // Use reflection to call protected method
        $reflection = new \ReflectionMethod($controller, $handlerMethod);
        $reflection->setAccessible(true);
        $reflection->invoke($controller, $eventData->data->object);

        return true;
    }

    /**
     * Map event types to handler methods
     */
    protected function getHandlerMethod(string $eventType): ?string
    {
        $map = [
            'invoice.payment_succeeded' => 'handleInvoicePaymentSucceeded',
            'invoice.payment_failed' => 'handleInvoicePaymentFailed',
            'customer.subscription.deleted' => 'handleSubscriptionDeleted',
            'customer.subscription.updated' => 'handleSubscriptionUpdated',
            'payment_intent.succeeded' => 'handlePaymentIntentSucceeded',
            'payment_intent.payment_failed' => 'handlePaymentIntentFailed',
            'charge.dispute.created' => 'handleDisputeCreated',
            'charge.dispute.closed' => 'handleDisputeClosed',
            'charge.refunded' => 'handleChargeRefunded',
        ];

        return $map[$eventType] ?? null;
    }

    /**
     * Cleanup old webhook records
     */
    protected function cleanup(): void
    {
        $days = (int) $this->option('days');
        $deleted = $this->retryService->cleanup($days);
        $this->info("Cleaned up {$deleted} old webhook records (older than {$days} days).");
    }
}
