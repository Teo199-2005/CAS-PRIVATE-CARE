<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * ApplicationStatusController
 * 
 * Handles application status checks for contractors/partners.
 * Returns approval status for caregivers, marketing staff, and training centers.
 */
class ApplicationStatusController extends Controller
{
    /**
     * Get caregiver application status
     */
    public function caregiverStatus()
    {
        $user = auth()->user();
        $status = $user->status ?? 'pending';
        $approvalStatus = $this->determineApprovalStatus($status);
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus,
            'application' => ['status' => $approvalStatus]
        ]);
    }

    /**
     * Get marketing staff application status
     */
    public function marketingStatus()
    {
        $user = auth()->user();
        $status = $user->status ?? 'pending';
        $approvalStatus = $this->determineApprovalStatus($status);
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus
        ]);
    }

    /**
     * Get training center application status
     */
    public function trainingStatus()
    {
        $user = auth()->user();
        $status = $user->status ?? 'pending';
        $approvalStatus = $this->determineApprovalStatus($status);
        
        return response()->json([
            'success' => true,
            'status' => $approvalStatus
        ]);
    }

    /**
     * Determine approval status from user status
     */
    protected function determineApprovalStatus(string $status): string
    {
        return (strtolower($status) === 'active' || strtolower($status) === 'approved') 
            ? 'approved' 
            : 'pending';
    }
}
