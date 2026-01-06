<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TimeTracking;
use App\Models\Booking;
use App\Models\BookingAssignment;
use Carbon\Carbon;

class AutoClockOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-clock-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out caregivers at their scheduled shift end time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $today = strtolower($now->format('l')); // monday, tuesday, etc.
        $currentTime = $now->format('H:i'); // 24-hour format HH:MM
        
        $this->info("Running auto clock-out at {$now->toDateTimeString()}");
        $this->info("Today is: {$today}, Current time: {$currentTime}");
        
        // Find all active bookings with day_schedules
        $bookings = Booking::where('status', 'approved')
            ->whereNotNull('day_schedules')
            ->with(['assignments' => function ($query) {
                $query->where('status', 'assigned')->where('is_active', true);
            }])
            ->get();
            
        $clockedOutCount = 0;
        
        foreach ($bookings as $booking) {
            // Check if this booking has schedule for today
            if (!isset($booking->day_schedules[$today])) {
                continue;
            }
            
            $daySchedule = $booking->day_schedules[$today];
            // Parse start and end time from schedule like "11:00 AM - 11:00 PM"
            if (preg_match('/(\d+:\d+\s*[AP]M)\s*-\s*(\d+:\d+\s*[AP]M)/', $daySchedule, $matches)) {
                $startTimeStr = $matches[1]; // e.g., "11:00 AM"
                $endTimeStr = $matches[2]; // e.g., "11:00 PM"
                
                $scheduledStart = Carbon::createFromFormat('g:i A', $startTimeStr);
                $scheduledEnd = Carbon::createFromFormat('g:i A', $endTimeStr);
                
                // Calculate scheduled hours for this shift
                $scheduledHours = $scheduledStart->diffInMinutes($scheduledEnd) / 60;
                if ($scheduledHours < 0) {
                    $scheduledHours += 24; // Handle next day scenarios
                }
                
                // Find all assigned caregivers for this booking
                foreach ($booking->assignments as $assignment) {
                    // Find time tracking entry that's still clocked in for today
                    $timeTracking = TimeTracking::where('caregiver_id', $assignment->caregiver_id)
                        ->where('booking_id', $booking->id)
                        ->whereDate('work_date', $now->toDateString())
                        ->whereNotNull('clock_in_time')
                        ->whereNull('clock_out_time')
                        ->first();
                        
                    if ($timeTracking) {
                        $clockInTime = Carbon::parse($timeTracking->clock_in_time);
                        
                        // Calculate when they should be clocked out (clock_in + scheduled hours)
                        $expectedClockOut = $clockInTime->copy()->addHours($scheduledHours);
                        
                        // Check if current time is past or equal to expected clock out time
                        if ($now->gte($expectedClockOut)) {
                            // Auto clock out at clock_in + scheduled hours
                            $timeTracking->clock_out_time = $expectedClockOut;
                            $timeTracking->hours_worked = $scheduledHours;
                            
                            // Calculate earnings based on scheduled hours (they get paid full shift)
                            $hourlyRate = $booking->hourly_rate ?? 0;
                            $timeTracking->caregiver_earnings = $scheduledHours * $hourlyRate;
                            $timeTracking->total_client_charge = $scheduledHours * $hourlyRate;
                            
                            $timeTracking->save();
                            
                            $clockedOutCount++;
                            $this->info("✓ Auto clocked out caregiver #{$assignment->caregiver_id} for booking #{$booking->id}");
                            $this->info("  Clocked in: {$clockInTime->format('g:i A')} → Clocked out: {$expectedClockOut->format('g:i A')} ({$scheduledHours} hours)");
                        }
                    }
                }
            }
        }
        
        if ($clockedOutCount > 0) {
            $this->info("Successfully auto-clocked out {$clockedOutCount} caregiver(s)");
        } else {
            $this->info("No caregivers needed to be auto-clocked out at this time");
        }
        
        return 0;
    }
}
