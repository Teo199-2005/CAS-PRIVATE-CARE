<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations - Add performance indexes
     */
    public function up(): void
    {
        // Bookings table indexes for common queries
        Schema::table('bookings', function (Blueprint $table) {
            // Composite index for client bookings filtered by status
            if (!$this->indexExists('bookings', 'idx_bookings_client_status_date')) {
                $table->index(['client_id', 'status', 'service_date'], 'idx_bookings_client_status_date');
            }
            
            // Index for payment status queries (only if column exists)
            if (Schema::hasColumn('bookings', 'payment_status') && !$this->indexExists('bookings', 'idx_bookings_payment_status')) {
                $table->index(['payment_status', 'created_at'], 'idx_bookings_payment_status');
            }
            
            // Index for Stripe payment tracking (only if column exists)
            if (Schema::hasColumn('bookings', 'stripe_payment_intent_id') && !$this->indexExists('bookings', 'idx_bookings_stripe_payment')) {
                $table->index('stripe_payment_intent_id', 'idx_bookings_stripe_payment');
            }
        });

        // Time trackings table indexes
        Schema::table('time_trackings', function (Blueprint $table) {
            // Composite index for caregiver earnings calculation (only if payment_status column exists)
            if (Schema::hasColumn('time_trackings', 'payment_status') && !$this->indexExists('time_trackings', 'idx_time_tracking_caregiver_pay')) {
                $table->index(['caregiver_id', 'payment_status', 'created_at'], 'idx_time_tracking_caregiver_pay');
            }
            
            // Index for caregiver time tracking queries
            if (!$this->indexExists('time_trackings', 'idx_time_tracking_caregiver')) {
                $table->index(['caregiver_id', 'clock_in_time'], 'idx_time_tracking_caregiver');
            }
            
            // Index for work date queries
            if (!$this->indexExists('time_trackings', 'idx_time_tracking_work_date')) {
                $table->index(['work_date', 'status'], 'idx_time_tracking_work_date');
            }
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            // Index for client payment history
            if (!$this->indexExists('payments', 'idx_payments_client_status')) {
                $table->index(['client_id', 'status', 'paid_at'], 'idx_payments_client_status');
            }
            
            // Index for transaction lookups
            if (!$this->indexExists('payments', 'idx_payments_transaction')) {
                $table->index('transaction_id', 'idx_payments_transaction');
            }
            
            // Index for booking payments
            if (!$this->indexExists('payments', 'idx_payments_booking')) {
                $table->index(['booking_id', 'status'], 'idx_payments_booking');
            }
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Index for filtering by user type and status
            if (!$this->indexExists('users', 'idx_users_type_status')) {
                $table->index(['user_type', 'status'], 'idx_users_type_status');
            }
            
            // Index for Stripe customer lookups
            if (!$this->indexExists('users', 'idx_users_stripe_customer')) {
                $table->index('stripe_customer_id', 'idx_users_stripe_customer');
            }
            
            // Index for email verification
            if (!$this->indexExists('users', 'idx_users_email_verified')) {
                $table->index('email_verified_at', 'idx_users_email_verified');
            }
        });

        // Caregivers table indexes
        Schema::table('caregivers', function (Blueprint $table) {
            // Index for available caregivers
            if (!$this->indexExists('caregivers', 'idx_caregivers_availability')) {
                $table->index('availability_status', 'idx_caregivers_availability');
            }
        });

        // Booking assignments table indexes
        Schema::table('booking_assignments', function (Blueprint $table) {
            // Composite index for caregiver assignments
            if (!$this->indexExists('booking_assignments', 'idx_assignments_caregiver_status')) {
                $table->index(['caregiver_id', 'status'], 'idx_assignments_caregiver_status');
            }
        });

        // Notifications table indexes
        Schema::table('notifications', function (Blueprint $table) {
            // Index for user notifications
            if (!$this->indexExists('notifications', 'idx_notifications_user_read')) {
                $table->index(['user_id', 'read', 'created_at'], 'idx_notifications_user_read');
            }
            
            // Index for notification type queries
            if (!$this->indexExists('notifications', 'idx_notifications_type')) {
                $table->index(['type', 'priority'], 'idx_notifications_type');
            }
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_client_status_date');
            $table->dropIndex('idx_bookings_payment_status');
            $table->dropIndex('idx_bookings_stripe_payment');
        });

        Schema::table('time_trackings', function (Blueprint $table) {
            $table->dropIndex('idx_time_tracking_caregiver_pay');
            $table->dropIndex('idx_time_tracking_caregiver');
            $table->dropIndex('idx_time_tracking_work_date');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_client_status');
            $table->dropIndex('idx_payments_transaction');
            $table->dropIndex('idx_payments_booking');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_type_status');
            $table->dropIndex('idx_users_stripe_customer');
            $table->dropIndex('idx_users_email_verified');
        });

        Schema::table('caregivers', function (Blueprint $table) {
            $table->dropIndex('idx_caregivers_availability');
        });

        Schema::table('booking_assignments', function (Blueprint $table) {
            $table->dropIndex('idx_assignments_caregiver_status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_type');
        });
    }

    /**
     * Check if index exists (Laravel 12 compatible)
     */
    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $schemaManager = $connection->getSchemaBuilder();
        
        // Get all indexes for the table
        $indexes = $connection->select(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            [$index]
        );

        return !empty($indexes);
    }
};
