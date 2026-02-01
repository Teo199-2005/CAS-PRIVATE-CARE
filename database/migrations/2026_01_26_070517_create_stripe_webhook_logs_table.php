<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates a webhook logging table for Stripe events.
     * This enables:
     * - Tracking of all received webhook events
     * - Idempotency checking (prevent duplicate processing)
     * - Debugging and auditing of payment events
     * - Retry tracking for failed webhooks
     */
    public function up(): void
    {
        Schema::create('stripe_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 100)->unique()->comment('Stripe event ID for idempotency');
            $table->string('event_type', 100)->index()->comment('e.g., payment_intent.succeeded');
            $table->enum('status', ['received', 'processing', 'processed', 'failed', 'skipped'])
                  ->default('received')
                  ->index();
            $table->json('payload')->nullable()->comment('Full event payload (encrypted)');
            $table->text('error_message')->nullable()->comment('Error details if failed');
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Composite index for querying recent events by type
            $table->index(['event_type', 'created_at']);
            // Index for finding failed events to retry
            $table->index(['status', 'retry_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_webhook_logs');
    }
};
