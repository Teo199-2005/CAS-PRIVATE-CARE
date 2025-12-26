<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidNYPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('The :attribute is required.');
            return;
        }
        
        // Remove all non-numeric characters (handles formatted input like (555) 123-4567)
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        
        // Must be exactly 10 digits (US format)
        if (strlen($cleaned) !== 10) {
            $fail('The :attribute must be a valid US phone number (10 digits).');
            return;
        }
        
        // Area code cannot start with 0 or 1
        $areaCode = substr($cleaned, 0, 3);
        if ($areaCode[0] === '0' || $areaCode[0] === '1') {
            $fail('The :attribute has an invalid area code. Area codes cannot start with 0 or 1.');
            return;
        }
        
        // Exchange code (middle 3 digits) cannot start with 0 or 1
        $exchangeCode = substr($cleaned, 3, 3);
        if ($exchangeCode[0] === '0' || $exchangeCode[0] === '1') {
            $fail('The :attribute has an invalid exchange code. Exchange codes cannot start with 0 or 1.');
            return;
        }
        
        // Basic validation: should not be all same digits
        if (count(array_unique(str_split($cleaned))) === 1) {
            $fail('The :attribute is not a valid phone number.');
            return;
        }
    }
}

