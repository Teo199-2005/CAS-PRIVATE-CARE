<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * This migration is now a no-op. The date_of_birth column stays as date type
 * since we removed the encrypted:date cast in favor of a plain date cast.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: date_of_birth stays as date column (no longer encrypted)
    }

    public function down(): void
    {
        // No-op
    }
};
