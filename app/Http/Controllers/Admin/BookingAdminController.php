<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Booking;
use App\Models\BookingAssignment;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\HousekeeperBookingSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BookingAdminController
 * 
 * Handles admin operations for booking management including:
 * - Viewing all bookings
 * - Assigning/unassigning caregivers and housekeepers
 * - Managing housekeeper schedules
 * - Booking maintenance mode
 * 
 * @package App\Http\Controllers\Admin
 */
class BookingAdminController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all bookings with related data for admin view.
     */
    public function getAllBookings(Request $request): JsonResponse
    {
        try {
            // Client relationship points to User model (client_id = user.id)
            $query = Booking::with([
                'client' => function($q) {
                    $q->select('id', 'name', 'email', 'phone');
                },
                'assignments.caregiver.user:id,name,email',
                'housekeeperAssignments.housekeeper.user:id,name,email',
                'payments',
                'referralCode.user:id,name,email'
            ]);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('service_type')) {
                $query->where('service_type', $request->service_type);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('service_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('service_date', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('borough', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->input('per_page', 20);
            $bookings = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Use paginatedResponse for proper format that frontend expects
            return $this->paginatedResponse($bookings, 'Bookings retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Failed to get all bookings', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return $this->errorResponse('Failed to retrieve bookings', 500);
        }
    }

    /**
     * Lightweight booking counts for analytics/charts (no full list).
     */
    public function getBookingStats(): JsonResponse
    {
        try {
            $pending = (int) Booking::where('status', 'pending')->count();
            $active = (int) Booking::where('status', 'confirmed')->count();
            $completed = (int) Booking::where('status', 'completed')->count();
            $cancelled = (int) Booking::where('status', 'cancelled')->count();
            $total = $pending + $active + $completed + $cancelled;
            return response()->json([
                'success' => true,
                'pending' => (string) $pending,
                'active' => (string) $active,
                'completed' => (string) $completed,
                'cancelled' => (string) $cancelled,
                'total' => (string) $total,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get booking stats', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'pending' => '0',
                'active' => '0',
                'completed' => '0',
                'cancelled' => '0',
                'total' => '0',
            ], 500);
        }
    }

    /**
     * Assign caregivers to a booking.
     */
    public function assignCaregivers(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'caregiver_ids' => 'required|array|min:1',
            'caregiver_ids.*' => 'exists:caregivers,id'
        ]);

        try {
            DB::beginTransaction();

            $booking = Booking::findOrFail($id);
            
            foreach ($request->caregiver_ids as $caregiverId) {
                // Check if already assigned
                $exists = BookingAssignment::where('booking_id', $id)
                    ->where('caregiver_id', $caregiverId)
                    ->exists();

                if (!$exists) {
                    BookingAssignment::create([
                        'booking_id' => $id,
                        'caregiver_id' => $caregiverId,
                        'assigned_at' => now(),
                        'assigned_by' => Auth::id(),
                        'status' => 'assigned'
                    ]);
                }
            }

            // Update booking status if needed
            if ($booking->status === 'pending') {
                $booking->update(['status' => 'assigned']);
            }

            DB::commit();

            $booking->load('assignments.caregiver.user');

            Log::info('Caregivers assigned to booking', [
                'booking_id' => $id,
                'caregiver_ids' => $request->caregiver_ids,
                'admin_id' => Auth::id()
            ]);

            return $this->successResponse($booking, 'Caregivers assigned successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign caregivers', [
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to assign caregivers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Assign housekeepers to a booking.
     */
    public function assignHousekeepers(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'housekeeper_ids' => 'required|array|min:1',
            'housekeeper_ids.*' => 'exists:housekeepers,id'
        ]);

        try {
            DB::beginTransaction();

            $booking = Booking::findOrFail($id);
            
            foreach ($request->housekeeper_ids as $housekeeperId) {
                // Check if already assigned
                $exists = DB::table('housekeeper_booking_assignments')
                    ->where('booking_id', $id)
                    ->where('housekeeper_id', $housekeeperId)
                    ->exists();

                if (!$exists) {
                    DB::table('housekeeper_booking_assignments')->insert([
                        'booking_id' => $id,
                        'housekeeper_id' => $housekeeperId,
                        'assigned_at' => now(),
                        'assigned_by' => Auth::id(),
                        'status' => 'assigned',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            $booking->load('housekeeperAssignments.housekeeper.user');

            Log::info('Housekeepers assigned to booking', [
                'booking_id' => $id,
                'housekeeper_ids' => $request->housekeeper_ids,
                'admin_id' => Auth::id()
            ]);

            return $this->successResponse($booking, 'Housekeepers assigned successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign housekeepers', [
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to assign housekeepers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Unassign a caregiver from a booking.
     */
    public function unassignCaregiver(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'caregiver_id' => 'required|exists:caregivers,id'
        ]);

        try {
            $deleted = BookingAssignment::where('booking_id', $id)
                ->where('caregiver_id', $request->caregiver_id)
                ->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Assignment not found', 404);
            }

            // Check if any caregivers remain assigned
            $remainingAssignments = BookingAssignment::where('booking_id', $id)->count();
            
            if ($remainingAssignments === 0) {
                Booking::where('id', $id)->update(['status' => 'pending']);
            }

            Log::info('Caregiver unassigned from booking', [
                'booking_id' => $id,
                'caregiver_id' => $request->caregiver_id,
                'admin_id' => Auth::id()
            ]);

            return $this->successResponse(null, 'Caregiver unassigned successfully');

        } catch (\Exception $e) {
            Log::error('Failed to unassign caregiver', [
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to unassign caregiver', 500);
        }
    }

    /**
     * Unassign a housekeeper from a booking.
     */
    public function unassignHousekeeper(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'housekeeper_id' => 'required|exists:housekeepers,id'
        ]);

        try {
            $deleted = DB::table('housekeeper_booking_assignments')
                ->where('booking_id', $id)
                ->where('housekeeper_id', $request->housekeeper_id)
                ->delete();

            if ($deleted === 0) {
                return $this->errorResponse('Assignment not found', 404);
            }

            Log::info('Housekeeper unassigned from booking', [
                'booking_id' => $id,
                'housekeeper_id' => $request->housekeeper_id,
                'admin_id' => Auth::id()
            ]);

            return $this->successResponse(null, 'Housekeeper unassigned successfully');

        } catch (\Exception $e) {
            Log::error('Failed to unassign housekeeper', [
                'booking_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to unassign housekeeper', 500);
        }
    }

    /**
     * Get housekeeper schedule for a booking.
     */
    public function getHousekeeperSchedule(int $id, int $housekeeperId): JsonResponse
    {
        try {
            $schedule = HousekeeperBookingSchedule::where('booking_id', $id)
                ->where('housekeeper_id', $housekeeperId)
                ->first();

            if (!$schedule) {
                // Return empty schedule structure
                return $this->successResponse([
                    'booking_id' => $id,
                    'housekeeper_id' => $housekeeperId,
                    'schedule_days' => [],
                    'notes' => null
                ], 'No schedule found');
            }

            return $this->successResponse($schedule, 'Schedule retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Failed to get housekeeper schedule', [
                'booking_id' => $id,
                'housekeeper_id' => $housekeeperId,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to retrieve schedule', 500);
        }
    }

    /**
     * Update housekeeper schedule for a booking.
     */
    public function updateHousekeeperSchedule(Request $request, int $id, int $housekeeperId): JsonResponse
    {
        $request->validate([
            'schedule_days' => 'required|array',
            'schedule_days.*.day' => 'required|string',
            'schedule_days.*.start_time' => 'required|string',
            'schedule_days.*.end_time' => 'required|string',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $schedule = HousekeeperBookingSchedule::updateOrCreate(
                [
                    'booking_id' => $id,
                    'housekeeper_id' => $housekeeperId
                ],
                [
                    'schedule_days' => json_encode($request->schedule_days),
                    'notes' => $request->notes,
                    'updated_by' => Auth::id()
                ]
            );

            Log::info('Housekeeper schedule updated', [
                'booking_id' => $id,
                'housekeeper_id' => $housekeeperId,
                'admin_id' => Auth::id()
            ]);

            return $this->successResponse($schedule, 'Schedule updated successfully');

        } catch (\Exception $e) {
            Log::error('Failed to update housekeeper schedule', [
                'booking_id' => $id,
                'housekeeper_id' => $housekeeperId,
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse('Failed to update schedule', 500);
        }
    }

    /**
     * Get booking maintenance status.
     */
    public function getBookingMaintenanceStatus(): JsonResponse
    {
        try {
            $isMaintenanceMode = Cache::get('booking_maintenance_mode', false);
            $maintenanceMessage = Cache::get('booking_maintenance_message', 'Booking system is currently under maintenance.');

            // Return flat response for frontend compatibility
            return response()->json([
                'maintenance_enabled' => $isMaintenanceMode,
                'maintenance_message' => $maintenanceMessage
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get maintenance status', ['error' => $e->getMessage()]);
            // Return default (disabled) state on error to prevent blocking bookings
            return response()->json([
                'maintenance_enabled' => false,
                'maintenance_message' => 'Booking system is available.'
            ]);
        }
    }

    /**
     * Toggle booking maintenance mode.
     */
    public function toggleBookingMaintenance(Request $request): JsonResponse
    {
        // Ensure user is authenticated and is admin
        if (!Auth::check()) {
            return $this->errorResponse('Unauthorized', 401);
        }

        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return $this->errorResponse('Forbidden - Admin access required', 403);
        }

        $request->validate([
            'enabled' => 'required|boolean',
            'message' => 'nullable|string|max:500'
        ]);

        try {
            $enabled = $request->boolean('enabled');
            $message = $request->input('message', 'Booking system is currently under maintenance.');

            Cache::put('booking_maintenance_mode', $enabled, now()->addDays(7));
            Cache::put('booking_maintenance_message', $message, now()->addDays(7));

            Log::info('Booking maintenance mode toggled', [
                'enabled' => $enabled,
                'message' => $message,
                'admin_id' => Auth::id()
            ]);

            return $this->successResponse([
                'maintenance_enabled' => $enabled,
                'maintenance_message' => $message
            ], $enabled ? 'Maintenance mode enabled' : 'Maintenance mode disabled');

        } catch (\Exception $e) {
            Log::error('Failed to toggle maintenance mode', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to toggle maintenance mode', 500);
        }
    }
}
