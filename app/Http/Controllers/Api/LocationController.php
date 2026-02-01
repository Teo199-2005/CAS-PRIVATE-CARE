<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\NYLocationService;
use Illuminate\Support\Facades\Storage;

/**
 * Location API Controller
 * 
 * Handles location-related API endpoints for NY counties and cities.
 * Moved from inline route closures for better maintainability.
 */
class LocationController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get all NY locations data (counties and their cities)
     * GET /api/ny-locations
     * 
     * Returns complete NY location data for frontend dropdowns
     */
    public function getAllLocations()
    {
        try {
            // Try to get data from the NYLocationService
            $counties = NYLocationService::getCountiesWithCities();
            
            return response()->json([
                'counties' => $counties
            ]);
        } catch (\Exception $e) {
            // Fallback: read JSON directly
            try {
                $jsonPath = storage_path('app/data/ny_accurate_counties.json');
                if (file_exists($jsonPath)) {
                    $data = json_decode(file_get_contents($jsonPath), true);
                    return response()->json([
                        'counties' => $data
                    ]);
                }
                
                // Return default fallback data
                return response()->json([
                    'counties' => $this->getFallbackLocationData()
                ]);
            } catch (\Exception $e2) {
                return response()->json([
                    'counties' => $this->getFallbackLocationData()
                ]);
            }
        }
    }

    /**
     * Get fallback location data
     */
    private function getFallbackLocationData(): array
    {
        return [
            "Albany" => ["Albany", "Cohoes", "Watervliet", "Green Island", "Menands", "Colonie", "Guilderland", "Bethlehem"],
            "Bronx" => ["Bronx", "Fordham", "Riverdale", "Throggs Neck", "Pelham Bay", "Concourse", "Morrisania"],
            "Brooklyn" => ["Brooklyn", "Bedford-Stuyvesant", "Park Slope", "Williamsburg", "DUMBO", "Bay Ridge"],
            "Erie" => ["Buffalo", "Cheektowaga", "West Seneca", "Amherst", "Tonawanda", "Lackawanna"],
            "Kings" => ["Brooklyn", "Bedford-Stuyvesant", "Park Slope", "Williamsburg", "DUMBO", "Bay Ridge"],
            "Monroe" => ["Rochester", "Brighton", "Greece", "Irondequoit", "Webster", "Penfield"],
            "Nassau" => ["Hempstead", "Long Beach", "Glen Cove", "Freeport", "Valley Stream", "Inwood"],
            "New York" => ["Manhattan", "Upper East Side", "Upper West Side", "Greenwich Village", "SoHo", "Tribeca", "Chelsea", "Midtown", "Hell's Kitchen", "Times Square", "Financial District", "Lower East Side", "East Village", "Chinatown", "Little Italy", "Harlem", "Washington Heights", "Inwood", "Lenox Hill"],
            "Onondaga" => ["Syracuse", "Liverpool", "Baldwinsville", "East Syracuse", "North Syracuse"],
            "Queens" => ["Queens", "Flushing", "Jamaica", "Astoria", "Long Island City", "Forest Hills"],
            "Richmond" => ["Staten Island", "St. George", "Tottenville", "New Brighton", "Port Richmond"],
            "Suffolk" => ["Huntington", "Brookhaven", "Islip", "Babylon", "Smithtown", "Riverhead"],
            "Westchester" => ["White Plains", "Yonkers", "New Rochelle", "Mount Vernon", "Scarsdale"],
        ];
    }

    /**
     * Get all NY counties
     * GET /api/ny-counties
     */
    public function getCounties()
    {
        try {
            $counties = NYLocationService::getCounties();
            return response()->json($counties);
        } catch (\Exception $e) {
            // Fallback: read JSON directly
            try {
                $jsonData = Storage::get('data/ny_accurate_counties.json');
                $data = json_decode($jsonData, true);
                return response()->json(array_keys($data));
            } catch (\Exception $e2) {
                return $this->serverErrorResponse('Failed to load counties');
            }
        }
    }

    /**
     * Get cities for a specific NY county
     * GET /api/ny-cities/{county}
     */
    public function getCitiesForCounty(string $county)
    {
        try {
            $cities = NYLocationService::getCitiesForCounty($county);
            return response()->json($cities);
        } catch (\Exception $e) {
            // Fallback: read JSON directly
            try {
                $jsonData = Storage::get('data/ny_accurate_counties.json');
                $data = json_decode($jsonData, true);
                return response()->json($data[$county] ?? []);
            } catch (\Exception $e2) {
                return $this->serverErrorResponse('Failed to load cities');
            }
        }
    }
}
