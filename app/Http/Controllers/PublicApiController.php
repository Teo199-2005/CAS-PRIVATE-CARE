<?php

namespace App\Http\Controllers;

use App\Http\Traits\ApiResponseTrait;
use App\Models\User;
use App\Services\ZipCodeService;
use Illuminate\Http\Request;

/**
 * PublicApiController
 * 
 * Handles public API endpoints that don't require authentication.
 * These endpoints are used for registration, lookup, and validation.
 */
class PublicApiController extends Controller
{
    use ApiResponseTrait;

    /**
     * Look up location by ZIP code
     * 
     * Returns location data in the format expected by frontend:
     * { success: true, location: "City, NY" } for successful lookups
     * { success: false, message: "error" } for failures
     * 
     * NY ZIP Code Rules:
     * - Must be 5 digits (optionally with -XXXX for ZIP+4)
     * - First two digits must be 10-14 (standard NY range)
     * - OR must be one of the special NY ZIPs: 00501, 00544, 06390
     */
    public function zipCodeLookup(string $zip)
    {
        // Get the 5-digit base ZIP
        $zip = ZipCodeService::getBaseZipCode($zip);
        
        // Validate ZIP code format
        if (!ZipCodeService::isValidZipFormat($zip)) {
            return response()->json([
                'success' => false,
                'message' => 'ZIP code must be 5 digits'
            ], 400);
        }

        // Validate NY ZIP code range (10xxx-14xxx or special cases)
        if (!ZipCodeService::isValidNYZipCode($zip)) {
            return response()->json([
                'success' => false,
                'message' => 'Not a valid New York State ZIP code'
            ], 400);
        }

        // Use our internal ZIP service (now includes region fallback)
        $location = ZipCodeService::lookupZipCode($zip);

        // Handle unknown ZIPs - try static map directly as fallback
        if (!$location) {
            $location = $this->lookupFromStaticMap($zip);
        }

        if (!$location) {
            return response()->json([
                'success' => false,
                'message' => 'ZIP code not found'
            ], 404);
        }
        
        // Return flat response matching frontend expectations
        return response()->json([
            'success' => true,
            'location' => $location,
            'zip' => $zip,
            'valid_ny' => true
        ]);
    }

    /**
     * Try to look up ZIP from static map using reflection
     */
    protected function lookupFromStaticMap(string $zip): ?string
    {
        try {
            $ref = new \ReflectionClass(ZipCodeService::class);
            $method = $ref->getMethod('getStaticZipCodeMap');
            $method->setAccessible(true);
            $staticMap = $method->invoke(null);
            
            if (isset($staticMap[$zip])) {
                return $staticMap[$zip];
            }
        } catch (\Throwable $e) {
            // If reflection fails, return null
        }
        
        return null;
    }

    /**
     * Get location data from JSON file
     */
    public function locationData()
    {
        $jsonPath = storage_path('app/data/ny_accurate_counties.json');
        
        if (file_exists($jsonPath)) {
            return response()->json(json_decode(file_get_contents($jsonPath), true));
        }
        
        return $this->notFoundResponse('Location data not found');
    }

    /**
     * Check if email already exists
     */
    public function checkEmailExists(string $email)
    {
        $exists = User::where('email', $email)->exists();
        
        return $this->successResponse([
            'exists' => $exists,
            'email' => $email
        ]);
    }

    /**
     * Get email verification status
     */
    public function verificationStatus()
    {
        $user = auth()->user();
        
        return $this->successResponse([
            'verified' => $user && $user->email_verified_at ? true : false,
            'email' => $user ? $user->email : null
        ]);
    }
}
