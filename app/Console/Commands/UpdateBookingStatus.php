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
        // Only update bookings that are approved/confirmed, NOT pending
        // Pending bookings should NOT auto-complete until approved
        $bookings = Booking::whereIn('status', ['approved', 'confirmed'])
            ->whereNotNull('service_date')
            ->whereNotNull('duration_days')
            ->get();

        $updated = 0;
        foreach ($bookings as $booking) {
            try {
                $serviceDate = Carbon::parse($booking->service_date);
                $endDate = $serviceDate->copy()->addDays($booking->duration_days);
                
                // Only mark as completed if the service period has ended
                if ($endDate->isPast()) {
                    $booking->update(['status' => 'completed']);
                    $updated++;
                    
                    $this->info("Completed booking #{$booking->id} (ended on {$endDate->toDateString()})");
                }
            } catch (\Exception $e) {
                $this->error("Failed to process booking #{$booking->id}: " . $e->getMessage());
            }
        }

        $this->info("Updated {$updated} booking(s) to completed status.");
        return 0;
    }
}