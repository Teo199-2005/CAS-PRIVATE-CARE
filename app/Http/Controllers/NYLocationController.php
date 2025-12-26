<?php

namespace App\Http\Controllers;

use App\Services\NYLocationService;
use Illuminate\Http\JsonResponse;

class NYLocationController extends Controller
{
    /**
     * Get all NY counties
     */
    public function getCounties(): JsonResponse
    {
        return response()->json(NYLocationService::getCounties());
    }

    /**
     * Get cities for a specific county
     */
    public function getCities(string $county): JsonResponse
    {
        if (!NYLocationService::isValidCounty($county)) {
            return response()->json(['error' => 'Invalid county'], 404);
        }

        return response()->json(NYLocationService::getCitiesForCounty($county));
    }

    /**
     * Get county options for select dropdown
     */
    public function getCountyOptions(): JsonResponse
    {
        return response()->json(NYLocationService::getCountyOptions());
    }

    /**
     * Get city options for select dropdown
     */
    public function getCityOptions(string $county): JsonResponse
    {
        if (!NYLocationService::isValidCounty($county)) {
            return response()->json(['error' => 'Invalid county'], 404);
        }

        return response()->json(NYLocationService::getCityOptions($county));
    }
}