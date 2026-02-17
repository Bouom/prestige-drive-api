<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRideRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated and own the ride or be admin
        $ride = $this->route('ride');

        return $this->user() && (
            $this->user()->id === $ride->customer_id ||
            $this->user()->isAdmin()
        );
    }

    public function rules(): array
    {
        return [
            // Only allow updating specific fields before ride is accepted
            'pickup_address' => ['sometimes', 'string', 'max:500'],
            'pickup_latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'dropoff_address' => ['sometimes', 'string', 'max:500'],
            'dropoff_latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'dropoff_longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'scheduled_at' => ['sometimes', 'date', 'after:now'],
            'passenger_count' => ['sometimes', 'integer', 'min:1', 'max:8'],
            'has_luggage' => ['sometimes', 'boolean'],
            'requires_child_seat' => ['sometimes', 'boolean'],
            'customer_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'return_scheduled_at' => ['sometimes', 'nullable', 'date', 'after:scheduled_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.after' => 'Ride must be scheduled for a future time.',
            'passenger_count.min' => 'At least one passenger is required.',
            'passenger_count.max' => 'Maximum 8 passengers allowed.',
            'pickup_latitude.between' => 'Invalid pickup latitude coordinates.',
            'pickup_longitude.between' => 'Invalid pickup longitude coordinates.',
            'dropoff_latitude.between' => 'Invalid dropoff latitude coordinates.',
            'dropoff_longitude.between' => 'Invalid dropoff longitude coordinates.',
        ];
    }
}
