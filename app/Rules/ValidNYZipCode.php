<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validate New York State ZIP Code
 * 
 * NY ZIP Code Rules:
 * - Must be 5 digits (optionally with -XXXX for ZIP+4)
 * - First two digits must be 10-14 (standard NY range)
 * - OR must be one of the special NY ZIPs: 00501, 00544, 06390
 * 
 * Valid examples: 10001, 11215, 12550, 14043, 00501
 * Invalid examples: 09000, 15001, 20001
 */
class ValidNYZipCode implements ValidationRule
{
    /**
     * NY ZIP code validation regex
     * Matches: 00501, 00544, 06390 (special cases) OR 10xxx-14xxx (standard NY range)
     * Optional: -XXXX suffix for ZIP+4 format
     */
    private const NY_ZIP_REGEX = '/^(00501|00544|06390|1[0-4]\d{3})(-\d{4})?$/';

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Allow empty values (use 'required' rule if needed)
        if (empty($value)) {
            return;
        }

        $value = trim((string) $value);

        // Check basic format first
        if (!preg_match('/^\d{5}(-\d{4})?$/', $value)) {
            $fail('The :attribute must be a 5-digit ZIP code.');
            return;
        }

        // Check if it's a valid NY ZIP
        if (!preg_match(self::NY_ZIP_REGEX, $value)) {
            $fail('The :attribute must be a valid New York State ZIP code (10xxx-14xxx).');
        }
    }
}
