# NY Location System Usage Guide

## Overview
This system provides consistent county and city data for New York state across your Laravel application.

## Files Created
- `app/Services/NYLocationService.php` - Main service class
- `app/Helpers/ny_locations.php` - Helper functions
- `resources/views/components/ny-location-selector.blade.php` - Blade component
- `routes/api.php` - API endpoints
- `app/Http/Controllers/NYLocationController.php` - Controller
- `app/Rules/ValidNYCounty.php` - County validation rule
- `app/Rules/ValidNYCity.php` - City validation rule

## Usage Examples

### 1. Using Helper Functions
```php
// Get all counties
$counties = ny_counties();

// Get county options for select dropdown
$countyOptions = ny_county_options();

// Get cities for a specific county
$cities = ny_cities('New York County');

// Get city options for select dropdown
$cityOptions = ny_city_options('New York County');

// Validate county
if (is_valid_ny_county('New York County')) {
    // Valid county
}

// Validate city in county
if (is_valid_ny_city('Manhattan', 'New York County')) {
    // Valid city
}
```

### 2. Using the Service Class Directly
```php
use App\Services\NYLocationService;

$counties = NYLocationService::getCounties();
$cities = NYLocationService::getCitiesForCounty('Kings County');
```

### 3. Using the Blade Component
```blade
<!-- Basic usage -->
<x-ny-location-selector />

<!-- With custom attributes -->
<x-ny-location-selector 
    county-name="user_county"
    city-name="user_city"
    county-value="{{ old('user_county', $user->county) }}"
    city-value="{{ old('user_city', $user->city) }}"
    county-id="user_county"
    city-id="user_city"
    :required="true"
    county-label="Your County"
    city-label="Your City"
/>
```

### 4. Form Validation
```php
use App\Rules\ValidNYCounty;
use App\Rules\ValidNYCity;

$request->validate([
    'county' => ['required', new ValidNYCounty],
    'city' => ['required', new ValidNYCity('county')],
]);
```

### 5. API Endpoints
```javascript
// Get all counties
fetch('/api/ny-counties')
    .then(response => response.json())
    .then(counties => console.log(counties));

// Get cities for a county
fetch('/api/ny-cities/New York County')
    .then(response => response.json())
    .then(cities => console.log(cities));
```

### 6. Controller Usage
```php
use App\Http\Controllers\NYLocationController;

Route::get('/locations/counties', [NYLocationController::class, 'getCounties']);
Route::get('/locations/cities/{county}', [NYLocationController::class, 'getCities']);
```

## Setup Instructions

1. Run `composer dump-autoload` to load the helper functions
2. The JSON data file is already in `storage/app/data/ny_accurate_counties.json`
3. Use the component in your Blade templates
4. Add validation rules to your form requests

## Benefits

- **Consistency**: All county/city data comes from one source
- **Easy Updates**: Change data in one JSON file
- **Validation**: Built-in validation rules
- **Reusable**: Use anywhere in your application
- **Dynamic**: Cities update based on county selection
- **API Ready**: RESTful endpoints available