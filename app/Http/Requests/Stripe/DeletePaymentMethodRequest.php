<?php

declare(strict_types=1);

namespace App\Http\Requests\Stripe;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for deleting a payment method
 */
class DeletePaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->user_type === 'client';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'string', 'max:255'],
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
            'payment_method_id.required' => 'Payment method ID is required.',
            'payment_method_id.string' => 'Invalid payment method format.',
        ];
    }
}
