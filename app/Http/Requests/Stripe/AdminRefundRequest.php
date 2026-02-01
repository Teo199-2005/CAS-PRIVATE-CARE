<?php

declare(strict_types=1);

namespace App\Http\Requests\Stripe;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for admin refund operations
 */
class AdminRefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && in_array($user->user_type, ['admin', 'staff']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_intent_id' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'in:requested_by_customer,duplicate,fraudulent'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_intent_id.required' => 'Payment Intent ID is required.',
            'amount.numeric' => 'Refund amount must be a number.',
            'amount.min' => 'Refund amount must be at least $0.01.',
            'reason.in' => 'Invalid refund reason.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default reason if not provided
        if (!$this->has('reason')) {
            $this->merge(['reason' => 'requested_by_customer']);
        }
    }
}
