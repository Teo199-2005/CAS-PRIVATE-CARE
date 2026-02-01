<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create CSP Reports Table
 * 
 * Stores Content Security Policy violation reports for security monitoring.
 * This helps identify XSS attempts and CSP misconfigurations.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('csp_reports', function (Blueprint $table) {
            $table->id();
            $table->string('document_uri', 2048)->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->string('violated_directive', 255)->nullable()->index();
            $table->string('effective_directive', 255)->nullable();
            $table->text('original_policy')->nullable();
            $table->string('blocked_uri', 2048)->nullable();
            $table->string('source_file', 2048)->nullable();
            $table->integer('line_number')->nullable();
            $table->integer('column_number')->nullable();
            $table->integer('status_code')->nullable();
            $table->text('script_sample')->nullable();
            $table->string('disposition', 50)->default('enforce');
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            
            // Index for cleanup queries
            $table->index(['created_at', 'violated_directive']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csp_reports');
    }
};
