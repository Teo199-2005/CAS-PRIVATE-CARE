<?php

namespace App\Http\Controllers;

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
    /**
     * Look up location by ZIP code
     */
    public function zipCodeLookup(string $zip)
    {
        // Validate ZIP code format
        if (!preg_match('/^\d{5}$/', $zip)) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid ZIP code format'
            ], 400);
        }

        // Use our internal ZIP service
        $location = ZipCodeService::lookupZipCode($zip);

        // Handle unknown ZIPs - try static map directly
        if (!$location || $location === 'New York, NY') {
            $location = $this->lookupFromStaticMap($zip);
        }

        if (!$location || $location === 'New York, NY') {
            return response()->json([
                'success' => false,
                'error' => 'ZIP code not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'location' => $location,
            'zip' => $zip
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
        
        return response()->json(['error' => 'Location data not found'], 404);
    }

    /**
     * Check if email already exists
     */
    public function checkEmailExists(string $email)
    {
        $exists = User::where('email', $email)->exists();
        
        return response()->json([
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
        
        return response()->json([
            'verified' => $user && $user->email_verified_at ? true : false,
            'email' => $user ? $user->email : null
        ]);
    }
}
