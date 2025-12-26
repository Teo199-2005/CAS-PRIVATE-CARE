<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class NYLocationService
{
    private static $locationData = null;

    /**
     * Get all NY counties and cities data
     */
    public static function getLocationData(): array
    {
        if (self::$locationData === null) {
            $jsonData = Storage::get('data/ny_accurate_counties.json');
            self::$locationData = json_decode($jsonData, true);
        }
        
        return self::$locationData;
    }

    /**
     * Get all counties
     */
    public static function getCounties(): array
    {
        return array_keys(self::getLocationData());
    }

    /**
     * Get cities for a specific county
     */
    public static function getCitiesForCounty(string $county): array
    {
        $data = self::getLocationData();
        return $data[$county] ?? [];
    }

    /**
     * Get all counties formatted for select options
     */
    public static function getCountyOptions(): array
    {
        $counties = self::getCounties();
        return array_combine($counties, $counties);
    }

    /**
     * Get cities for a county formatted for select options
     */
    public static function getCityOptions(string $county): array
    {
        $cities = self::getCitiesForCounty($county);
        return array_combine($cities, $cities);
    }

    /**
     * Validate if a county exists
     */
    public static function isValidCounty(string $county): bool
    {
        return array_key_exists($county, self::getLocationData());
    }

    /**
     * Validate if a city exists in a specific county
     */
    public static function isValidCityInCounty(string $city, string $county): bool
    {
        $cities = self::getCitiesForCounty($county);
        return in_array($city, $cities);
    }
}