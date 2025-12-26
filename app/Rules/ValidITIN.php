<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidITIN implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Allow empty/null for optional fields
        }
        
        // Remove any non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        
        // ITIN must be exactly 9 digits
        // Must start with 9
        // Format: 9XX-XX-XXXX
        if (strlen($cleaned) !== 9) {
            $fail('The :attribute must be a valid 9-digit ITIN.');
            return;
        }
        
        if (substr($cleaned, 0, 1) !== '9') {
            $fail('The :attribute must start with 9.');
            return;
        }
        
        // Check that it's not all zeros in last 4 digits
        $lastFour = substr($cleaned, 5, 4);
        if ($lastFour === '0000') {
            $fail('The :attribute contains an invalid serial number.');
            return;
        }
    }
}

