<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\UpdateBookingStatus::class,
        Commands\AutoClockOut::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('bookings:update-status')->hourly();
        $schedule->command('app:auto-clock-out')->everyMinute();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}