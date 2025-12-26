<?php

namespace App\Rules;

use App\Services\NYLocationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNYCity implements ValidationRule
{
    public function __construct(private string $countyField = 'county')
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $county = request()->input($this->countyField);
        
        if (!$county || !NYLocationService::isValidCityInCounty($value, $county)) {
            $fail('The :attribute must be a valid city in the selected county.');
        }
    }
}