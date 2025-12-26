<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('insurance_provider')->nullable()->after('emergency_contact_relationship');
            $table->string('insurance_policy_number')->nullable()->after('insurance_provider');
            $table->json('allergies')->nullable()->after('medical_conditions');
            $table->json('medications')->nullable()->after('allergies');
            $table->string('preferred_language')->default('english')->after('medications');
            $table->string('account_type')->default('basic')->after('preferred_language');
            $table->boolean('two_factor_enabled')->default(false)->after('account_type');
            $table->json('notification_preferences')->nullable()->after('two_factor_enabled');
            $table->string('timezone')->default('America/New_York')->after('notification_preferences');
            $table->text('bio')->nullable()->after('timezone');
            $table->string('profile_photo')->nullable()->after('bio');
            $table->json('preferred_caregivers')->nullable()->after('profile_photo');
            $table->json('blocked_caregivers')->nullable()->after('preferred_caregivers');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'insurance_provider', 'insurance_policy_number', 'allergies', 'medications',
                'preferred_language', 'account_type', 'two_factor_enabled', 
                'notification_preferences', 'timezone', 'bio', 'profile_photo',
                'preferred_caregivers', 'blocked_caregivers'
            ]);
        });
    }
};