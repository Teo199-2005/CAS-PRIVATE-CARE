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
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}