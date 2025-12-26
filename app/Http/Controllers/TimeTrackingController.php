<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeTracking;
use App\Models\Caregiver;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimeTrackingController extends Controller
{
    public function clockIn(Request $request)
    {
        $request->validate([
            'caregiver_id' => 'required|exists:caregivers,id',
            'client_id' => 'nullable|exists:clients,id',
            'location' => 'nullable|string'
        ]);

        // Check if caregiver is already clocked in
        $activeSession = TimeTracking::where('caregiver_id', $request->caregiver_id)
            ->where('status', 'active')
            ->whereNull('clock_out_time')
            ->first();

        if ($activeSession) {
            return response()->json(['error' => 'Already clocked in'], 400);
        }

        // If no client_id provided, try to get it from active assignment
        $clientId = $request->client_id;
        if (!$clientId) {
            $assignment = \App\Models\BookingAssignment::with('booking')
                ->where('caregiver_id', $request->caregiver_id)
                ->where('status', 'assigned')
                ->whereHas('booking', function($query) {
                    $today = now()->toDateString();
                    $query->where('status', 'approved')
                          ->whereRaw('service_date <= ?', [$today])
                          ->whereRaw('DATE_ADD(service_date, INTERVAL duration_days DAY) >= ?', [$today]);
                })
                ->first();
            
            if ($assignment && $assignment->booking) {
                // booking.client_id is a user_id, we need to find the clients.id for that user
                $clientRecord = Client::where('user_id', $assignment->booking->client_id)->first();
                if ($clientRecord) {
                    $clientId = $clientRecord->id;
                }
            }
        }

        $timeTracking = TimeTracking::create([
            'caregiver_id' => $request->caregiver_id,
            'client_id' => $clientId,
            'clock_in_time' => now(),
            'location' => $request->location ?? 'Client Home',
            'work_date' => now()->toDateString(),
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully',
            'data' => $timeTracking
        ]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'caregiver_id' => 'required|exists:caregivers,id'
        ]);

        $activeSession = TimeTracking::where('caregiver_id', $request->caregiver_id)
            ->where('status', 'active')
            ->whereNull('clock_out_time')
            ->first();

        if (!$activeSession) {
            return response()->json(['error' => 'No active session found'], 400);
        }

        $activeSession->update([
            'clock_out_time' => now(),
            'status' => 'completed'
        ]);

        $activeSession->calculateHours();

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully',
            'data' => $activeSession->fresh()
        ]);
    }

    public function getCurrentSession($caregiverId)
    {
        $activeSession = TimeTracking::where('caregiver_id', $caregiverId)
            ->where('status', 'active')
            ->whereNull('clock_out_time')
            ->with(['client.user'])
            ->first();

        if (!$activeSession) {
            return response()->json(['active_session' => null]);
        }

        return response()->json([
            'active_session' => [
                'id' => $activeSession->id,
                'clock_in_time' => $activeSession->clock_in_time,
                'location' => $activeSession->location,
                'client_name' => $activeSession->client ? $activeSession->client->user->name : 'N/A',
                'current_duration' => $activeSession->getCurrentDuration()
            ]
        ]);
    }

    public function getTodaySummary($caregiverId)
    {
        $today = Carbon::now()->toDateString();
        
        // Get all sessions for today
        $todaySessions = TimeTracking::where('caregiver_id', $caregiverId)
            ->where('work_date', $today)
            ->orderBy('clock_in_time', 'desc')
            ->get();
            
        if ($todaySessions->isEmpty()) {
            return response()->json([
                'total_hours' => 0,
                'sessions_count' => 0,
                'last_session' => null
            ]);
        }
        
        $totalHours = $todaySessions->sum('hours_worked');
        $lastSession = $todaySessions->first();
        
        return response()->json([
            'total_hours' => $totalHours,
            'sessions_count' => $todaySessions->count(),
            'last_session' => [
                'clock_in' => $lastSession->clock_in_time->format('g:i A'),
                'clock_out' => $lastSession->clock_out_time ? $lastSession->clock_out_time->format('g:i A') : null,
                'hours_worked' => $lastSession->hours_worked,
                'status' => $lastSession->status
            ]
        ]);
    }

    public function getWeeklyHistory($caregiverId)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $sessions = TimeTracking::where('caregiver_id', $caregiverId)
            ->whereBetween('work_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->orderBy('work_date', 'desc')
            ->get();

        $weeklyData = [];
        $totalHours = 0;

        foreach ($sessions as $session) {
            $date = Carbon::parse($session->work_date);
            $dayName = $date->format('D');
            $dateFormatted = $date->format('M j');
            
            if (!isset($weeklyData[$session->work_date])) {
                $weeklyData[$session->work_date] = [
                    'day' => $dayName,
                    'date' => $dateFormatted,
                    'sessions' => [],
                    'total_hours' => 0
                ];
            }

            $hours = $session->hours_worked ?? $session->getCurrentDuration();
            $weeklyData[$session->work_date]['sessions'][] = [
                'clock_in' => $session->clock_in_time ? $session->clock_in_time->format('g:i A') : null,
                'clock_out' => $session->clock_out_time ? $session->clock_out_time->format('g:i A') : 'In Progress',
                'hours' => $hours
            ];
            $weeklyData[$session->work_date]['total_hours'] += $hours;
            $totalHours += $hours;
        }

        return response()->json([
            'weekly_data' => array_values($weeklyData),
            'total_hours' => $totalHours,
            'target_hours' => 40
        ]);
    }

    public function getAdminTimeTracking()
    {
        $today = Carbon::now()->toDateString();
        
        // Get caregivers with ongoing contracts (bookings that cover today)
        $ongoingBookings = DB::table('bookings as b')
            ->join('booking_assignments as ba', 'b.id', '=', 'ba.booking_id')
            ->join('caregivers as c', 'ba.caregiver_id', '=', 'c.id')
            ->join('users as u', 'c.user_id', '=', 'u.id')
            ->join('users as client_user', 'b.client_id', '=', 'client_user.id')
            ->whereRaw('DATE_ADD(b.service_date, INTERVAL b.duration_days DAY) >= ?', [$today])
            ->where('b.service_date', '<=', $today)
            ->whereIn('b.status', ['approved', 'confirmed'])
            ->select('ba.caregiver_id', 'u.name as caregiver_name', 'client_user.id as client_id', 'client_user.name as client_name')
            ->distinct()
            ->get();

        $trackingData = [];

        foreach ($ongoingBookings as $booking) {
            // Get today's time tracking for this caregiver (get the latest one)
            $todaySession = TimeTracking::with(['caregiver.user', 'client.user'])
                ->where('caregiver_id', $booking->caregiver_id)
                ->where('work_date', $today)
                ->orderBy('clock_in_time', 'desc')
                ->first();

            $weeklyHours = $this->getWeeklyHours($booking->caregiver_id);
            
            // Get client name from the time tracking record if available, otherwise from booking
            $clientName = $booking->client_name;
            if ($todaySession && $todaySession->client && $todaySession->client->user) {
                $clientName = $todaySession->client->user->name;
            }
            
            if ($todaySession) {
                $trackingData[] = [
                    'caregiver_id' => $booking->caregiver_id,
                    'caregiver_name' => $booking->caregiver_name,
                    'client_id' => $booking->client_id,
                    'status' => $todaySession->status === 'active' ? 'Clocked In' : 'Clocked Out',
                    'clock_in' => $todaySession->clock_in_time ? $todaySession->clock_in_time->format('g:i A') : null,
                    'clock_out' => $todaySession->clock_out_time ? $todaySession->clock_out_time->format('g:i A') : null,
                    'hours_today' => $todaySession->hours_worked ?? $todaySession->getCurrentDuration(),
                    'weekly_hours' => $weeklyHours,
                    'client_name' => $clientName
                ];
            } else {
                // Caregiver has ongoing contract but no time tracking today
                $trackingData[] = [
                    'caregiver_id' => $booking->caregiver_id,
                    'caregiver_name' => $booking->caregiver_name,
                    'client_id' => $booking->client_id,
                    'status' => 'Not Clocked In',
                    'clock_in' => null,
                    'clock_out' => null,
                    'hours_today' => 0,
                    'weekly_hours' => $weeklyHours,
                    'client_name' => $clientName
                ];
            }
        }

        return response()->json($trackingData);
    }

    private function getWeeklyHours($caregiverId)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return TimeTracking::where('caregiver_id', $caregiverId)
            ->whereBetween('work_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
            ->sum('hours_worked') ?? 0;
    }
}