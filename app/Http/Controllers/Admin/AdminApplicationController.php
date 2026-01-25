<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin Application Management Controller
 * 
 * Extracted from AdminController for better maintainability.
 * Handles contractor application approval/rejection.
 * 
 * Phase 2 Refactoring: Controller Extraction
 */
class AdminApplicationController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Get all pending applications
     */
    public function index()
    {
        try {
            // Get pending caregivers
            $pendingCaregivers = User::where('user_type', 'caregiver')
                ->where(function ($q) {
                    $q->where('contractor_status', 'pending')
                      ->orWhereNull('contractor_status');
                })
                ->with('caregiver')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'type' => 'caregiver',
                        'applied_at' => $user->created_at,
                        'years_experience' => $user->caregiver->years_experience ?? 0,
                        'training_center' => $user->training_center,
                    ];
                });

            // Get pending housekeepers
            $pendingHousekeepers = User::where('user_type', 'housekeeper')
                ->where(function ($q) {
                    $q->where('contractor_status', 'pending')
                      ->orWhereNull('contractor_status');
                })
                ->with('housekeeper')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'type' => 'housekeeper',
                        'applied_at' => $user->created_at,
                        'years_experience' => $user->housekeeper->years_experience ?? 0,
                    ];
                });

            $applications = $pendingCaregivers->merge($pendingHousekeepers)
                ->sortByDesc('applied_at')
                ->values();

            return response()->json([
                'success' => true,
                'applications' => $applications,
                'count' => $applications->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch applications', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch applications'
            ], 500);
        }
    }

    /**
     * Approve an application
     */
    public function approve($id)
    {
        try {
            $user = User::findOrFail($id);

            if (!in_array($user->user_type, ['caregiver', 'housekeeper'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only contractor applications can be approved'
                ], 400);
            }

            $user->update([
                'contractor_status' => 'approved',
                'approved_at' => now(),
            ]);

            // Update role-specific status
            if ($user->user_type === 'caregiver' && $user->caregiver) {
                $user->caregiver->update(['availability_status' => 'available']);
            } elseif ($user->user_type === 'housekeeper' && $user->housekeeper) {
                $user->housekeeper->update(['availability_status' => 'available']);
            }

            // Send approval email
            try {
                $this->emailService->sendApprovalEmail($user);
            } catch (\Exception $e) {
                Log::warning('Failed to send approval email', [
                    'user_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Application approved', [
                'user_id' => $id,
                'user_type' => $user->user_type,
                'approved_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application approved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to approve application', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application'
            ], 500);
        }
    }

    /**
     * Reject an application
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $user = User::findOrFail($id);

            if (!in_array($user->user_type, ['caregiver', 'housekeeper'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only contractor applications can be rejected'
                ], 400);
            }

            $user->update([
                'contractor_status' => 'rejected',
                'rejection_reason' => $validated['reason'] ?? null,
            ]);

            // Send rejection email
            try {
                $this->emailService->sendRejectionEmail($user, $validated['reason'] ?? null);
            } catch (\Exception $e) {
                Log::warning('Failed to send rejection email', [
                    'user_id' => $id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Application rejected', [
                'user_id' => $id,
                'user_type' => $user->user_type,
                'rejected_by' => auth()->id(),
                'reason' => $validated['reason'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application rejected'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reject application', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject application'
            ], 500);
        }
    }

    /**
     * Unapprove (revert to pending) an approved contractor
     */
    public function unapprove($id)
    {
        try {
            $user = User::findOrFail($id);

            if (!in_array($user->user_type, ['caregiver', 'housekeeper'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only contractors can be unapproved'
                ], 400);
            }

            $user->update([
                'contractor_status' => 'pending',
                'approved_at' => null,
            ]);

            Log::info('Application unapproved', [
                'user_id' => $id,
                'user_type' => $user->user_type,
                'unapproved_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contractor status reverted to pending'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to unapprove application', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to unapprove application'
            ], 500);
        }
    }
}
