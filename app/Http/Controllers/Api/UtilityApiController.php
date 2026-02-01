<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\ZipCodeService;
use App\Models\User;
use App\Models\Caregiver;
use App\Models\Housekeeper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Utility API Controller
 * 
 * Handles miscellaneous API endpoints that were previously inline in routes.
 * This improves code organization, testability, and maintainability.
 */
class UtilityApiController extends Controller
{
    use ApiResponseTrait;
    /**
     * Lookup ZIP code information (NY-only)
     * 
     * NY ZIP Code Rules:
     * - Must be 5 digits (optionally with -XXXX for ZIP+4)
     * - First two digits must be 10-14 (standard NY range)
     * - OR must be one of the special NY ZIPs: 00501, 00544, 06390
     */
    public function lookupZipCode(string $zip): JsonResponse
    {
        // Get the 5-digit base ZIP (handles ZIP+4 format)
        $zip = ZipCodeService::getBaseZipCode(preg_replace('/[^\d-]/', '', $zip));
        
        // Validate ZIP code format
        if (!ZipCodeService::isValidZipFormat($zip)) {
            return response()->json([
                'success' => false,
                'message' => 'ZIP code must be 5 digits'
            ], 422);
        }

        // Validate NY ZIP code range (10xxx-14xxx or special cases: 00501, 00544, 06390)
        if (!ZipCodeService::isValidNYZipCode($zip)) {
            return response()->json([
                'success' => false,
                'message' => 'Not a valid New York State ZIP code (must be 10xxx-14xxx)'
            ], 400);
        }

        // Lookup location (now includes region fallback for valid NY ZIPs)
        $location = ZipCodeService::lookupZipCode($zip);
        
        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'ZIP code not found in database'
            ], 404);
        }

        // Location is in "City, NY" format
        [$city, $state] = array_pad(explode(',', $location, 2), 2, '');
        $city = trim((string) $city);
        $state = strtoupper(trim((string) $state)) ?: 'NY';

        // Return flat response for backward compatibility with frontend
        return response()->json([
            'success' => true,
            'zip' => $zip,
            'city' => $city,
            'state' => $state,
            'place' => "{$city}, {$state}",
            'location' => "{$city}, {$state}",
            'valid_ny' => true
        ]);
    }

    /**
     * Get caregiver application status
     */
    public function caregiverApplicationStatus(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('web')->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $status = $user->status ?? 'pending';
            $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') 
                ? 'approved' 
                : 'pending';
            
            return response()->json([
                'success' => true,
                'status' => $approvalStatus,
                'application' => [
                    'status' => $approvalStatus
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check application status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get marketing staff application status
     */
    public function marketingApplicationStatus(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('web')->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $status = $user->status ?? 'pending';
            $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') 
                ? 'approved' 
                : 'pending';
            
            return response()->json([
                'success' => true,
                'status' => $approvalStatus,
                'application' => [
                    'status' => $approvalStatus
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check application status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get housekeeper application status
     */
    public function housekeeperApplicationStatus(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('web')->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $status = $user->status ?? 'pending';
            $approvalStatus = (strtolower($status) === 'active' || strtolower($status) === 'approved') 
                ? 'approved' 
                : 'pending';
            
            return response()->json([
                'success' => true,
                'status' => $approvalStatus,
                'application' => [
                    'status' => $approvalStatus
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check application status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generic application status check (for any user type)
     */
    public function applicationStatus(Request $request): JsonResponse
    {
        try {
            $user = Auth::guard('web')->user();
            
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $status = $user->status ?? 'pending';
            $approvalStatus = in_array(strtolower($status), ['active', 'approved']) 
                ? 'approved' 
                : 'pending';
            
            return response()->json([
                'success' => true,
                'status' => $approvalStatus,
                'user_type' => $user->user_type ?? 'unknown',
                'application' => [
                    'status' => $approvalStatus,
                    'submitted_at' => $user->created_at?->toISOString(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to check application status: ' . $e->getMessage()
            ], 500);
        }
    }
}
