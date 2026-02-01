<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

/**
 * Strong Password Validation Rule
 * 
 * Ensures passwords meet security requirements:
 * - Minimum length
 * - Character complexity
 * - Common password checking
 * - Breach detection (optional)
 * 
 * @package App\Rules
 */
class StrongPassword implements ValidationRule
{
    /**
     * Minimum password length
     */
    protected int $minLength;

    /**
     * Whether to check against common passwords
     */
    protected bool $checkCommon;

    /**
     * Whether to check against breached passwords (HIBP API)
     */
    protected bool $checkBreached;

    /**
     * Create a new rule instance.
     *
     * @param int $minLength Minimum password length (default: 8)
     * @param bool $checkCommon Check against common passwords (default: true)
     * @param bool $checkBreached Check against breached passwords (default: false for performance)
     */
    public function __construct(int $minLength = 8, bool $checkCommon = true, bool $checkBreached = false)
    {
        $this->minLength = $minLength;
        $this->checkCommon = $checkCommon;
        $this->checkBreached = $checkBreached;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('The :attribute is required.');
            return;
        }

        // Length check
        if (strlen($value) < $this->minLength) {
            $fail("The :attribute must be at least {$this->minLength} characters.");
            return;
        }

        // Maximum length (prevent DOS)
        if (strlen($value) > 128) {
            $fail('The :attribute must be less than 128 characters.');
            return;
        }

        // Complexity checks
        $errors = [];

        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = 'a lowercase letter';
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = 'an uppercase letter';
        }

        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = 'a number';
        }

        if (!preg_match('/[@$!%*?&#^()_+\-=\[\]{};\':"\\|,.<>\/]/', $value)) {
            $errors[] = 'a special character';
        }

        if (!empty($errors)) {
            $fail('The :attribute must contain ' . $this->formatList($errors) . '.');
            return;
        }

        // Check for repeated characters (e.g., "aaa" or "111")
        if (preg_match('/(.)\1{2,}/', $value)) {
            $fail('The :attribute cannot contain 3 or more repeated characters in a row.');
            return;
        }

        // Check for sequential characters
        if ($this->hasSequentialChars($value)) {
            $fail('The :attribute cannot contain sequential characters (e.g., "abc", "123").');
            return;
        }

        // Check against common passwords
        if ($this->checkCommon && $this->isCommonPassword($value)) {
            $fail('The :attribute is too common. Please choose a more unique password.');
            return;
        }

        // Check against breached passwords (optional - requires API call)
        if ($this->checkBreached && $this->isBreachedPassword($value)) {
            $fail('The :attribute has been found in a data breach. Please choose a different password.');
            return;
        }
    }

    /**
     * Check for sequential characters (abc, 123, etc.)
     */
    protected function hasSequentialChars(string $value): bool
    {
        $lowerValue = strtolower($value);
        
        $sequences = [
            'abcdefghijklmnopqrstuvwxyz',
            'zyxwvutsrqponmlkjihgfedcba',
            '0123456789',
            '9876543210',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm',
        ];

        foreach ($sequences as $sequence) {
            for ($i = 0; $i <= strlen($sequence) - 4; $i++) {
                if (strpos($lowerValue, substr($sequence, $i, 4)) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if password is in common passwords list
     */
    protected function isCommonPassword(string $value): bool
    {
        // Cache common passwords list
        $commonPasswords = Cache::remember('common_passwords', 86400, function () {
            return [
                'password', 'password1', 'password123', '123456', '12345678', '123456789',
                'qwerty', 'abc123', 'monkey', '1234567', 'letmein', 'trustno1',
                'dragon', 'baseball', 'iloveyou', 'master', 'sunshine', 'ashley',
                'bailey', 'shadow', '123123', '654321', 'superman', 'qazwsx',
                'michael', 'football', 'password1!', 'admin', 'login', 'welcome',
                'passw0rd', 'pa$$w0rd', 'p@ssw0rd', 'pass@123', 'admin123',
                'root', 'test', 'guest', 'user', 'changeme', 'secret',
            ];
        });

        return in_array(strtolower($value), $commonPasswords, true);
    }

    /**
     * Check against Have I Been Pwned API (k-anonymity model)
     * This only sends the first 5 chars of the SHA1 hash, never the full password
     */
    protected function isBreachedPassword(string $value): bool
    {
        try {
            $sha1 = strtoupper(sha1($value));
            $prefix = substr($sha1, 0, 5);
            $suffix = substr($sha1, 5);

            $response = @file_get_contents(
                "https://api.pwnedpasswords.com/range/{$prefix}",
                false,
                stream_context_create([
                    'http' => [
                        'timeout' => 2,
                        'header' => 'Add-Padding: true',
                    ],
                ])
            );

            if ($response === false) {
                // API unavailable - don't block user
                return false;
            }

            // Check if suffix is in response
            $hashes = explode("\r\n", $response);
            foreach ($hashes as $hash) {
                [$hashSuffix, $count] = explode(':', $hash);
                if (strtoupper($hashSuffix) === $suffix && (int)$count > 0) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            // Log error but don't block user
            \Log::warning('HIBP API check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format list for error message
     */
    protected function formatList(array $items): string
    {
        if (count($items) === 1) {
            return $items[0];
        }

        $last = array_pop($items);
        return implode(', ', $items) . ' and ' . $last;
    }
}
