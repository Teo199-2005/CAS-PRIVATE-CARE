<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Email Campaigns table - stores campaign templates and settings
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Campaign name for admin reference
            $table->string('subject'); // Email subject line
            $table->text('content'); // HTML content of the email
            $table->enum('type', [
                'promotional', // Special offers/discounts
                'reengagement', // "We miss you" emails
                'seasonal', // Holiday/seasonal campaigns
                'announcement', // General announcements
                'reminder', // Booking reminders
                'custom' // Custom admin emails
            ])->default('custom');
            $table->enum('target_audience', [
                'all_clients', // All clients
                'never_booked', // Clients who haven't booked
                'inactive', // Clients inactive for X days
                'active', // Active clients with bookings
                'selected' // Manually selected clients
            ])->default('all_clients');
            $table->integer('inactive_days')->nullable(); // For 'inactive' target
            $table->json('selected_client_ids')->nullable(); // For 'selected' target
            $table->enum('status', ['draft', 'scheduled', 'sent', 'cancelled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('emails_sent')->default(0);
            $table->integer('emails_opened')->default(0);
            $table->integer('emails_clicked')->default(0);
            $table->integer('emails_failed')->default(0);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Email Logs table - tracks individual email sends
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('user_id'); // Recipient
            $table->string('email_type'); // campaign, assignment, shift_reminder, etc.
            $table->string('subject');
            $table->string('status')->default('pending'); // pending, sent, opened, clicked, failed, bounced
            $table->string('tracking_id')->unique()->nullable(); // For open/click tracking
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional data (booking_id, etc.)
            $table->timestamps();

            $table->foreign('campaign_id')->references('id')->on('email_campaigns')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['email_type', 'status']);
            $table->index('tracking_id');
        });

        // Contractor notifications preferences
        Schema::create('contractor_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('new_assignment_email')->default(true);
            $table->boolean('shift_reminder_email')->default(true);
            $table->boolean('schedule_change_email')->default(true);
            $table->boolean('cancellation_email')->default(true);
            $table->boolean('weekly_summary_email')->default(true);
            $table->boolean('payment_email')->default(true);
            $table->integer('shift_reminder_hours')->default(24); // Hours before shift
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Email templates table - reusable templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->text('content');
            $table->enum('category', [
                'marketing',
                'transactional',
                'notification',
                'reminder'
            ])->default('marketing');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('contractor_notification_settings');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_campaigns');
    }
};
