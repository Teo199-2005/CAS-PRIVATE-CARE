<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * API controller for time tracking operations.
 * Extracted from inline route closures as part of the codebase refactoring.
 *
 * @see AUDIT_COMPLIANCE.md Task 2.2
 */
class TimeTrackingApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get time tracking history.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function history()
    {
        try {
            // Join through caregivers table to get caregiver user name
            // Join through clients table to get client user name
            $timeTrackings = DB::table('time_trackings')
                ->leftJoin('caregivers', 'time_trackings.caregiver_id', '=', 'caregivers.id')
                ->leftJoin('users as caregiver_users', 'caregivers.user_id', '=', 'caregiver_users.id')
                ->leftJoin('clients', 'time_trackings.client_id', '=', 'clients.id')
                ->leftJoin('users as client_users', 'clients.user_id', '=', 'client_users.id')
                ->select(
                    'time_trackings.*',
                    'caregiver_users.name as caregiver_name',
                    'client_users.name as client_name'
                )
                ->orderBy('time_trackings.work_date', 'desc')
                ->orderBy('time_trackings.clock_in_time', 'desc')
                ->get();
            
            $history = $timeTrackings->map(function ($record) {
                $clockIn = $record->clock_in_time ? date('g:i A', strtotime($record->clock_in_time)) : 'N/A';
                $clockOut = $record->clock_out_time ? date('g:i A', strtotime($record->clock_out_time)) : 'N/A';
                $workDate = $record->work_date ? date('m/d/Y', strtotime($record->work_date)) : 'N/A';
                
                return [
                    'id' => $record->id,
                    'date' => $workDate,
                    'caregiver' => $record->caregiver_name ?? 'Unknown Caregiver',
                    'client' => $record->client_name ?? 'Unknown Client',
                    'clockIn' => $clockIn,
                    'clockOut' => $clockOut,
                    'hoursWorked' => (float) $record->hours_worked,
                    'status' => $record->status === 'active' ? 'Active' : 'Completed'
                ];
            });
            
            return $this->successResponse(['history' => $history]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch time tracking history: ' . $e->getMessage());
            return $this->errorResponse(
                'Failed to fetch time tracking history: ' . $e->getMessage(),
                500
            );
        }
    }
}
