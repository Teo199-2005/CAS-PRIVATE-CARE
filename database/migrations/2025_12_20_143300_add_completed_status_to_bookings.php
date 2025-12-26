<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change status column to varchar temporarily
        DB::statement("ALTER TABLE bookings MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
        
        // Now change it to enum with completed status
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};