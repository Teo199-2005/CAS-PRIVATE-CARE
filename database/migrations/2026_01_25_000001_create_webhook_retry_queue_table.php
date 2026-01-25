<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create webhook retry queue table for handling failed webhook processing
 * 
 * Phase 2 Maintainability Enhancement
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhook_retry_queue', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->index(); // stripe, brevo, etc.
            $table->string('event_id', 255)->index(); // unique event identifier
            $table->string('event_type', 100)->index(); // e.g., payment_intent.succeeded
            $table->longText('payload'); // JSON payload
            $table->text('error_message')->nullable(); // last error
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Composite index for efficient retry queries
            $table->index(['status', 'retry_count', 'next_retry_at'], 'webhook_retry_lookup');
            
            // Unique constraint to prevent duplicate event processing
            $table->unique(['provider', 'event_id'], 'unique_webhook_event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_retry_queue');
    }
};
