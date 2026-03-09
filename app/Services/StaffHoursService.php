<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\BookingHousekeeperAssignment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Computes scheduled hours per week for caregivers and housekeepers.
 * Used to enforce max 40 hours/week and to validate assignments.
 */
class StaffHoursService
{
    public function getMaxHoursPerWeek(): int
    {
        return config('scheduling.max_hours_per_week', 40);
    }

    public function getMaxHoursPerShift(): int
    {
        return config('scheduling.max_hours_per_shift', 12);
    }

    /**
     * Get week key (Monday date Y-m-d) for a given date.
     */
    public function weekKey($date): string
    {
        $carbon = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);
        return $carbon->startOfWeek()->format('Y-m-d');
    }

    /**
     * Extract hours per day from booking duty_type (e.g. "8 Hours", "12 Hours").
     */
    public function extractHoursFromDutyType(?string $dutyType): int
    {
        if (!$dutyType) {
            return 8;
        }
        if (preg_match('/(\d+)\s*Hours?/i', $dutyType, $matches)) {
            return (int) $matches[1];
        }
        return 8;
    }

    /**
     * Get scheduled hours per week for a caregiver in a date range.
     * Returns array keyed by week start (Y-m-d) => hours in that week.
     *
     * @param int $caregiverId
     * @param string $startDate Y-m-d
     * @param string $endDate Y-m-d
     * @param int|null $excludeBookingId Exclude this booking (e.g. when checking before update)
     * @return array<string, float>
     */
    public function getCaregiverHoursPerWeek(int $caregiverId, string $startDate, string $endDate, ?int $excludeBookingId = null): array
    {
        $query = BookingAssignment::query()
            ->where('caregiver_id', $caregiverId)
            ->where('status', '!=', 'cancelled')
            ->with(['booking' => function ($q) {
                $q->select('id', 'duty_type', 'day_schedules', 'service_date', 'duration_days', 'hours_per_day');
            }]);

        if ($excludeBookingId) {
            $query->where('booking_id', '!=', $excludeBookingId);
        }

        $assignments = $query->get();
        $weekHours = [];

        foreach ($assignments as $assignment) {
            $booking = $assignment->booking;
            if (!$booking) {
                continue;
            }
            if ($assignment->start_date && $assignment->end_date) {
                $assignStart = Carbon::parse($assignment->start_date);
                $assignEnd = Carbon::parse($assignment->end_date);
            } else {
                $assignStart = Carbon::parse($booking->service_date);
                $assignEnd = Carbon::parse($booking->service_date)->addDays(($booking->duration_days ?? 15) - 1);
            }
            $rangeStart = Carbon::parse($startDate);
            $rangeEnd = Carbon::parse($endDate);
            $effectiveStart = $assignStart->max($rangeStart);
            $effectiveEnd = $assignEnd->min($rangeEnd);
            if ($effectiveStart->gt($effectiveEnd)) {
                continue;
            }
            $hoursPerDay = $booking->hours_per_day
                ? (int) $booking->hours_per_day
                : $this->extractHoursFromDutyType($booking->duty_type);
            $daySchedules = is_array($booking->day_schedules) ? $booking->day_schedules : null;

            $date = $effectiveStart->copy();
            while ($date->lte($effectiveEnd)) {
                $dayOfWeek = strtolower($date->format('l'));
                $dayHours = $hoursPerDay;
                if ($daySchedules && isset($daySchedules[$dayOfWeek])) {
                    $dayHours = $this->parseDayScheduleHours($daySchedules[$dayOfWeek]);
                }
                $dayHours = min($dayHours, $this->getMaxHoursPerShift());
                $key = $this->weekKey($date);
                $weekHours[$key] = ($weekHours[$key] ?? 0) + $dayHours;
                $date->addDay();
            }
        }

        return $weekHours;
    }

    /**
     * Parse day_schedules value to hours (e.g. "11:00 AM - 11:00 PM" or array with start/end).
     */
    public function parseDayScheduleHours($schedule): float
    {
        if (is_numeric($schedule)) {
            return (float) $schedule;
        }
        if (is_array($schedule)) {
            $start = $schedule['start'] ?? $schedule['start_time'] ?? null;
            $end = $schedule['end'] ?? $schedule['end_time'] ?? null;
            if ($start && $end) {
                $startCarbon = Carbon::parse($start);
                $endCarbon = Carbon::parse($end);
                $hours = $startCarbon->diffInMinutes($endCarbon) / 60;
                if ($hours < 0) {
                    $hours += 24;
                }
                return (float) min($hours, $this->getMaxHoursPerShift());
            }
            return 0;
        }
        $str = (string) $schedule;
        if (preg_match('/(\d{1,2}:\d{2}\s*[AP]M)\s*-\s*(\d{1,2}:\d{2}\s*[AP]M)/i', $str, $m)) {
            $start = Carbon::createFromFormat('g:i A', trim($m[1]));
            $end = Carbon::createFromFormat('g:i A', trim($m[2]));
            if ($end->lt($start)) {
                $end->addDay();
            }
            $hours = $start->diffInMinutes($end) / 60;
            return (float) min($hours, $this->getMaxHoursPerShift());
        }
        return 8;
    }

    /**
     * Get scheduled hours per week for a housekeeper in a date range.
     * Uses housekeeper_schedules when present; otherwise falls back to booking hours_per_day × days.
     *
     * @param int $housekeeperId
     * @param string $startDate Y-m-d
     * @param string $endDate Y-m-d
     * @param int|null $excludeBookingId
     * @return array<string, float>
     */
    public function getHousekeeperHoursPerWeek(int $housekeeperId, string $startDate, string $endDate, ?int $excludeBookingId = null): array
    {
        $assignments = BookingHousekeeperAssignment::query()
            ->where('housekeeper_id', $housekeeperId)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->when($excludeBookingId, fn ($q) => $q->where('booking_id', '!=', $excludeBookingId))
            ->with(['booking' => function ($q) {
                $q->select('id', 'service_date', 'duration_days', 'hours_per_day', 'duty_type');
            }])
            ->get();

        $weekHours = [];
        $rangeStart = Carbon::parse($startDate);
        $rangeEnd = Carbon::parse($endDate);

        foreach ($assignments as $assignment) {
            $booking = $assignment->booking;
            if (!$booking) {
                continue;
            }
            $assignStart = $assignment->start_date
                ? Carbon::parse($assignment->start_date)
                : Carbon::parse($booking->service_date);
            $assignEnd = $assignment->end_date
                ? Carbon::parse($assignment->end_date)
                : Carbon::parse($booking->service_date)->addDays(($booking->duration_days ?? 15) - 1);
            $effectiveStart = $assignStart->max($rangeStart);
            $effectiveEnd = $assignEnd->min($rangeEnd);
            if ($effectiveStart->gt($effectiveEnd)) {
                continue;
            }

            $hoursPerDay = (int) ($booking->hours_per_day ?? $this->extractHoursFromDutyType($booking->duty_type ?? '8 Hours'));
            $hoursPerDay = min($hoursPerDay, $this->getMaxHoursPerShift());

            $row = null;
            if (\Illuminate\Support\Facades\Schema::hasTable('housekeeper_schedules')) {
                $row = DB::table('housekeeper_schedules')
                    ->where('booking_id', $assignment->booking_id)
                    ->where('housekeeper_id', $housekeeperId)
                    ->first();
            }

            $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $date = $effectiveStart->copy();
            while ($date->lte($effectiveEnd)) {
                $dayOfWeek = strtolower($date->format('l'));
                $dayHours = $hoursPerDay;
                if ($row && $row->days && $row->schedules) {
                    $days = json_decode($row->days, true);
                    $schedules = json_decode($row->schedules, true);
                    if (is_array($days) && in_array($dayOfWeek, $days, true) && is_array($schedules) && isset($schedules[$dayOfWeek])) {
                        $slot = $schedules[$dayOfWeek];
                        $start = $slot['start_time'] ?? '08:00';
                        $end = $slot['end_time'] ?? '17:00';
                        $startCarbon = Carbon::parse($start);
                        $endCarbon = Carbon::parse($end);
                        if ($endCarbon->format('H:i') <= $startCarbon->format('H:i')) {
                            $endCarbon->addDay();
                        }
                        $dayHours = min($startCarbon->diffInMinutes($endCarbon) / 60, $this->getMaxHoursPerShift());
                    }
                }
                $key = $this->weekKey($date);
                $weekHours[$key] = ($weekHours[$key] ?? 0) + $dayHours;
                $date->addDay();
            }
        }

        return $weekHours;
    }

    /**
     * Compute hours per week that a new caregiver assignment would add for the given booking.
     * Uses assignment start_date/end_date when provided (AdminController style), else booking service_date + duration.
     *
     * @return array<string, float> week key => additional hours
     */
    public function getNewCaregiverAssignmentHoursPerWeek(Booking $booking, string $assignStartDate, string $assignEndDate): array
    {
        $hoursPerDay = (int) ($booking->hours_per_day ?? $this->extractHoursFromDutyType($booking->duty_type));
        $hoursPerDay = min($hoursPerDay, $this->getMaxHoursPerShift());
        $daySchedules = is_array($booking->day_schedules) ? $booking->day_schedules : null;

        $weekHours = [];
        $start = Carbon::parse($assignStartDate);
        $end = Carbon::parse($assignEndDate);
        $date = $start->copy();
        while ($date->lte($end)) {
            $dayOfWeek = strtolower($date->format('l'));
            $dayHours = $hoursPerDay;
            if ($daySchedules && isset($daySchedules[$dayOfWeek])) {
                $dayHours = $this->parseDayScheduleHours($daySchedules[$dayOfWeek]);
            }
            $key = $this->weekKey($date);
            $weekHours[$key] = ($weekHours[$key] ?? 0) + $dayHours;
            $date->addDay();
        }
        return $weekHours;
    }

    /**
     * Compute hours per week that a new housekeeper assignment would add (estimate using booking defaults).
     */
    public function getNewHousekeeperAssignmentHoursPerWeek(Booking $booking, string $assignStartDate, string $assignEndDate): array
    {
        $hoursPerDay = (int) ($booking->hours_per_day ?? $this->extractHoursFromDutyType($booking->duty_type ?? '8 Hours'));
        $hoursPerDay = min($hoursPerDay, $this->getMaxHoursPerShift());
        $weekHours = [];
        $start = Carbon::parse($assignStartDate);
        $end = Carbon::parse($assignEndDate);
        $date = $start->copy();
        while ($date->lte($end)) {
            $key = $this->weekKey($date);
            $weekHours[$key] = ($weekHours[$key] ?? 0) + $hoursPerDay;
            $date->addDay();
        }
        return $weekHours;
    }

    /**
     * Check if adding the given weekly hours would exceed max for any week.
     * Returns first violation message or null.
     */
    public function checkCaregiverWeeklyLimit(int $caregiverId, string $bookingStartDate, string $bookingEndDate, array $newHoursPerWeek, ?int $excludeBookingId = null): ?string
    {
        $existing = $this->getCaregiverHoursPerWeek($caregiverId, $bookingStartDate, $bookingEndDate, $excludeBookingId);
        $max = $this->getMaxHoursPerWeek();
        foreach ($newHoursPerWeek as $weekKey => $newHours) {
            $current = $existing[$weekKey] ?? 0;
            $total = $current + $newHours;
            if ($total > $max) {
                $weekStart = Carbon::parse($weekKey);
                return "Caregiver would exceed 40 hours in the week of {$weekStart->format('M j')}–" . $weekStart->copy()->endOfWeek()->format('M j, Y') . " ({$total} hours).";
            }
        }
        return null;
    }

    /**
     * Check if adding the given weekly hours would exceed max for any week (housekeeper).
     */
    public function checkHousekeeperWeeklyLimit(int $housekeeperId, string $bookingStartDate, string $bookingEndDate, array $newHoursPerWeek, ?int $excludeBookingId = null): ?string
    {
        $existing = $this->getHousekeeperHoursPerWeek($housekeeperId, $bookingStartDate, $bookingEndDate, $excludeBookingId);
        $max = $this->getMaxHoursPerWeek();
        foreach ($newHoursPerWeek as $weekKey => $newHours) {
            $current = $existing[$weekKey] ?? 0;
            $total = $current + $newHours;
            if ($total > $max) {
                $weekStart = Carbon::parse($weekKey);
                return "Housekeeper would exceed 40 hours in the week of {$weekStart->format('M j')}–" . $weekStart->copy()->endOfWeek()->format('M j, Y') . " ({$total} hours).";
            }
        }
        return null;
    }

    /**
     * Compute hours per week from a schedule_days array (e.g. from updateHousekeeperSchedule).
     * Each entry: ['day' => 'monday', 'start_time' => '08:00', 'end_time' => '17:00'].
     * Used to validate 40 hrs/week when saving housekeeper schedule.
     *
     * @return array<string, float>
     */
    public function getHousekeeperHoursPerWeekFromScheduleDays(string $bookingStartDate, string $bookingEndDate, array $scheduleDays): array
    {
        $weekHours = [];
        $start = Carbon::parse($bookingStartDate);
        $end = Carbon::parse($bookingEndDate);
        $dayToSlot = [];
        foreach ($scheduleDays as $entry) {
            $day = isset($entry['day']) ? strtolower((string) $entry['day']) : null;
            if ($day === null) {
                continue;
            }
            $st = $entry['start_time'] ?? '08:00';
            $et = $entry['end_time'] ?? '17:00';
            $s = Carbon::parse($st);
            $e = Carbon::parse($et);
            if ($e->format('H:i') <= $s->format('H:i')) {
                $e->addDay();
            }
            $dayToSlot[$day] = $s->diffInMinutes($e) / 60;
        }
        $date = $start->copy();
        while ($date->lte($end)) {
            $dayOfWeek = strtolower($date->format('l'));
            $dayHours = $dayToSlot[$dayOfWeek] ?? 0;
            $dayHours = min($dayHours, $this->getMaxHoursPerShift());
            $key = $this->weekKey($date);
            $weekHours[$key] = ($weekHours[$key] ?? 0) + $dayHours;
            $date->addDay();
        }
        return $weekHours;
    }
}
