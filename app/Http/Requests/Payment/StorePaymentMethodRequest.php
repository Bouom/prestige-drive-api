<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Stripe payment method ID
            'payment_method_id' => ['required', 'string', 'max:255'],

            // Payment method type
            'type' => ['required', 'string', 'in:card,bank_transfer,cash,wallet'],

            // Card details (if type is card)
            'card_brand' => ['required_if:type,card', 'nullable', 'string', 'max:50'],
            'card_last4' => ['required_if:type,card', 'nullable', 'string', 'size:4', 'regex:/^[0-9]{4}$/'],
            'card_exp_month' => ['required_if:type,card', 'nullable', 'integer', 'min:1', 'max:12'],
            'card_exp_year' => ['required_if:type,card', 'nullable', 'integer', 'min:2024', 'max:2099'],

            // Set as default
            'is_default' => ['sometimes', 'boolean'],

            // Billing details
            'billing_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'billing_email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            'billing_address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'billing_postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'billing_city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'billing_country' => ['sometimes', 'nullable', 'string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Payment method ID is required.',
            'type.required' => 'Payment method type is required.',
            'type.in' => 'Invalid payment method type.',
            'card_brand.required_if' => 'Card brand is required for card payments.',
            'card_last4.required_if' => 'Card last 4 digits are required.',
            'card_last4.size' => 'Card last 4 digits must be exactly 4 digits.',
            'card_last4.regex' => 'Card last 4 digits must be numeric.',
            'card_exp_month.required_if' => 'Card expiration month is required.',
            'card_exp_month.min' => 'Invalid expiration month.',
            'card_exp_month.max' => 'Invalid expiration month.',
            'card_exp_year.required_if' => 'Card expiration year is required.',
            'card_exp_year.min' => 'Card has expired or invalid year.',
            'billing_email.email' => 'Please provide a valid billing email.',
            'billing_country.size' => 'Country code must be 2 characters.',
        ];
    }
}
