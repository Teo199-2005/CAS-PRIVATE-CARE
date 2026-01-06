<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Payout transactions table - records every single payout attempt
        Schema::create('payout_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caregiver_id')->constrained('caregivers')->onDelete('cascade');
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->onDelete('set null'); // Who approved it
            
            // Financial details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');
            
            // Stripe details
            $table->string('stripe_transfer_id')->nullable()->unique(); // Stripe transfer ID
            $table->string('stripe_connect_id'); // Destination account
            
            // Status tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'reversed'])->default('pending');
            $table->text('failure_reason')->nullable();
            
            // Time tracking records included in this payout
            $table->json('time_tracking_ids'); // Array of time_tracking IDs paid
            $table->integer('sessions_count');
            $table->decimal('total_hours', 8, 2);
            
            // Timestamps
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['caregiver_id', 'status']);
            $table->index('created_at');
            $table->index('stripe_transfer_id');
        });

        // Financial ledger - double-entry bookkeeping
        Schema::create('financial_ledger', function (Blueprint $table) {
            $table->id();
            
            // Transaction details
            $table->string('transaction_type'); // 'booking_payment', 'caregiver_payout', 'marketing_commission', etc.
            $table->foreignId('related_id')->nullable(); // ID of booking, payout_transaction, etc.
            $table->string('related_type')->nullable(); // Model name
            
            // Accounts (double-entry)
            $table->string('debit_account'); // e.g., 'caregiver_payables', 'platform_revenue'
            $table->string('credit_account'); // e.g., 'bank_account', 'caregiver_payables'
            $table->decimal('amount', 10, 2);
            
            // References
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('description');
            $table->json('metadata')->nullable(); // Store additional data
            
            // Reconciliation
            $table->boolean('reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->foreignId('reconciled_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['transaction_type', 'created_at']);
            $table->index(['debit_account', 'credit_account']);
            $table->index('reconciled');
        });

        // Daily balance snapshots
        Schema::create('daily_balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date')->unique();
            
            // Balances
            $table->decimal('total_revenue', 12, 2)->default(0); // All client payments
            $table->decimal('caregiver_payables', 12, 2)->default(0); // Owed to caregivers
            $table->decimal('caregiver_paid', 12, 2)->default(0); // Already paid to caregivers
            $table->decimal('marketing_commission_payables', 12, 2)->default(0);
            $table->decimal('marketing_commission_paid', 12, 2)->default(0);
            $table->decimal('training_commission_payables', 12, 2)->default(0);
            $table->decimal('training_commission_paid', 12, 2)->default(0);
            $table->decimal('platform_revenue', 12, 2)->default(0); // Agency's cut
            
            // Stripe reconciliation
            $table->decimal('stripe_balance', 12, 2)->nullable(); // From Stripe API
            $table->decimal('stripe_pending', 12, 2)->nullable();
            $table->boolean('stripe_reconciled')->default(false);
            $table->text('discrepancies')->nullable(); // Any issues found
            
            $table->timestamps();
        });

        // Payout verification log
        Schema::create('payout_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_transaction_id')->constrained('payout_transactions');
            
            // Verification details
            $table->enum('verification_type', ['pre_payment', 'post_payment', 'reconciliation']);
            $table->boolean('passed')->default(false);
            $table->json('checks_performed'); // What was verified
            $table->json('results'); // Results of each check
            $table->text('notes')->nullable();
            
            // Who verified
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_verifications');
        Schema::dropIfExists('daily_balance_snapshots');
        Schema::dropIfExists('financial_ledger');
        Schema::dropIfExists('payout_transactions');
    }
};
