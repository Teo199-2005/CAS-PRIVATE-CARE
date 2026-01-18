<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\ContractorNotificationSetting;
use App\Mail\AssignmentNotificationEmail;
use App\Mail\ShiftReminderEmail;
use App\Mail\WeeklyEarningsSummaryEmail;
use App\Mail\BookingCancellationEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContractorNotificationService
{
    /**
     * Send assignment notification to contractor
     */
    public function sendAssignmentNotification(Booking $booking, User $contractor, array $assignmentDetails = [])
    {
        try {
            // Check if contractor has this notification enabled
            if (!$this->isNotificationEnabled($contractor->id, 'assignment_notifications')) {
                Log::info("Assignment notification disabled for contractor {$contractor->id}");
                return false;
            }

            // Get client info
            $client = $booking->user;

            // Build assignment details
            $details = array_merge([
                'date' => Carbon::parse($booking->service_date)->format('l, F j, Y'),
                'time' => $booking->start_time ? Carbon::parse($booking->start_time)->format('g:i A') : 'TBD',
                'address' => $this->formatAddress($booking),
                'service_type' => $booking->service_type,
                'duration' => $booking->hours_per_day ?? null,
                'client_name' => $client ? $client->name : 'N/A',
                'client_phone' => $client ? $client->phone : null,
                'hourly_rate' => $booking->contractor_rate ?? null,
            ], $assignmentDetails);

            Mail::to($contractor->email)->send(new AssignmentNotificationEmail(
                $booking,
                $contractor,
                $details
            ));

            Log::info("Assignment notification sent to contractor {$contractor->id} for booking {$booking->id}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send assignment notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send shift reminder to contractor
     */
    public function sendShiftReminder(Booking $booking, User $contractor, int $hoursUntilShift = 24)
    {
        try {
            // Check if contractor has this notification enabled
            if (!$this->isNotificationEnabled($contractor->id, 'shift_reminders')) {
                Log::info("Shift reminder disabled for contractor {$contractor->id}");
                return false;
            }

            // Get client info
            $client = $booking->user;

            Mail::to($contractor->email)->send(new ShiftReminderEmail(
                $booking,
                $contractor,
                $client,
                $hoursUntilShift
            ));

            Log::info("Shift reminder sent to contractor {$contractor->id} for booking {$booking->id}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send shift reminder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send booking cancellation notification to contractor
     */
    public function sendCancellationNotification(Booking $booking, User $contractor, string $reason = null)
    {
        try {
            // Check if contractor has this notification enabled
            if (!$this->isNotificationEnabled($contractor->id, 'cancellation_alerts')) {
                Log::info("Cancellation alert disabled for contractor {$contractor->id}");
                return false;
            }

            Mail::to($contractor->email)->send(new BookingCancellationEmail(
                $booking,
                $contractor,
                $reason
            ));

            Log::info("Cancellation notification sent to contractor {$contractor->id} for booking {$booking->id}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send cancellation notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send weekly earnings summary to contractor
     */
    public function sendWeeklyEarningsSummary(User $contractor)
    {
        try {
            // Check if contractor has this notification enabled
            if (!$this->isNotificationEnabled($contractor->id, 'weekly_earnings')) {
                Log::info("Weekly earnings disabled for contractor {$contractor->id}");
                return false;
            }

            $weekStart = Carbon::now()->startOfWeek();
            $weekEnd = Carbon::now()->endOfWeek();

            // Get completed shifts for the week
            $shifts = $this->getContractorShiftsForWeek($contractor, $weekStart, $weekEnd);
            
            if (empty($shifts)) {
                Log::info("No shifts for contractor {$contractor->id} this week - skipping earnings email");
                return false;
            }

            // Calculate totals
            $totalEarnings = collect($shifts)->sum('earnings');
            $totalHours = collect($shifts)->sum('hours');

            // Get pending payouts
            $pendingPayouts = $this->getPendingPayouts($contractor);

            Mail::to($contractor->email)->send(new WeeklyEarningsSummaryEmail(
                $contractor,
                $weekStart,
                $weekEnd,
                $totalEarnings,
                $totalHours,
                $shifts,
                $pendingPayouts
            ));

            Log::info("Weekly earnings summary sent to contractor {$contractor->id}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to send weekly earnings summary: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send shift reminders for upcoming bookings (scheduled task)
     */
    public function sendUpcomingShiftReminders()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        // Find all bookings scheduled for tomorrow with assigned contractors
        $bookings = Booking::where('service_date', $tomorrow)
            ->where('status', 'confirmed')
            ->whereNotNull('assigned_caregiver_id')
            ->get();

        $sentCount = 0;

        foreach ($bookings as $booking) {
            // Get the contractor
            $contractor = User::find($booking->assigned_caregiver_id);
            
            if ($contractor && $contractor->email) {
                if ($this->sendShiftReminder($booking, $contractor, 24)) {
                    $sentCount++;
                }
            }
        }

        // Also check for housekeepers
        $housekeepingBookings = Booking::where('service_date', $tomorrow)
            ->where('status', 'confirmed')
            ->whereNotNull('assigned_housekeeper_id')
            ->get();

        foreach ($housekeepingBookings as $booking) {
            $contractor = User::find($booking->assigned_housekeeper_id);
            
            if ($contractor && $contractor->email) {
                if ($this->sendShiftReminder($booking, $contractor, 24)) {
                    $sentCount++;
                }
            }
        }

        Log::info("Sent {$sentCount} shift reminders for {$tomorrow}");
        return $sentCount;
    }

    /**
     * Send weekly earnings summaries to all contractors (scheduled task)
     */
    public function sendAllWeeklyEarningsSummaries()
    {
        $contractors = User::whereIn('role', ['caregiver', 'housekeeper'])->get();
        $sentCount = 0;

        foreach ($contractors as $contractor) {
            if ($this->sendWeeklyEarningsSummary($contractor)) {
                $sentCount++;
            }
        }

        Log::info("Sent {$sentCount} weekly earnings summaries");
        return $sentCount;
    }

    /**
     * Check if a notification type is enabled for a contractor
     */
    private function isNotificationEnabled(int $userId, string $notificationType): bool
    {
        $settings = ContractorNotificationSetting::where('user_id', $userId)->first();

        // If no settings exist, default to enabled
        if (!$settings) {
            return true;
        }

        return (bool) $settings->$notificationType;
    }

    /**
     * Get or create notification settings for a contractor
     */
    public function getNotificationSettings(int $userId)
    {
        return ContractorNotificationSetting::firstOrCreate(
            ['user_id' => $userId],
            [
                'assignment_notifications' => true,
                'shift_reminders' => true,
                'cancellation_alerts' => true,
                'weekly_earnings' => true,
                'payout_notifications' => true,
            ]
        );
    }

    /**
     * Update notification settings for a contractor
     */
    public function updateNotificationSettings(int $userId, array $settings)
    {
        return ContractorNotificationSetting::updateOrCreate(
            ['user_id' => $userId],
            $settings
        );
    }

    /**
     * Format booking address
     */
    private function formatAddress(Booking $booking): string
    {
        $parts = array_filter([
            $booking->address,
            $booking->city,
            $booking->state,
            $booking->zip_code
        ]);

        return implode(', ', $parts) ?: 'Address not provided';
    }

    /**
     * Get contractor shifts for a given week
     */
    private function getContractorShiftsForWeek(User $contractor, Carbon $weekStart, Carbon $weekEnd): array
    {
        $shifts = [];

        // Get bookings where contractor was assigned
        $bookings = Booking::where(function ($query) use ($contractor) {
            $query->where('assigned_caregiver_id', $contractor->id)
                  ->orWhere('assigned_housekeeper_id', $contractor->id);
        })
        ->whereBetween('service_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
        ->whereIn('status', ['completed', 'confirmed'])
        ->get();

        foreach ($bookings as $booking) {
            $hours = $booking->hours_per_day ?? 0;
            $rate = $booking->contractor_rate ?? 0;

            $shifts[] = [
                'date' => $booking->service_date,
                'hours' => $hours,
                'earnings' => $hours * $rate,
                'service_type' => $booking->service_type,
            ];
        }

        return $shifts;
    }

    /**
     * Get pending payouts for contractor
     */
    private function getPendingPayouts(User $contractor): float
    {
        // Check if there's a scheduled_payouts table
        try {
            return \DB::table('scheduled_payouts')
                ->where('contractor_id', $contractor->id)
                ->where('status', 'pending')
                ->sum('amount');
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}
