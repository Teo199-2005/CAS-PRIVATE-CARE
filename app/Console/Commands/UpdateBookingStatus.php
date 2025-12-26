<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateBookingStatus extends Command
{
    protected $signature = 'bookings:update-status';
    protected $description = 'Update booking status to completed when service period has ended';

    public function handle()
    {
        $bookings = Booking::whereIn('status', ['approved', 'confirmed'])
            ->get();

        $updated = 0;
        foreach ($bookings as $booking) {
            $endDate = Carbon::parse($booking->service_date)->addDays($booking->duration_days);
            
            if ($endDate->isPast()) {
                $booking->update(['status' => 'completed']);
                $updated++;
            }
        }

        $this->info("Updated {$updated} bookings to completed status.");
        return 0;
    }
}