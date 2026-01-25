<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the account_deletions table for GDPR compliance.
     * This table maintains an audit trail of deleted accounts
     * without storing personally identifiable information.
     */
    public function up(): void
    {
        Schema::create('account_deletions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_user_id')->comment('Original user ID for reference');
            $table->string('original_email_hash')->comment('SHA256 hash of original email for verification');
            $table->string('user_type')->nullable()->comment('Type of user account');
            $table->text('deletion_reason')->nullable()->comment('Optional reason provided by user');
            $table->timestamp('deleted_at')->comment('When the account was deleted');
            $table->timestamps();
            
            $table->index('original_user_id');
            $table->index('deleted_at');
        });

        // Add deleted_at column to users table if it doesn't exist
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable()->after('updated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_deletions');
        
        if (Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }
};
