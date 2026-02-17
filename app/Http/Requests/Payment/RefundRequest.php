<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins can issue refunds
        return $this->user() && $this->user()->can('issue-refunds');
    }

    public function rules(): array
    {
        return [
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'amount' => ['sometimes', 'nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'reason' => ['required', 'string', 'in:duplicate,fraudulent,requested_by_customer,other'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_id.required' => 'Payment ID is required.',
            'payment_id.exists' => 'The specified payment does not exist.',
            'amount.min' => 'Refund amount must be at least 0.01.',
            'amount.max' => 'Refund amount exceeds maximum allowed.',
            'reason.required' => 'Refund reason is required.',
            'reason.in' => 'Invalid refund reason selected.',
        ];
    }
}
