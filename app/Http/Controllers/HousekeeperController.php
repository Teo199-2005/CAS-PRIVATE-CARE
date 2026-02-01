<?php

namespace App\Http\Controllers;

use App\Models\Housekeeper;
use App\Models\BookingHousekeeperAssignment;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HousekeeperController extends Controller
{
    use ApiResponseTrait;
    /**
     * Get housekeeper stats for dashboard
     */
    public function stats($housekeeperId): JsonResponse
    {
        $housekeeper = Housekeeper::find($housekeeperId);
        
        if (!$housekeeper) {
            return response()->json(['error' => 'Housekeeper not found'], 404);
        }
        
        $today = now()->toDateString();
        
        // Get active assignments for this housekeeper from the housekeeper-specific assignments table
        $activeAssignments = BookingHousekeeperAssignment::with(['booking.client', 'housekeeper'])
            ->where('housekeeper_id', $housekeeperId)
            ->where('status', 'assigned')
            ->whereHas('booking', function($query) use ($today) {
                $query->whereIn('status', ['approved', 'confirmed'])
                      ->where(function($q) use ($today) {
                          // Active: service has started and hasn't ended yet
                          $q->where(function($subQ) use ($today) {
                              $subQ->where('service_date', '<=', $today)
                                   ->whereRaw('DATE_ADD(service_date, INTERVAL duration_days DAY) >= ?', [$today]);
                          })
                          // OR Upcoming: service starts in the future
                          ->orWhere('service_date', '>', $today);
                      });
            })
            ->get();
        
        // Sort: prioritize active assignments (started) over upcoming ones, then by service date
        $activeAssignments = $activeAssignments->sortBy(function($assignment) use ($today) {
            $serviceDate = $assignment->booking->service_date;
            // Prioritize active assignments (started) over upcoming ones
            $priority = $serviceDate <= $today ? 0 : 1;
            return $priority . '_' . $serviceDate;
        })->values();
        
        // Calculate actual earnings from TimeTracking records
        $timeTrackings = \App\Models\TimeTracking::where('housekeeper_id', $housekeeper->id)
            ->whereNotNull('clock_out_time')
            ->where('status', 'completed')
            ->get();
        
        $totalEarnings = $timeTrackings->sum('caregiver_earnings') ?? 0; // Using caregiver_earnings field for housekeepers too
        $monthlyEarnings = $timeTrackings->where('work_date', '>=', now()->startOfMonth())->sum('caregiver_earnings') ?? 0;
        $weeklyEarnings = $timeTrackings->where('work_date', '>=', now()->startOfWeek())->sum('caregiver_earnings') ?? 0;
        $pendingBalance = $timeTrackings->whereNull('paid_at')->sum('caregiver_earnings') ?? 0;
        
        // Get recent transactions from TimeTracking
        $transactions = \App\Models\TimeTracking::where('housekeeper_id', $housekeeper->id)
            ->whereNotNull('clock_out_time')
            ->orderBy('work_date', 'desc')
            ->take(10)
            ->get()
            ->map(function($tt) {
                return [
                    'id' => $tt->id,
                    'type' => $tt->paid_at ? 'payment' : 'pending',
                    'description' => 'Earnings for ' . ($tt->work_date ? $tt->work_date->format('M d, Y') : 'Unknown date'),
                    'amount' => $tt->caregiver_earnings ?? 0,
                    'status' => $tt->paid_at ? 'completed' : 'pending',
                    'method' => 'Direct Deposit',
                    'created_at' => $tt->created_at ? $tt->created_at->toDateTimeString() : now()->toDateTimeString()
                ];
            })->toArray();
        
        return response()->json([
            'total_clients' => $activeAssignments->count(),
            'total_earnings' => $totalEarnings,
            'monthly_earnings' => $monthlyEarnings,
            'weekly_earnings' => $weeklyEarnings,
            'pending_balance' => $pendingBalance,
            'rating' => $housekeeper->rating ?? 4.9,
            'active_assignments' => $activeAssignments->map(function($assignment) {
                $booking = $assignment->booking;
                $serviceDate = $booking->service_date;
                $durationDays = $booking->duration_days ?? 1;
                $startTime = $booking->start_time;
                
                // Calculate end date
                $endDate = null;
                if ($serviceDate && $durationDays) {
                    $endDate = \Carbon\Carbon::parse($serviceDate)->addDays($durationDays)->format('Y-m-d');
                }
                
                // Format start_time if it's a Carbon instance
                $formattedStartTime = null;
                if ($startTime) {
                    if ($startTime instanceof \Carbon\Carbon) {
                        $formattedStartTime = $startTime->format('H:i:s');
                    } else {
                        $formattedStartTime = is_string($startTime) ? $startTime : (string)$startTime;
                    }
                }
                
                return [
                    'booking' => [
                        'client' => [
                            'name' => $booking->client->name ?? 'Unknown Client'
                        ],
                        'service_type' => $booking->service_type,
                        'service_date' => $serviceDate,
                        'start_time' => $formattedStartTime,
                        'duration_days' => $durationDays,
                        'end_date' => $endDate,
                        'day_schedules' => $booking->day_schedules,
                        'status' => $booking->status
                    ]
                ];
            })->toArray(),
            'transactions' => $transactions
        ]);
    }

    /**
     * Get available clients (bookings) for housekeepers
     */
    public function getAvailableClients(): JsonResponse
    {
        // Get housekeeping bookings that need assignment
        $availableBookings = \App\Models\Booking::where('service_type', 'Housekeeping')
            ->whereDoesntHave('assignments', function($query) {
                $query->where('provider_type', 'housekeeper');
            })
            ->where('status', 'pending')
            ->with('client')
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'name' => $booking->client->name,
                    'serviceType' => $booking->service_type,
                    'location' => $booking->borough ?? 'New York',
                    'hourlyRate' => $booking->hourly_rate ?? 25,
                    'date' => $booking->service_date,
                    'duration' => $booking->duration_days,
                    'initials' => strtoupper(substr($booking->client->name, 0, 1) . substr(explode(' ', $booking->client->name)[1] ?? 'X', 0, 1)),
                    'avatarColor' => 'success'
                ];
            });
        
        // Return array directly as frontend expects array not object
        return response()->json($availableBookings);
    }

    /**
     * Apply for a client booking
     */
    public function applyForClient($bookingId): JsonResponse
    {
        $user = auth()->user();
        $housekeeper = Housekeeper::where('user_id', $user->id)->first();
        
        if (!$housekeeper) {
            return response()->json(['error' => 'Housekeeper profile not found'], 404);
        }
        
        // Check if booking exists
        $booking = \App\Models\Booking::find($bookingId);
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }
        
        // Create housekeeper assignment using the correct model
        $assignment = \App\Models\BookingHousekeeperAssignment::create([
            'booking_id' => $bookingId,
            'housekeeper_id' => $housekeeper->id,
            'status' => 'pending',
            'assigned_at' => now(),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully'
        ]);
    }

    /**
     * Get housekeeper earnings report
     */
    public function getEarningsReport($housekeeperId): JsonResponse
    {
        $housekeeper = Housekeeper::find($housekeeperId);
        
        if (!$housekeeper) {
            return response()->json(['error' => 'Housekeeper not found'], 404);
        }
        
        // Get time trackings for earnings calculation
        $timeTrackings = \App\Models\TimeTracking::where('housekeeper_id', $housekeeperId)
            ->where('provider_type', 'housekeeper')
            ->orderBy('work_date', 'desc')
            ->get();
        
        $totalEarnings = $timeTrackings->sum('caregiver_earnings'); // Will use same field name
        
        return response()->json([
            'total_earnings' => $totalEarnings,
            'time_trackings' => $timeTrackings
        ]);
    }
}
