<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPhoneNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Allow empty/null for optional fields
        }
        
        // Remove all non-numeric characters except + at the beginning
        $cleaned = preg_replace('/[^0-9+]/', '', $value);
        
        // Remove leading + for validation
        if (strpos($cleaned, '+') === 0) {
            $cleaned = substr($cleaned, 1);
        }
        
        // Phone number should be between 10-15 digits (international format)
        // US numbers are typically 10 digits
        $digitCount = strlen($cleaned);
        
        if ($digitCount < 10 || $digitCount > 15) {
            $fail('The :attribute must be a valid phone number (10-15 digits).');
            return;
        }
        
        // Basic validation: should not be all same digits
        if (count(array_unique(str_split($cleaned))) === 1) {
            $fail('The :attribute is not a valid phone number.');
            return;
        }
    }
}

