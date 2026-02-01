<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Secure Password Validation Rule
 * 
 * Validates passwords against the configured password policy.
 * Uses Laravel's built-in Password rule with additional custom checks.
 * 
 * Usage:
 *   'password' => ['required', new SecurePassword()]
 *   'password' => ['required', new SecurePassword(checkHistory: true, userId: 123)]
 * 
 * @see config/password.php for policy configuration
 */
class SecurePassword implements ValidationRule
{
    /**
     * Whether to check password history
     */
    protected bool $checkHistory;

    /**
     * User ID for history check
     */
    protected ?int $userId;

    /**
     * Create a new rule instance.
     *
     * @param bool $checkHistory Whether to check against password history
     * @param int|null $userId User ID for password history check
     */
    public function __construct(bool $checkHistory = false, ?int $userId = null)
    {
        $this->checkHistory = $checkHistory;
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $config = config('password');

        // Check minimum length
        if (strlen($value) < $config['min_length']) {
            $fail("The :attribute must be at least {$config['min_length']} characters.");
            return;
        }

        // Check maximum length
        if (strlen($value) > $config['max_length']) {
            $fail("The :attribute must not exceed {$config['max_length']} characters.");
            return;
        }

        // Check mixed case
        if ($config['require_mixed_case']) {
            if (!preg_match('/[a-z]/', $value) || !preg_match('/[A-Z]/', $value)) {
                $fail('The :attribute must contain at least one uppercase and one lowercase letter.');
                return;
            }
        }

        // Check for numbers
        if ($config['require_numbers']) {
            if (!preg_match('/[0-9]/', $value)) {
                $fail('The :attribute must contain at least one number.');
                return;
            }
        }

        // Check for symbols
        if ($config['require_symbols']) {
            if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
                $fail('The :attribute must contain at least one special character.');
                return;
            }
        }

        // Check blocked passwords (case-insensitive)
        $lowerValue = strtolower($value);
        if (in_array($lowerValue, array_map('strtolower', $config['blocked_passwords']))) {
            $fail('The :attribute is too common. Please choose a more secure password.');
            return;
        }

        // Check if password contains user email or name
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            if ($user) {
                $email = strtolower($user->email);
                $emailLocal = explode('@', $email)[0];
                
                if (str_contains($lowerValue, $emailLocal)) {
                    $fail('The :attribute cannot contain your email address.');
                    return;
                }

                if ($user->name) {
                    $nameParts = explode(' ', strtolower($user->name));
                    foreach ($nameParts as $part) {
                        if (strlen($part) >= 3 && str_contains($lowerValue, $part)) {
                            $fail('The :attribute cannot contain your name.');
                            return;
                        }
                    }
                }
            }
        }

        // Check password history
        if ($this->checkHistory && $this->userId && $config['history_count'] > 0) {
            $historyCount = $config['history_count'];
            $passwordHistories = \App\Models\PasswordHistory::where('user_id', $this->userId)
                ->orderBy('created_at', 'desc')
                ->take($historyCount)
                ->get();

            foreach ($passwordHistories as $history) {
                if (Hash::check($value, $history->password)) {
                    $fail("The :attribute cannot be one of your last {$historyCount} passwords.");
                    return;
                }
            }
        }

        // Check against Have I Been Pwned (if enabled)
        if ($config['check_uncompromised']) {
            try {
                $threshold = $config['uncompromised_threshold'];
                
                // Use Laravel's built-in uncompromised check
                $passwordRule = Password::min(1)->uncompromised($threshold);
                
                $validator = validator(
                    [$attribute => $value],
                    [$attribute => $passwordRule]
                );

                if ($validator->fails()) {
                    $fail('The :attribute has appeared in a data breach. Please choose a different password.');
                    return;
                }
            } catch (\Exception $e) {
                // If HIBP check fails (network issue), log but don't block
                \Log::warning('HIBP password check failed', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Get the password requirements message for display to users.
     *
     * @return string
     */
    public static function getRequirementsMessage(): string
    {
        return config('password.requirements_message', 
            'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.'
        );
    }

    /**
     * Get the Laravel Password rule configured from our settings.
     * 
     * @return Password
     */
    public static function getLaravelRule(): Password
    {
        $config = config('password');
        
        $rule = Password::min($config['min_length']);

        if ($config['require_mixed_case']) {
            $rule->mixedCase();
        }

        if ($config['require_numbers']) {
            $rule->numbers();
        }

        if ($config['require_symbols']) {
            $rule->symbols();
        }

        if ($config['check_uncompromised']) {
            $rule->uncompromised($config['uncompromised_threshold']);
        }

        return $rule;
    }
}
