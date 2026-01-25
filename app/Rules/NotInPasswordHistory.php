<?php

namespace App\Rules;

use App\Services\PasswordHistoryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

/**
 * Not In Password History Rule
 * 
 * Validates that a new password has not been used recently.
 * Works with PasswordHistoryService to check last 5 passwords.
 */
class NotInPasswordHistory implements ValidationRule
{
    /**
     * The user to check password history for
     */
    protected $user;

    /**
     * Create a new rule instance.
     *
     * @param mixed $user Optional user, defaults to authenticated user
     */
    public function __construct($user = null)
    {
        $this->user = $user ?? Auth::user();
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->user) {
            return; // No user to check against
        }

        $historyService = new PasswordHistoryService();

        if ($historyService->wasUsedBefore($this->user, $value)) {
            $fail(PasswordHistoryService::getValidationMessage());
        }
    }
}
