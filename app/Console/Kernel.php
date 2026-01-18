<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\UpdateBookingStatus::class,
        Commands\AutoClockOut::class,
        Commands\ProcessRecurringBookings::class,
        Commands\SendRecurringReminderEmails::class,
        Commands\ProcessScheduledPayouts::class,
        Commands\Generate1099Forms::class,
        Commands\RunComplianceChecks::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('bookings:update-status')->hourly();
        $schedule->command('app:auto-clock-out')->everyMinute();
        
        // Process recurring bookings daily at 1:00 AM
        // This creates new bookings and charges clients automatically
        $schedule->command('bookings:process-recurring')
            ->dailyAt('01:00')
            ->appendOutputTo(storage_path('logs/recurring-bookings.log'));
        
        // Send recurring reminder emails daily at 9:00 AM
        // Sends reminders at 5, 4, 3, 2, and 1 days before renewal
        $schedule->command('bookings:send-recurring-reminders')
            ->dailyAt('09:00')
            ->appendOutputTo(storage_path('logs/recurring-reminders.log'));
        
        // Process scheduled payouts - runs daily at 6:00 AM
        // Checks if today is a payout day and processes accordingly
        $schedule->command('payouts:process')
            ->dailyAt('06:00')
            ->appendOutputTo(storage_path('logs/scheduled-payouts.log'));
        
        // Run compliance checks weekly on Mondays at 2:00 AM
        $schedule->command('compliance:check')
            ->weeklyOn(1, '02:00')
            ->appendOutputTo(storage_path('logs/compliance-checks.log'));
        
        // Generate 1099 forms annually on January 15th at 3:00 AM
        // Only runs if it's January 15th
        $schedule->command('tax:generate-1099')
            ->yearlyOn(1, 15, '03:00')
            ->appendOutputTo(storage_path('logs/1099-generation.log'));
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}