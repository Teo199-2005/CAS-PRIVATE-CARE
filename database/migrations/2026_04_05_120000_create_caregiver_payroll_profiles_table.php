<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caregiver_payroll_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caregiver_id')->unique()->constrained('caregivers')->cascadeOnDelete();
            $table->string('legal_first_name')->nullable();
            $table->string('legal_middle_name')->nullable();
            $table->string('legal_last_name')->nullable();
            $table->text('ssn_encrypted')->nullable();
            $table->string('ssn_last_four', 4)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('region', 64)->nullable();
            $table->string('postal_code', 32)->nullable();
            $table->string('country', 2)->default('US');
            $table->string('payroll_email')->nullable();
            $table->string('payroll_phone', 32)->nullable();
            $table->text('bank_routing_number_encrypted')->nullable();
            $table->text('bank_account_number_encrypted')->nullable();
            $table->string('bank_account_type', 16)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 32)->nullable();
            $table->string('emergency_contact_relationship', 64)->nullable();
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamp('verified_by_admin_at')->nullable();
            $table->unsignedBigInteger('verified_by_admin_user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caregiver_payroll_profiles');
    }
};
