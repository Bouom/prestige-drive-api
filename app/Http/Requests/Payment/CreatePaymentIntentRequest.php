<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Ride or amount
            'ride_id' => ['required', 'integer', 'exists:rides,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'currency' => ['sometimes', 'string', 'size:3', 'in:EUR,USD,GBP'],

            // Payment Method
            'payment_method_id' => ['sometimes', 'nullable', 'integer', 'exists:payment_methods,id'],
            'save_payment_method' => ['sometimes', 'boolean'],

            // Metadata
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'ride_id.required' => 'Ride ID is required.',
            'ride_id.exists' => 'The specified ride does not exist.',
            'amount.required' => 'Payment amount is required.',
            'amount.min' => 'Payment amount must be at least 0.01.',
            'amount.max' => 'Payment amount exceeds maximum allowed.',
            'currency.in' => 'Currency must be EUR, USD, or GBP.',
            'currency.size' => 'Currency code must be 3 characters.',
            'payment_method_id.exists' => 'The selected payment method is invalid.',
        ];
    }
}
