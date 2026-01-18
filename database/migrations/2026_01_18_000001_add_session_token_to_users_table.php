<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds session token for single session enforcement on admin accounts
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'session_token')) {
                $table->string('session_token', 64)->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'session_started_at')) {
                $table->timestamp('session_started_at')->nullable()->after('session_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'session_token')) {
                $table->dropColumn('session_token');
            }
            if (Schema::hasColumn('users', 'session_started_at')) {
                $table->dropColumn('session_started_at');
            }
        });
    }
};
