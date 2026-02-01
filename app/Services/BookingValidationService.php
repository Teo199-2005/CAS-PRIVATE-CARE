<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Caregiver;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Service for validating booking requests.
 * 
 * Handles all business logic validation for bookings including:
 * - Caregiver availability
 * - Scheduling conflicts
 * - Service type validation
 * - Booking time restrictions
 * - Client eligibility
 */
class BookingValidationService
{
    /**
     * Minimum hours in advance a booking can be made
     */
    protected int $minAdvanceHours = 24;

    /**
     * Maximum days in advance a booking can be made
     */
    protected int $maxAdvanceDays = 90;

    /**
     * Minimum booking duration in hours
     */
    protected int $minDuration = 2;

    /**
     * Maximum booking duration in hours
     */
    protected int $maxDuration = 12;

    /**
     * Available service types
     */
    protected array $serviceTypes = [
        'caregiving',
        'companionship', 
        'personal_care',
        'nanny',
        'babysitting',
        'housekeeping',
        'deep_cleaning',
        'meal_prep',
    ];

    /**
     * Validate a complete booking request.
     * 
     * @throws ValidationException
     */
    public function validate(array $data, ?User $client = null): array
    {
        $errors = [];

        // Validate caregiver exists and is available
        $caregiverErrors = $this->validateCaregiver($data['caregiver_id'] ?? null);
        if ($caregiverErrors) {
            $errors = array_merge($errors, $caregiverErrors);
        }

        // Validate service type
        $serviceErrors = $this->validateServiceType($data['service_type'] ?? null);
        if ($serviceErrors) {
            $errors = array_merge($errors, $serviceErrors);
        }

        // Validate scheduling
        $scheduleErrors = $this->validateSchedule(
            $data['scheduled_date'] ?? null,
            $data['scheduled_time'] ?? null,
            $data['duration_hours'] ?? null,
            $data['caregiver_id'] ?? null
        );
        if ($scheduleErrors) {
            $errors = array_merge($errors, $scheduleErrors);
        }

        // Validate address
        $addressErrors = $this->validateAddress($data);
        if ($addressErrors) {
            $errors = array_merge($errors, $addressErrors);
        }

        // Validate client eligibility
        if ($client) {
            $clientErrors = $this->validateClient($client);
            if ($clientErrors) {
                $errors = array_merge($errors, $clientErrors);
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $this->prepareValidatedData($data);
    }

    /**
     * Validate caregiver availability.
     */
    public function validateCaregiver(?int $caregiverId): ?array
    {
        if (!$caregiverId) {
            return ['caregiver_id' => ['Caregiver selection is required.']];
        }

        $caregiver = Caregiver::find($caregiverId);

        if (!$caregiver) {
            return ['caregiver_id' => ['Selected caregiver not found.']];
        }

        if ($caregiver->status !== 'active') {
            return ['caregiver_id' => ['Selected caregiver is not currently available.']];
        }

        if (!$caregiver->is_verified) {
            return ['caregiver_id' => ['Selected caregiver has not completed verification.']];
        }

        return null;
    }

    /**
     * Validate service type.
     */
    public function validateServiceType(?string $serviceType): ?array
    {
        if (!$serviceType) {
            return ['service_type' => ['Service type is required.']];
        }

        if (!in_array($serviceType, $this->serviceTypes, true)) {
            return ['service_type' => ['Invalid service type selected.']];
        }

        return null;
    }

    /**
     * Validate scheduling details.
     */
    public function validateSchedule(
        ?string $date,
        ?string $time,
        ?int $duration,
        ?int $caregiverId
    ): ?array {
        $errors = [];

        // Validate date
        if (!$date) {
            $errors['scheduled_date'] = ['Booking date is required.'];
        } else {
            try {
                $scheduledDate = Carbon::parse($date);
                $now = Carbon::now();

                // Check minimum advance time
                $hoursUntilBooking = $now->diffInHours($scheduledDate, false);
                if ($hoursUntilBooking < $this->minAdvanceHours) {
                    $errors['scheduled_date'] = [
                        "Bookings must be made at least {$this->minAdvanceHours} hours in advance."
                    ];
                }

                // Check maximum advance time
                $daysUntilBooking = $now->diffInDays($scheduledDate, false);
                if ($daysUntilBooking > $this->maxAdvanceDays) {
                    $errors['scheduled_date'] = [
                        "Bookings cannot be made more than {$this->maxAdvanceDays} days in advance."
                    ];
                }
            } catch (\Exception $e) {
                $errors['scheduled_date'] = ['Invalid date format.'];
            }
        }

        // Validate time
        if (!$time) {
            $errors['scheduled_time'] = ['Booking time is required.'];
        } elseif (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time)) {
            $errors['scheduled_time'] = ['Invalid time format. Use HH:MM.'];
        }

        // Validate duration
        if (!$duration) {
            $errors['duration_hours'] = ['Duration is required.'];
        } elseif ($duration < $this->minDuration) {
            $errors['duration_hours'] = [
                "Minimum booking duration is {$this->minDuration} hours."
            ];
        } elseif ($duration > $this->maxDuration) {
            $errors['duration_hours'] = [
                "Maximum booking duration is {$this->maxDuration} hours."
            ];
        }

        // Check for scheduling conflicts
        if (empty($errors) && $caregiverId && $date && $time && $duration) {
            $conflictError = $this->checkScheduleConflict(
                $caregiverId,
                $date,
                $time,
                $duration
            );
            if ($conflictError) {
                $errors['scheduled_date'] = [$conflictError];
            }
        }

        return empty($errors) ? null : $errors;
    }

    /**
     * Check for scheduling conflicts with existing bookings.
     */
    protected function checkScheduleConflict(
        int $caregiverId,
        string $date,
        string $time,
        int $duration
    ): ?string {
        $requestedStart = Carbon::parse("{$date} {$time}");
        $requestedEnd = $requestedStart->copy()->addHours($duration);

        // Check for overlapping bookings
        $conflict = Booking::where('caregiver_id', $caregiverId)
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->whereDate('scheduled_date', $date)
            ->get()
            ->first(function ($booking) use ($requestedStart, $requestedEnd) {
                $bookingStart = Carbon::parse($booking->scheduled_date . ' ' . $booking->scheduled_time);
                $bookingEnd = $bookingStart->copy()->addHours($booking->duration_hours);

                // Check for overlap
                return $requestedStart < $bookingEnd && $requestedEnd > $bookingStart;
            });

        if ($conflict) {
            return 'The caregiver is not available at the selected time. Please choose a different time slot.';
        }

        return null;
    }

    /**
     * Validate address information.
     */
    public function validateAddress(array $data): ?array
    {
        $errors = [];

        if (empty($data['address'])) {
            $errors['address'] = ['Street address is required.'];
        } elseif (strlen($data['address']) < 5) {
            $errors['address'] = ['Please provide a complete street address.'];
        }

        if (empty($data['city'])) {
            $errors['city'] = ['City is required.'];
        }

        if (empty($data['state'])) {
            $errors['state'] = ['State is required.'];
        } elseif (strlen($data['state']) !== 2) {
            $errors['state'] = ['Please use the 2-letter state code.'];
        }

        if (empty($data['zip'])) {
            $errors['zip'] = ['ZIP code is required.'];
        } elseif (!preg_match('/^\d{5}(-\d{4})?$/', $data['zip'])) {
            $errors['zip'] = ['Please enter a valid ZIP code.'];
        }

        return empty($errors) ? null : $errors;
    }

    /**
     * Validate client eligibility for booking.
     */
    public function validateClient(User $client): ?array
    {
        $errors = [];

        // Check if client is verified
        if (!$client->email_verified_at) {
            $errors['client'] = ['Please verify your email address before making bookings.'];
        }

        // Check if client has active status
        if ($client->status !== 'active') {
            $errors['client'] = ['Your account is not active. Please contact support.'];
        }

        // Check for outstanding unpaid bookings
        $unpaidCount = Booking::where('client_id', $client->id)
            ->where('payment_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '<', now()->subDays(7))
            ->count();

        if ($unpaidCount >= 2) {
            $errors['payment'] = [
                'You have outstanding unpaid bookings. Please complete payment before making new bookings.'
            ];
        }

        return empty($errors) ? null : $errors;
    }

    /**
     * Prepare validated data for booking creation.
     */
    protected function prepareValidatedData(array $data): array
    {
        return [
            'caregiver_id' => (int) $data['caregiver_id'],
            'service_type' => $data['service_type'],
            'scheduled_date' => Carbon::parse($data['scheduled_date'])->toDateString(),
            'scheduled_time' => $data['scheduled_time'],
            'duration_hours' => (int) $data['duration_hours'],
            'address' => trim($data['address']),
            'city' => trim($data['city']),
            'state' => strtoupper(trim($data['state'])),
            'zip' => trim($data['zip']),
            'notes' => isset($data['notes']) ? trim($data['notes']) : null,
            'special_instructions' => isset($data['special_instructions']) 
                ? trim($data['special_instructions']) 
                : null,
        ];
    }

    /**
     * Get available time slots for a caregiver on a specific date.
     */
    public function getAvailableTimeSlots(int $caregiverId, string $date): array
    {
        $cacheKey = "caregiver:{$caregiverId}:slots:{$date}";

        return Cache::remember($cacheKey, 300, function () use ($caregiverId, $date) {
            $existingBookings = Booking::where('caregiver_id', $caregiverId)
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->whereDate('scheduled_date', $date)
                ->get(['scheduled_time', 'duration_hours']);

            $blockedSlots = [];
            foreach ($existingBookings as $booking) {
                $start = Carbon::parse($booking->scheduled_time);
                $end = $start->copy()->addHours($booking->duration_hours);

                while ($start < $end) {
                    $blockedSlots[] = $start->format('H:i');
                    $start->addMinutes(30);
                }
            }

            // Generate all possible time slots (8 AM to 8 PM)
            $allSlots = [];
            $slotTime = Carbon::parse('08:00');
            $endTime = Carbon::parse('20:00');

            while ($slotTime < $endTime) {
                $slot = $slotTime->format('H:i');
                $allSlots[] = [
                    'time' => $slot,
                    'available' => !in_array($slot, $blockedSlots, true),
                ];
                $slotTime->addMinutes(30);
            }

            return $allSlots;
        });
    }

    /**
     * Calculate the total cost for a booking.
     */
    public function calculateCost(int $caregiverId, string $serviceType, int $durationHours): array
    {
        $caregiver = Caregiver::findOrFail($caregiverId);

        $hourlyRate = $caregiver->hourly_rate ?? 25.00;
        $subtotal = $hourlyRate * $durationHours;

        // Apply service type adjustments
        $serviceMultiplier = match ($serviceType) {
            'personal_care' => 1.2,
            'deep_cleaning' => 1.3,
            'nanny' => 1.1,
            default => 1.0,
        };

        $adjustedSubtotal = $subtotal * $serviceMultiplier;
        $platformFee = $adjustedSubtotal * 0.15; // 15% platform fee
        $total = $adjustedSubtotal + $platformFee;

        return [
            'hourly_rate' => round($hourlyRate, 2),
            'duration_hours' => $durationHours,
            'subtotal' => round($adjustedSubtotal, 2),
            'platform_fee' => round($platformFee, 2),
            'total' => round($total, 2),
            'caregiver_payout' => round($adjustedSubtotal, 2),
        ];
    }
}
