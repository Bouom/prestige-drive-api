<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class CancelRideRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Customer, assigned driver, or admin can cancel
        $ride = $this->route('ride');

        return $this->user() && (
            $this->user()->id === $ride->customer_id ||
            ($this->user()->driverProfile && $this->user()->driverProfile->id === $ride->driver_id) ||
            $this->user()->isAdmin()
        );
    }

    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'max:1000'],
            'cancel_type' => ['sometimes', 'in:customer,driver,admin,no_show'],
        ];
    }

    public function messages(): array
    {
        return [
            'cancellation_reason.required' => 'Please provide a reason for cancellation.',
            'cancellation_reason.max' => 'Cancellation reason must not exceed 1000 characters.',
            'cancel_type.in' => 'Invalid cancellation type.',
        ];
    }
}
