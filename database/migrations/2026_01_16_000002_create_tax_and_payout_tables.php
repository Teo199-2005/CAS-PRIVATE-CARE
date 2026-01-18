<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates tables for tax documents, 1099 forms, and scheduled payouts
     */
    public function up(): void
    {
        // Tax Documents Table - stores W9 and other tax documents
        Schema::create('tax_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('document_type', ['w9', 'w9_correction', '1099_nec', '1099_misc', 'other']);
            $table->integer('tax_year')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable();
            
            // Form data (for digital submissions)
            $table->json('form_data')->nullable();
            
            // Status tracking
            $table->enum('status', ['draft', 'submitted', 'pending_review', 'verified', 'rejected', 'sent'])->default('draft');
            $table->text('rejection_reason')->nullable();
            
            // Verification
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            
            // For 1099s - IRS tracking
            $table->string('irs_submission_id')->nullable();
            $table->timestamp('irs_submitted_at')->nullable();
            $table->enum('irs_status', ['not_submitted', 'pending', 'accepted', 'rejected'])->default('not_submitted');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'document_type']);
            $table->index(['tax_year', 'document_type']);
            $table->index('status');
        });

        // Tax Forms Table - specifically for generated 1099s
        Schema::create('tax_forms_1099', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('tax_year');
            $table->enum('form_type', ['1099-NEC', '1099-MISC', '1099-K'])->default('1099-NEC');
            
            // Compensation amounts
            $table->decimal('box1_nonemployee_compensation', 12, 2)->default(0); // Box 1 on 1099-NEC
            $table->decimal('box4_federal_tax_withheld', 12, 2)->default(0);
            $table->decimal('box5_state_tax_withheld', 12, 2)->default(0);
            $table->decimal('total_compensation', 12, 2)->default(0);
            
            // Payer information
            $table->json('payer_info')->nullable(); // Company info
            
            // Recipient information (from user at time of generation)
            $table->json('recipient_info')->nullable();
            
            // Generated files
            $table->string('pdf_path_copy_b')->nullable(); // For recipient
            $table->string('pdf_path_copy_c')->nullable(); // For payer records
            
            // Status
            $table->enum('status', ['draft', 'generated', 'sent_to_recipient', 'sent_to_irs', 'corrected'])->default('draft');
            $table->boolean('correction_required')->default(false);
            $table->text('correction_notes')->nullable();
            
            // Delivery tracking
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('sent_to_recipient_at')->nullable();
            $table->timestamp('sent_to_irs_at')->nullable();
            $table->string('delivery_method')->nullable(); // 'email', 'mail', 'portal'
            $table->string('delivery_email')->nullable();
            $table->json('delivery_address')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['user_id', 'tax_year', 'form_type']);
            $table->index(['tax_year', 'status']);
        });

        // Scheduled Payouts Table - tracks batch payout runs
        Schema::create('scheduled_payouts', function (Blueprint $table) {
            $table->id();
            $table->date('scheduled_date');
            $table->enum('frequency', ['weekly', 'biweekly', 'monthly']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'partial'])->default('pending');
            
            // Summary stats
            $table->integer('total_contractors')->default(0);
            $table->integer('successful_payouts')->default(0);
            $table->integer('failed_payouts')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('successful_amount', 12, 2)->default(0);
            $table->decimal('failed_amount', 12, 2)->default(0);
            
            // Processing times
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('processing_duration_seconds')->nullable();
            
            // Initiated by (null = automated, user_id = manual)
            $table->foreignId('initiated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Error tracking
            $table->json('error_log')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['scheduled_date', 'frequency']);
            $table->index('status');
        });

        // Payout Settings Table - global configuration
        Schema::create('payout_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, int, float, bool, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Compliance Checks Table - tracks contractor compliance status
        Schema::create('compliance_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('check_date');
            
            // Check results (JSON for flexibility)
            $table->json('checks_performed');
            $table->json('check_results');
            $table->boolean('overall_compliant')->default(false);
            
            // Specific flags
            $table->boolean('w9_compliant')->default(false);
            $table->boolean('bank_compliant')->default(false);
            $table->boolean('background_check_compliant')->default(false);
            $table->boolean('certification_compliant')->default(false);
            $table->boolean('work_pattern_compliant')->default(true);
            
            // Work pattern data
            $table->decimal('average_weekly_hours', 8, 2)->nullable();
            $table->integer('unique_clients_count')->nullable();
            $table->text('compliance_notes')->nullable();
            
            // Who ran the check
            $table->enum('check_type', ['automated', 'manual'])->default('automated');
            $table->foreignId('checked_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'check_date']);
            $table->index('overall_compliant');
        });

        // Insert default payout settings
        \DB::table('payout_settings')->insert([
            [
                'key' => 'default_payout_frequency',
                'value' => 'weekly',
                'type' => 'string',
                'description' => 'Default payout frequency for new contractors',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'default_payout_day',
                'value' => '5',
                'type' => 'int',
                'description' => 'Default payout day (1=Monday, 5=Friday)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'minimum_payout_amount',
                'value' => '50.00',
                'type' => 'float',
                'description' => 'Minimum amount required for payout',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'auto_approve_threshold',
                'value' => '500.00',
                'type' => 'float',
                'description' => 'Auto-approve payouts below this amount',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => '1099_threshold',
                'value' => '600.00',
                'type' => 'float',
                'description' => 'Minimum annual earnings requiring 1099',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'require_w9_before_payout',
                'value' => 'true',
                'type' => 'bool',
                'description' => 'Require W9 before processing payouts',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'company_name',
                'value' => 'CAS Private Care LLC',
                'type' => 'string',
                'description' => 'Company name for tax documents',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'company_ein',
                'value' => '',
                'type' => 'string',
                'description' => 'Company EIN for tax documents',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'company_address',
                'value' => '{"street": "", "city": "New York", "state": "NY", "zip": ""}',
                'type' => 'json',
                'description' => 'Company address for tax documents',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compliance_checks');
        Schema::dropIfExists('payout_settings');
        Schema::dropIfExists('scheduled_payouts');
        Schema::dropIfExists('tax_forms_1099');
        Schema::dropIfExists('tax_documents');
    }
};
