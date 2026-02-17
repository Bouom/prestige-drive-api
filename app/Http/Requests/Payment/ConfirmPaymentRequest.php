<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'payment_intent_id' => ['required', 'string', 'max:255'],
            'payment_method_id' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_intent_id.required' => 'Payment intent ID is required.',
        ];
    }
}
