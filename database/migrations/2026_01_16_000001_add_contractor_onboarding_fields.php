<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds contractor onboarding, tax information, and payout scheduling fields
     */
    public function up(): void
    {
        // Add tax and onboarding fields to users table
        Schema::table('users', function (Blueprint $table) {
            // Tax information (encrypted at application level)
            if (!Schema::hasColumn('users', 'ssn_encrypted')) {
                $table->string('ssn_encrypted')->nullable()->after('itin');
            }
            if (!Schema::hasColumn('users', 'itin_encrypted')) {
                $table->string('itin_encrypted')->nullable()->after('ssn_encrypted');
            }
            if (!Schema::hasColumn('users', 'tax_id_type')) {
                $table->enum('tax_id_type', ['ssn', 'itin', 'ein'])->nullable()->after('itin_encrypted');
            }
            if (!Schema::hasColumn('users', 'legal_name')) {
                $table->string('legal_name')->nullable()->after('tax_id_type');
            }
            if (!Schema::hasColumn('users', 'business_name')) {
                $table->string('business_name')->nullable()->after('legal_name');
            }
            if (!Schema::hasColumn('users', 'tax_classification')) {
                $table->enum('tax_classification', ['individual', 'sole_proprietor', 'single_member_llc', 'c_corporation', 's_corporation', 'partnership', 'trust_estate', 'other'])->nullable()->after('business_name');
            }
            
            // W9 form tracking
            if (!Schema::hasColumn('users', 'w9_submitted')) {
                $table->boolean('w9_submitted')->default(false)->after('tax_classification');
            }
            if (!Schema::hasColumn('users', 'w9_submitted_at')) {
                $table->timestamp('w9_submitted_at')->nullable()->after('w9_submitted');
            }
            if (!Schema::hasColumn('users', 'w9_verified')) {
                $table->boolean('w9_verified')->default(false)->after('w9_submitted_at');
            }
            if (!Schema::hasColumn('users', 'w9_verified_at')) {
                $table->timestamp('w9_verified_at')->nullable()->after('w9_verified');
            }
            if (!Schema::hasColumn('users', 'w9_verified_by')) {
                $table->foreignId('w9_verified_by')->nullable()->after('w9_verified_at');
            }
            
            // Payout preferences
            if (!Schema::hasColumn('users', 'payout_frequency')) {
                $table->enum('payout_frequency', ['weekly', 'biweekly', 'monthly'])->default('weekly')->after('w9_verified_by');
            }
            if (!Schema::hasColumn('users', 'payout_day')) {
                $table->integer('payout_day')->default(5)->after('payout_frequency'); // 1=Mon, 5=Fri
            }
            if (!Schema::hasColumn('users', 'minimum_payout_amount')) {
                $table->decimal('minimum_payout_amount', 8, 2)->default(50.00)->after('payout_day');
            }
            
            // Onboarding tracking
            if (!Schema::hasColumn('users', 'onboarding_steps')) {
                $table->json('onboarding_steps')->nullable()->after('minimum_payout_amount');
            }
            if (!Schema::hasColumn('users', 'onboarding_complete')) {
                $table->boolean('onboarding_complete')->default(false)->after('onboarding_steps');
            }
            if (!Schema::hasColumn('users', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_complete');
            }
        });

        // Add payout_transaction_id to time_trackings if not exists
        Schema::table('time_trackings', function (Blueprint $table) {
            if (!Schema::hasColumn('time_trackings', 'payout_transaction_id')) {
                $table->foreignId('payout_transaction_id')->nullable()->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'ssn_encrypted', 'itin_encrypted', 'tax_id_type', 'legal_name', 
                'business_name', 'tax_classification', 'w9_submitted', 'w9_submitted_at',
                'w9_verified', 'w9_verified_at', 'w9_verified_by', 'payout_frequency',
                'payout_day', 'minimum_payout_amount', 'onboarding_steps',
                'onboarding_complete', 'onboarding_completed_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('time_trackings', function (Blueprint $table) {
            if (Schema::hasColumn('time_trackings', 'payout_transaction_id')) {
                $table->dropColumn('payout_transaction_id');
            }
        });
    }
};
