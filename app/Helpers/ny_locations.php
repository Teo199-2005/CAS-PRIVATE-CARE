<?php

use App\Services\NYLocationService;

if (!function_exists('ny_counties')) {
    /**
     * Get all NY counties
     */
    function ny_counties(): array
    {
        return NYLocationService::getCounties();
    }
}

if (!function_exists('ny_county_options')) {
    /**
     * Get NY counties formatted for select options
     */
    function ny_county_options(): array
    {
        return NYLocationService::getCountyOptions();
    }
}

if (!function_exists('ny_cities')) {
    /**
     * Get cities for a specific NY county
     */
    function ny_cities(string $county): array
    {
        return NYLocationService::getCitiesForCounty($county);
    }
}

if (!function_exists('ny_city_options')) {
    /**
     * Get cities for a county formatted for select options
     */
    function ny_city_options(string $county): array
    {
        return NYLocationService::getCityOptions($county);
    }
}

if (!function_exists('is_valid_ny_county')) {
    /**
     * Check if a county is valid
     */
    function is_valid_ny_county(string $county): bool
    {
        return NYLocationService::isValidCounty($county);
    }
}

if (!function_exists('is_valid_ny_city')) {
    /**
     * Check if a city is valid for a specific county
     */
    function is_valid_ny_city(string $city, string $county): bool
    {
        return NYLocationService::isValidCityInCounty($city, $county);
    }
}