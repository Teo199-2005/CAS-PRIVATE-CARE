<?php

namespace App\Rules;

use App\Services\NYLocationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNYCounty implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!NYLocationService::isValidCounty($value)) {
            $fail('The :attribute must be a valid New York county.');
        }
    }
}