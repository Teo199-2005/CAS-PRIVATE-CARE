<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSSN implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Allow empty/null for optional fields
        }
        
        // Remove any non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $value);
        
        // SSN must be exactly 9 digits
        // Must not start with 000, 666, or 9xx
        // First 3 digits cannot be 000
        // Middle 2 digits cannot be 00
        // Last 4 digits cannot be 0000
        if (strlen($cleaned) !== 9) {
            $fail('The :attribute must be a valid 9-digit SSN.');
            return;
        }
        
        $firstThree = substr($cleaned, 0, 3);
        $middleTwo = substr($cleaned, 3, 2);
        $lastFour = substr($cleaned, 5, 4);
        
        if ($firstThree === '000' || $firstThree === '666' || substr($firstThree, 0, 1) === '9') {
            $fail('The :attribute contains an invalid area number.');
            return;
        }
        
        if ($middleTwo === '00') {
            $fail('The :attribute contains an invalid group number.');
            return;
        }
        
        if ($lastFour === '0000') {
            $fail('The :attribute contains an invalid serial number.');
            return;
        }
    }
}

