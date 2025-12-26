<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caregiver;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CaregiverController extends Controller
{
    public function removeRatings(Request $request)
    {
        $request->validate([
            'caregiver_ids' => 'required|array',
            'caregiver_ids.*' => 'exists:caregivers,id',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $caregiverIds = $request->caregiver_ids;
            $reason = $request->reason;

            // Log the action
            Log::info('Admin removing ratings', [
                'admin_id' => auth()->id(),
                'caregiver_ids' => $caregiverIds,
                'reason' => $reason
            ]);

            // Remove all ratings for selected caregivers
            $deletedCount = Rating::whereIn('caregiver_id', $caregiverIds)->delete();

            // Reset caregiver rating averages
            Caregiver::whereIn('id', $caregiverIds)->update([
                'rating' => 0.00,
                'total_ratings' => 0
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$deletedCount} ratings for " . count($caregiverIds) . " caregiver(s)"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove caregiver ratings', [
                'error' => $e->getMessage(),
                'caregiver_ids' => $caregiverIds ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove ratings'
            ], 500);
        }
    }
}