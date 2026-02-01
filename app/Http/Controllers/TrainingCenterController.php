<?php

namespace App\Http\Controllers;

use App\Models\BookingAssignment;
use App\Models\Caregiver;
use App\Models\TimeTracking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TrainingCenterController
 * 
 * Handles training center dashboard data, caregiver approvals, and statistics.
 */
class TrainingCenterController extends Controller
{
    /**
     * Get pending caregiver requests
     */
    public function pendingCaregivers(Request $request)
    {
        $userId = auth()->id();
        
        $pendingCaregivers = Caregiver::where('training_center_id', $userId)
            ->where('training_center_approval_status', 'pending')
            ->with('user')
            ->get()
            ->map(function ($caregiver) {
                return [
                    'id' => $caregiver->id,
                    'name' => $caregiver->user->name ?? 'Unknown',
                    'email' => $caregiver->user->email ?? '',
                    'phone' => $caregiver->user->phone ?? '',
                    'years_experience' => $caregiver->years_experience ?? 0,
                    'specializations' => $caregiver->specializations ?? [],
                    'bio' => $caregiver->bio ?? '',
                    'requested_at' => $caregiver->updated_at 
                        ? $caregiver->updated_at->format('M d, Y') 
                        : ''
                ];
            });
        
        return response()->json(['pendingCaregivers' => $pendingCaregivers]);
    }

    /**
     * Approve caregiver request
     */
    public function approveCaregiver($id)
    {
        if (!ctype_digit((string) $id)) {
            return response()->json(['message' => 'Invalid caregiver id.'], 422);
        }

        $userId = auth()->id();
        $caregiver = Caregiver::where('id', (int) $id)
            ->where('training_center_id', $userId)
            ->where('training_center_approval_status', 'pending')
            ->first();

        if (!$caregiver) {
            return response()->json([
                'message' => 'Caregiver request not found or already processed. It may have been approved or rejected.',
            ], 404);
        }

        try {
            $caregiver->update(['training_center_approval_status' => 'approved']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Unable to approve. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Caregiver approved successfully',
        ]);
    }

    /**
     * Reject caregiver request
     */
    public function rejectCaregiver($id)
    {
        if (!ctype_digit((string) $id)) {
            return response()->json(['message' => 'Invalid caregiver id.'], 422);
        }

        $userId = auth()->id();
        $caregiver = Caregiver::where('id', (int) $id)
            ->where('training_center_id', $userId)
            ->where('training_center_approval_status', 'pending')
            ->first();

        if (!$caregiver) {
            return response()->json([
                'message' => 'Caregiver request not found or already processed.',
            ], 404);
        }

        try {
            $caregiver->update([
                'training_center_approval_status' => 'rejected',
                'training_center_id' => null,
                'has_training_center' => false,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Unable to reject. Please try again.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Caregiver request rejected',
        ]);
    }

    /**
     * Get training center statistics
     * Commission is $0.50 per actual hour worked (from time_trackings.training_center_commission)
     */
    public function stats(Request $request)
    {
        $userId = auth()->id();
        
        // Only show approved caregivers
        $caregivers = Caregiver::where('training_center_id', $userId)
            ->where('training_center_approval_status', 'approved')
            ->with('user')
            ->get();
        
        // Get ALL time trackings for this training center's caregivers
        $allTimeTrackings = \App\Models\TimeTracking::where('training_center_user_id', $userId)
            ->whereNotNull('training_center_commission')
            ->get();
        
        // Calculate totals from actual time_trackings data
        $totalRevenue = $allTimeTrackings->sum('training_center_commission') ?? 0;
        $totalHours = $allTimeTrackings->sum('hours_worked') ?? 0;
        $pendingRevenue = $allTimeTrackings->whereNull('training_commission_paid_at')
            ->sum('training_center_commission') ?? 0;
        $paidRevenue = $allTimeTrackings->whereNotNull('training_commission_paid_at')
            ->sum('training_center_commission') ?? 0;
        
        // Monthly revenue
        $monthlyRevenue = $allTimeTrackings->filter(function($tt) {
            return $tt->work_date && $tt->work_date >= now()->startOfMonth();
        })->sum('training_center_commission') ?? 0;
        
        // Weekly revenue
        $weeklyRevenue = $allTimeTrackings->filter(function($tt) {
            return $tt->work_date && $tt->work_date >= now()->startOfWeek();
        })->sum('training_center_commission') ?? 0;
        
        // Last payout info
        $lastPayout = $allTimeTrackings->whereNotNull('training_commission_paid_at')
            ->sortByDesc('training_commission_paid_at')
            ->first();
        
        $caregiverData = [];
        
        foreach ($caregivers as $caregiver) {
            // Get actual hours and earnings from time_trackings for this caregiver
            $caregiverTimeTrackings = $allTimeTrackings->where('caregiver_id', $caregiver->id);
            
            $hours = $caregiverTimeTrackings->sum('hours_worked') ?? 0;
            $earnings = $caregiverTimeTrackings->sum('training_center_commission') ?? 0;
            
            $hasActiveBooking = BookingAssignment::where('caregiver_id', $caregiver->id)
                ->where('status', 'assigned')
                ->whereHas('booking', function($q) {
                    $q->whereIn('status', ['approved', 'confirmed', 'assigned']);
                })
                ->exists();
            
            $caregiverData[] = [
                'id' => $caregiver->id,
                'name' => $caregiver->user->name ?? 'Unknown',
                'email' => $caregiver->user->email ?? '',
                'phone' => $caregiver->user->phone ?? '',
                'borough' => $caregiver->user->borough ?? 'N/A',
                'course' => 'Certified Care Training',
                'certification' => 'Certified',
                'hours' => number_format($hours, 1),
                'earnings' => number_format($earnings, 2),
                'status' => $hasActiveBooking ? 'Ongoing Contract' : 'No Contract'
            ];
        }
        
        $thisWeekDeployed = BookingAssignment::whereIn('caregiver_id', $caregivers->pluck('id'))
            ->where('assigned_at', '>=', now()->startOfWeek())
            ->count();
        
        return response()->json([
            'total_caregivers' => $caregivers->count(),
            'total_revenue' => number_format($totalRevenue, 2),
            'monthly_revenue' => number_format($monthlyRevenue, 2),
            'weekly_revenue' => number_format($weeklyRevenue, 2),
            'total_hours' => number_format($totalHours, 1),
            'account_balance' => number_format($pendingRevenue, 2), // Unpaid balance
            'paid_revenue' => number_format($paidRevenue, 2),
            'caregivers' => $caregiverData,
            'weekly_summary' => [
                'deployed_caregivers' => $thisWeekDeployed,
                'target' => 10,
                'previous_payout' => $lastPayout ? number_format($paidRevenue, 2) : '0.00',
                'previous_payout_date' => $lastPayout && $lastPayout->training_commission_paid_at 
                    ? Carbon::parse($lastPayout->training_commission_paid_at)->format('M d, Y') 
                    : null
            ]
        ]);
    }
}
