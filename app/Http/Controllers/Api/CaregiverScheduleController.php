<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Controller for caregiver schedule management.
 * Extracted from inline route closures as part of the codebase refactoring.
 *
 * @see AUDIT_COMPLIANCE.md Task 2.2
 */
class CaregiverScheduleController extends Controller
{
    use ApiResponseTrait;

    /**
     * Unassign a caregiver from a booking.
     * 
     * @param int $bookingId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unassignCaregiver(int $bookingId, Request $request)
    {
        try {
            $caregiverId = $request->input('caregiver_id');
            
            $deleted = DB::table('booking_assignments')
                ->where('booking_id', $bookingId)
                ->where('caregiver_id', $caregiverId)
                ->delete();
                
            if ($deleted) {
                return $this->successResponse(
                    null,
                    'Caregiver unassigned successfully'
                );
            } else {
                return $this->errorResponse('Assignment not found', 404);
            }
        } catch (\Exception $e) {
            Log::error('Failed to unassign caregiver: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to unassign caregiver: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get the schedule for a caregiver on a booking.
     * 
     * @param int $bookingId
     * @param int $caregiverId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSchedule(int $bookingId, int $caregiverId)
    {
        try {
            $schedule = DB::table('caregiver_schedules')
                ->where('booking_id', $bookingId)
                ->where('caregiver_id', $caregiverId)
                ->first();
            
            if ($schedule) {
                return $this->successResponse([
                    'schedule' => [
                        'days' => json_decode($schedule->days, true),
                        'schedules' => json_decode($schedule->schedules, true)
                    ]
                ]);
            } else {
                return $this->successResponse(['schedule' => null]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load schedule: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to load schedule: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Update or create a schedule for a caregiver on a booking.
     * 
     * @param int $bookingId
     * @param int $caregiverId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSchedule(int $bookingId, int $caregiverId, Request $request)
    {
        try {
            $days = $request->input('days', []);
            $schedules = $request->input('schedules', []);
            
            // Check if schedule already exists
            $existing = DB::table('caregiver_schedules')
                ->where('booking_id', $bookingId)
                ->where('caregiver_id', $caregiverId)
                ->first();
            
            // If days is empty, delete the record instead of updating
            if (empty($days)) {
                if ($existing) {
                    $deleted = DB::table('caregiver_schedules')
                        ->where('id', $existing->id)
                        ->delete();
                    
                    Log::info("Schedule deleted for booking {$bookingId}, caregiver {$caregiverId}, rows deleted: {$deleted}");
                    
                    return $this->successResponse(
                        ['schedule' => ['days' => [], 'schedules' => (object)[]]],
                        'Schedule cleared successfully'
                    );
                }
                
                return $this->successResponse(
                    ['schedule' => ['days' => [], 'schedules' => (object)[]]],
                    'No schedule to clear'
                );
            }
            
            $data = [
                'booking_id' => $bookingId,
                'caregiver_id' => $caregiverId,
                'days' => json_encode($days),
                'schedules' => json_encode($schedules),
                'updated_at' => now()
            ];
            
            if ($existing) {
                // Update existing schedule
                DB::table('caregiver_schedules')
                    ->where('id', $existing->id)
                    ->update($data);
            } else {
                // Create new schedule
                $data['created_at'] = now();
                DB::table('caregiver_schedules')->insert($data);
            }
            
            return $this->successResponse(
                ['schedule' => ['days' => $days, 'schedules' => $schedules]],
                'Schedule saved successfully'
            );
        } catch (\Exception $e) {
            Log::error('Failed to save schedule: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to save schedule: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Delete a schedule for a caregiver on a booking.
     * 
     * @param int $bookingId
     * @param int $caregiverId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSchedule(int $bookingId, int $caregiverId)
    {
        try {
            $deleted = DB::table('caregiver_schedules')
                ->where('booking_id', $bookingId)
                ->where('caregiver_id', $caregiverId)
                ->delete();
            
            Log::info("Schedule deleted via DELETE endpoint for booking {$bookingId}, caregiver {$caregiverId}, rows deleted: {$deleted}");
            
            return $this->successResponse(
                ['deleted' => $deleted],
                'Schedule deleted successfully'
            );
        } catch (\Exception $e) {
            Log::error('Failed to delete schedule: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to delete schedule: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get the weekly schedule for a caregiver (their own view).
     * This returns all days the caregiver is assigned to work this week.
     * 
     * @param int $caregiverId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyWeeklySchedule(int $caregiverId)
    {
        try {
            // Get all schedules for this caregiver
            $schedules = DB::table('caregiver_schedules')
                ->where('caregiver_id', $caregiverId)
                ->get();
            
            // Get booking details for context
            $bookingIds = $schedules->pluck('booking_id')->unique();
            $bookings = DB::table('bookings')
                ->whereIn('id', $bookingIds)
                ->get()
                ->keyBy('id');
            
            // Get client names
            $clientIds = $bookings->pluck('client_id')->unique();
            $clients = DB::table('users')
                ->whereIn('id', $clientIds)
                ->get(['id', 'name'])
                ->keyBy('id');
            
            // Build weekly schedule
            $weekDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $weeklySchedule = [];
            
            foreach ($weekDays as $dayName) {
                $weeklySchedule[$dayName] = [
                    'day' => ucfirst($dayName),
                    'assigned' => false,
                    'client' => null,
                    'booking_id' => null,
                    'start_time' => null,
                    'end_time' => null
                ];
            }
            
            // Fill in assigned days
            foreach ($schedules as $schedule) {
                $days = json_decode($schedule->days, true) ?: [];
                $times = json_decode($schedule->schedules, true) ?: [];
                $booking = $bookings[$schedule->booking_id] ?? null;
                $client = $booking ? ($clients[$booking->client_id] ?? null) : null;
                
                foreach ($days as $day) {
                    $dayLower = strtolower($day);
                    if (isset($weeklySchedule[$dayLower])) {
                        $dayTime = $times[$day] ?? $times[$dayLower] ?? null;
                        $weeklySchedule[$dayLower] = [
                            'day' => ucfirst($dayLower),
                            'assigned' => true,
                            'client' => $client ? $client->name : 'Unknown Client',
                            'booking_id' => $schedule->booking_id,
                            'start_time' => $dayTime['start_time'] ?? ($booking ? $booking->start_time : '09:00'),
                            'end_time' => $dayTime['end_time'] ?? null
                        ];
                    }
                }
            }
            
            return $this->successResponse([
                'weekly_schedule' => array_values($weeklySchedule)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load weekly schedule: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to load weekly schedule: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Get the weekly schedule for a housekeeper (their own view).
     * 
     * @param int $housekeeperId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHousekeeperWeeklySchedule(int $housekeeperId)
    {
        try {
            // Get all schedules for this housekeeper
            $schedules = DB::table('housekeeper_schedules')
                ->where('housekeeper_id', $housekeeperId)
                ->get();
            
            // If no housekeeper_schedules table or no records, try caregiver_schedules
            if ($schedules->isEmpty()) {
                $schedules = DB::table('caregiver_schedules')
                    ->where('caregiver_id', $housekeeperId)
                    ->get();
            }
            
            // Get booking details for context
            $bookingIds = $schedules->pluck('booking_id')->unique();
            $bookings = DB::table('bookings')
                ->whereIn('id', $bookingIds)
                ->get()
                ->keyBy('id');
            
            // Get client names
            $clientIds = $bookings->pluck('client_id')->unique();
            $clients = DB::table('users')
                ->whereIn('id', $clientIds)
                ->get(['id', 'name'])
                ->keyBy('id');
            
            // Build weekly schedule
            $weekDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $weeklySchedule = [];
            
            foreach ($weekDays as $dayName) {
                $weeklySchedule[$dayName] = [
                    'day' => ucfirst($dayName),
                    'assigned' => false,
                    'client' => null,
                    'booking_id' => null,
                    'start_time' => null,
                    'end_time' => null
                ];
            }
            
            // Fill in assigned days
            foreach ($schedules as $schedule) {
                $days = json_decode($schedule->days, true) ?: [];
                $times = json_decode($schedule->schedules, true) ?: [];
                $booking = $bookings[$schedule->booking_id] ?? null;
                $client = $booking ? ($clients[$booking->client_id] ?? null) : null;
                
                foreach ($days as $day) {
                    $dayLower = strtolower($day);
                    if (isset($weeklySchedule[$dayLower])) {
                        $dayTime = $times[$day] ?? $times[$dayLower] ?? null;
                        $weeklySchedule[$dayLower] = [
                            'day' => ucfirst($dayLower),
                            'assigned' => true,
                            'client' => $client ? $client->name : 'Unknown Client',
                            'booking_id' => $schedule->booking_id,
                            'start_time' => $dayTime['start_time'] ?? ($booking ? $booking->start_time : '09:00'),
                            'end_time' => $dayTime['end_time'] ?? null
                        ];
                    }
                }
            }
            
            return $this->successResponse([
                'weekly_schedule' => array_values($weeklySchedule)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load housekeeper weekly schedule: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to load weekly schedule: ' . $e->getMessage(),
                500
            );
        }
    }
}
