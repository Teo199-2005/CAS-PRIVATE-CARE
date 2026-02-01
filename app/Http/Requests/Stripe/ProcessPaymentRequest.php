<?php

declare(strict_types=1);

namespace App\Http\Requests\Stripe;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request validation for processing payment with a payment method
 */
class ProcessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
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
            'booking_id.required' => 'Booking ID is required.',
            'booking_id.integer' => 'Booking ID must be a number.',
            'booking_id.exists' => 'Booking not found.',
        ];
    }
}
