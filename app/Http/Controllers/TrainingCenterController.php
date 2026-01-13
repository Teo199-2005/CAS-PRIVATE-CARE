<?php

namespace App\Http\Controllers;

use App\Models\BookingAssignment;
use App\Models\Caregiver;
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
        $userId = auth()->id();
        
        $caregiver = Caregiver::where('id', $id)
            ->where('training_center_id', $userId)
            ->where('training_center_approval_status', 'pending')
            ->firstOrFail();
        
        $caregiver->update(['training_center_approval_status' => 'approved']);
        
        return response()->json([
            'success' => true, 
            'message' => 'Caregiver approved successfully'
        ]);
    }

    /**
     * Reject caregiver request
     */
    public function rejectCaregiver($id)
    {
        $userId = auth()->id();
        
        $caregiver = Caregiver::where('id', $id)
            ->where('training_center_id', $userId)
            ->where('training_center_approval_status', 'pending')
            ->firstOrFail();
        
        $caregiver->update([
            'training_center_approval_status' => 'rejected',
            'training_center_id' => null,
            'has_training_center' => false
        ]);
        
        return response()->json([
            'success' => true, 
            'message' => 'Caregiver request rejected'
        ]);
    }

    /**
     * Get training center statistics
     */
    public function stats(Request $request)
    {
        $userId = auth()->id();
        
        // Only show approved caregivers
        $caregivers = Caregiver::where('training_center_id', $userId)
            ->where('training_center_approval_status', 'approved')
            ->with('user')
            ->get();
        
        $totalHours = 0;
        $totalRevenue = 0;
        $caregiverData = [];
        
        foreach ($caregivers as $caregiver) {
            $timeTrackings = DB::table('time_trackings')
                ->where('caregiver_id', $caregiver->id)
                ->where('status', 'completed')
                ->get();
            
            $hours = $timeTrackings->sum('hours_worked');
            $earnings = $hours * 0.50; // Training center commission
            $totalHours += $hours;
            $totalRevenue += $earnings;
            
            $hasActiveBooking = BookingAssignment::where('caregiver_id', $caregiver->id)
                ->where('status', 'assigned')
                ->whereHas('booking', function($q) {
                    $q->whereIn('status', ['approved', 'confirmed']);
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
                'earnings' => number_format($earnings, 2),
                'status' => $hasActiveBooking ? 'Ongoing Contract' : 'No Contract'
            ];
        }
        
        $thisWeekDeployed = BookingAssignment::whereIn('caregiver_id', $caregivers->pluck('id'))
            ->where('assigned_at', '>=', now()->startOfWeek())
            ->count();
        
        return response()->json([
            'total_caregivers' => $caregivers->count(),
            'total_revenue' => $totalRevenue,
            'total_hours' => $totalHours,
            'account_balance' => $totalRevenue,
            'caregivers' => $caregiverData,
            'weekly_summary' => [
                'deployed_caregivers' => $thisWeekDeployed,
                'target' => 10,
                'previous_payout' => $totalRevenue * 0.8,
                'previous_payout_date' => now()->subWeek()->format('M d, Y')
            ]
        ]);
    }
}
