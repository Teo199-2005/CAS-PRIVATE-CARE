<?php

if (!function_exists('generate_location_page_content')) {
    function generate_location_page_content($location, $county, $lat, $lng) {
        $locationLower = strtolower($location);
        $locationSlug = str_replace(' ', '-', $locationLower);
        $title = ucfirst($location) . ' Caregiver | Verified & Trusted | CAS Private Care';
        $description = "Find verified caregivers in {$location}, New York. Background-checked professionals for elderly care, personal care & housekeeping. Available 24/7.";
        
        return [
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'county' => $county,
            'slug' => $locationSlug,
            'lat' => $lat,
            'lng' => $lng,
        ];
    }
}


