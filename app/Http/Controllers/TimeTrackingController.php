<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimeTracking;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimeTrackingController extends Controller
{
    public function clockIn(Request $request)
    {
        // Validate - allow either caregiver_id OR housekeeper_id
        $request->validate([
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'housekeeper_id' => 'nullable|exists:housekeepers,id',
            'client_id' => 'nullable|exists:clients,id',
            'location' => 'nullable|string'
        ]);

        // Require at least one provider ID
        if (!$request->caregiver_id && !$request->housekeeper_id) {
            return response()->json(['error' => 'Either caregiver_id or housekeeper_id is required'], 400);
        }

        // Determine provider type
        $providerType = $request->housekeeper_id ? 'housekeeper' : 'caregiver';
        $providerId = $request->housekeeper_id ?? $request->caregiver_id;

        // Check if provider is already clocked in
        $activeSessionQuery = TimeTracking::where('status', 'active')
            ->whereNull('clock_out_time');
        
        if ($providerType === 'housekeeper') {
            $activeSessionQuery->where('housekeeper_id', $providerId);
        } else {
            $activeSessionQuery->where('caregiver_id', $providerId);
        }
        
        $activeSession = $activeSessionQuery->first();

        if ($activeSession) {
            return response()->json(['error' => 'Already clocked in'], 400);
        }

        // If no client_id provided, try to get it from active assignment
        $clientId = $request->client_id;
        if (!$clientId) {
            if ($providerType === 'housekeeper') {
                // Check housekeeper assignment
                $assignment = \App\Models\BookingHousekeeperAssignment::with('booking')
                    ->where('housekeeper_id', $providerId)
                    ->where('status', 'assigned')
                    ->whereHas('booking', function($query) {
                        $today = now()->toDateString();
                        $query->where('status', 'approved')
                              ->whereRaw('service_date <= ?', [$today])
                              ->whereRaw('DATE_ADD(service_date, INTERVAL duration_days DAY) >= ?', [$today]);
                    })
                    ->first();
            } else {
                // Check caregiver assignment
                $assignment = \App\Models\BookingAssignment::with('booking')
                    ->where('caregiver_id', $providerId)
                    ->where('status', 'assigned')
                    ->whereHas('booking', function($query) {
                        $today = now()->toDateString();
                        $query->where('status', 'approved')
                              ->whereRaw('service_date <= ?', [$today])
                              ->whereRaw('DATE_ADD(service_date, INTERVAL duration_days DAY) >= ?', [$today]);
                    })
                    ->first();
            }
            
            if ($assignment && $assignment->booking) {
                // booking.client_id is a user_id, we need to find the clients.id for that user
                $clientRecord = Client::where('user_id', $assignment->booking->client_id)->first();
                if ($clientRecord) {
                    $clientId = $clientRecord->id;
                }
            }
        }

        $timeTracking = TimeTracking::create([
            'caregiver_id' => $providerType === 'caregiver' ? $providerId : null,
            'housekeeper_id' => $providerType === 'housekeeper' ? $providerId : null,
            'provider_type' => $providerType,
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
        // Validate - allow either caregiver_id OR housekeeper_id
        $request->validate([
            'caregiver_id' => 'nullable|exists:caregivers,id',
            'housekeeper_id' => 'nullable|exists:housekeepers,id'
        ]);

        // Require at least one provider ID
        if (!$request->caregiver_id && !$request->housekeeper_id) {
            return response()->json(['error' => 'Either caregiver_id or housekeeper_id is required'], 400);
        }

        // Determine provider type
        $providerType = $request->housekeeper_id ? 'housekeeper' : 'caregiver';
        $providerId = $request->housekeeper_id ?? $request->caregiver_id;

        // Find active session for this provider
        $activeSessionQuery = TimeTracking::where('status', 'active')
            ->whereNull('clock_out_time');
        
        if ($providerType === 'housekeeper') {
            $activeSessionQuery->where('housekeeper_id', $providerId);
        } else {
            $activeSessionQuery->where('caregiver_id', $providerId);
        }
        
        $activeSession = $activeSessionQuery->first();

        if (!$activeSession) {
            return response()->json(['error' => 'No active session found'], 400);
        }

        $activeSession->update([
            'clock_out_time' => now(),
            'status' => 'completed'
        ]);

        // Calculate hours worked
        $activeSession->calculateHours();
        
        // Calculate earnings and commissions based on hourly rate
        $this->calculateEarnings($activeSession);

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully',
            'data' => $activeSession->fresh()
        ]);
    }

    /**
     * Calculate earnings and commissions for a time tracking entry
     * Based on actual hours worked × hourly rates
     * Supports both caregivers and housekeepers
     * Uses PricingService for consistent rate calculations
     */
    private function calculateEarnings(TimeTracking $timeTracking)
    {
        $hoursWorked = $timeTracking->hours_worked ?? 0;
        
        if ($hoursWorked <= 0) {
            return;
        }

        // Determine if this is a housekeeper or caregiver
        $isHousekeeper = !empty($timeTracking->housekeeper_id);
        
        $provider = null;
        $assignment = null;
        $booking = null;
        $hasReferral = false;

        if ($isHousekeeper) {
            // Get housekeeper info
            $provider = \App\Models\Housekeeper::with(['user'])->find($timeTracking->housekeeper_id);
            if (!$provider) {
                return;
            }

            // Get active booking assignment for this housekeeper
            $assignment = \App\Models\BookingHousekeeperAssignment::with('booking.referralCode')
                ->where('housekeeper_id', $timeTracking->housekeeper_id)
                ->where('status', 'assigned')
                ->first();
            
            $booking = $assignment ? $assignment->booking : null;
            
            // Check if booking has referral code
            $hasReferral = $booking && $booking->referral_code_id && $booking->referralCode;
            
            // Use assigned rate (admin sets this when assigning housekeeper)
            // Fallback to default rate if not set
            $providerRate = $assignment->assigned_hourly_rate ?? \App\Services\PricingService::getHousekeeperDefaultRate();
            $clientChargeRate = \App\Services\PricingService::getHousekeeperClientRate($hasReferral);
            $marketingRate = \App\Services\PricingService::HOUSEKEEPER_MARKETING_RATE;
            
            // Calculate provider earnings
            $providerEarnings = $hoursWorked * $providerRate;
            
            // Marketing commission (if referral used)
            $marketingPartnerId = null;
            $marketingCommission = 0;
            
            if ($hasReferral) {
                $referralCode = $booking->referralCode;
                if ($referralCode && $referralCode->user_id) {
                    $marketingPartnerId = $referralCode->user_id;
                    $marketingCommission = $hoursWorked * $marketingRate;
                }
            }
            
            // No training centers for housekeepers
            $trainingCenterId = null;
            $trainingCommission = 0;
            
            // Calculate total client charge
            $totalClientCharge = $hoursWorked * $clientChargeRate;
            
            // Calculate agency commission (remainder)
            $agencyCommission = $totalClientCharge - $providerEarnings - $marketingCommission;
            
        } else {
            // Get caregiver info
            $provider = \App\Models\Caregiver::with(['user', 'trainingCenter'])->find($timeTracking->caregiver_id);
            if (!$provider) {
                return;
            }

            // Get active booking assignment for this caregiver
            $assignment = \App\Models\BookingAssignment::with('booking.referralCode')
                ->where('caregiver_id', $timeTracking->caregiver_id)
                ->where('status', 'assigned')
                ->where('is_active', true)
                ->first();

            $booking = $assignment ? $assignment->booking : null;
            
            // Check if booking has referral code
            $hasReferral = $booking && $booking->referral_code_id && $booking->referralCode;
            $hasTrainingCenter = $provider->has_training_center && $provider->training_center_id;
            
            // Use PricingService for caregiver rates
            $providerRate = $assignment->assigned_hourly_rate ?? \App\Services\PricingService::getCaregiverRate();
            $clientChargeRate = \App\Services\PricingService::getClientRate($hasReferral);
            $marketingRate = \App\Services\PricingService::MARKETING_RATE;
            $trainingRate = \App\Services\PricingService::TRAINING_CENTER_RATE;
            
            // Calculate provider earnings
            $providerEarnings = $hoursWorked * $providerRate;
            
            // Marketing commission (if referral used)
            $marketingPartnerId = null;
            $marketingCommission = 0;
            
            if ($hasReferral) {
                $referralCode = $booking->referralCode;
                if ($referralCode && $referralCode->user_id) {
                    $marketingPartnerId = $referralCode->user_id;
                    $marketingCommission = $hoursWorked * $marketingRate;
                }
            }
            
            // Training center commission (only for caregivers with training centers)
            $trainingCenterId = null;
            $trainingCommission = 0;
            
            if ($hasTrainingCenter) {
                $trainingCenterId = $provider->training_center_id;
                $trainingCommission = $hoursWorked * $trainingRate;
            }
            
            // Calculate total client charge
            $totalClientCharge = $hoursWorked * $clientChargeRate;
            
            // Calculate agency commission (remainder)
            $agencyCommission = $totalClientCharge - $providerEarnings - $marketingCommission - $trainingCommission;
        }

        // Update time tracking record with all earnings
        $timeTracking->update([
            'caregiver_earnings' => $providerEarnings, // Field stores provider earnings for both types
            'marketing_partner_id' => $marketingPartnerId,
            'marketing_partner_commission' => $marketingCommission > 0 ? $marketingCommission : null,
            'training_center_user_id' => $trainingCenterId,
            'training_center_commission' => $trainingCommission > 0 ? $trainingCommission : null,
            'agency_commission' => $agencyCommission,
            'total_client_charge' => $totalClientCharge,
            'booking_id' => $booking ? $booking->id : null,
            'payment_status' => 'pending'
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
            $dateKey = $date->toDateString();
            
            if (!isset($weeklyData[$dateKey])) {
                $weeklyData[$dateKey] = [
                    'day' => $dayName,
                    'date' => $dateFormatted,
                    'sessions' => [],
                    'total_hours' => 0
                ];
            }

            $hours = $session->hours_worked ?? $session->getCurrentDuration();
            $weeklyData[$dateKey]['sessions'][] = [
                'clock_in' => $session->clock_in_time ? $session->clock_in_time->format('g:i A') : null,
                'clock_out' => $session->clock_out_time ? $session->clock_out_time->format('g:i A') : 'In Progress',
                'hours' => $hours
            ];
            $weeklyData[$dateKey]['total_hours'] += $hours;
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

    /**
     * Housekeeper-specific: Get current active session
     */
    public function getHousekeeperCurrentSession($housekeeperId)
    {
        $activeSession = TimeTracking::where('housekeeper_id', $housekeeperId)
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

    /**
     * Housekeeper-specific: Get today's summary
     */
    public function getHousekeeperTodaySummary($housekeeperId)
    {
        $today = Carbon::now()->toDateString();
        
        // Get all sessions for today
        $todaySessions = TimeTracking::where('housekeeper_id', $housekeeperId)
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

    /**
     * Housekeeper-specific: Get weekly history
     */
    public function getHousekeeperWeeklyHistory($housekeeperId)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $sessions = TimeTracking::where('housekeeper_id', $housekeeperId)
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
            'total_hours' => round($totalHours, 2)
        ]);
    }

    /**
     * Admin: Update a time tracking entry
     */
    public function update(Request $request, $id)
    {
        // Verify admin access
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'admin_staff'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $timeTracking = TimeTracking::findOrFail($id);

        $validated = $request->validate([
            'clock_in_time' => 'nullable|date',
            'clock_out_time' => 'nullable|date|after:clock_in_time',
            'hours_worked' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,completed,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Update the record
        if (isset($validated['clock_in_time'])) {
            $timeTracking->clock_in_time = Carbon::parse($validated['clock_in_time']);
        }

        if (isset($validated['clock_out_time'])) {
            $timeTracking->clock_out_time = Carbon::parse($validated['clock_out_time']);
            // Recalculate hours if clock_out changed
            if ($timeTracking->clock_in_time) {
                $timeTracking->hours_worked = $timeTracking->clock_in_time->diffInMinutes($timeTracking->clock_out_time) / 60;
            }
            $timeTracking->status = 'completed';
        }

        if (isset($validated['hours_worked'])) {
            $timeTracking->hours_worked = $validated['hours_worked'];
        }

        if (isset($validated['status'])) {
            $timeTracking->status = $validated['status'];
        }

        if (isset($validated['notes'])) {
            $timeTracking->notes = $validated['notes'];
        }

        $timeTracking->save();

        // Recalculate earnings if hours changed and provider exists
        if ($timeTracking->hours_worked > 0 && ($timeTracking->caregiver_id || $timeTracking->housekeeper_id)) {
            $this->calculateEarnings($timeTracking);
        }

        return response()->json([
            'success' => true,
            'message' => 'Time entry updated successfully',
            'data' => $timeTracking->fresh()
        ]);
    }

    /**
     * Admin: Delete a time tracking entry
     */
    public function destroy($id)
    {
        // Verify admin access
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['admin', 'admin_staff'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $timeTracking = TimeTracking::findOrFail($id);

        // Don't allow deleting paid entries
        if ($timeTracking->paid_at) {
            return response()->json([
                'error' => 'Cannot delete paid time entries'
            ], 400);
        }

        $timeTracking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Time entry deleted successfully'
        ]);
    }
}