<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class StartRideRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only the assigned driver can start the ride
        $ride = $this->route('ride');

        return $this->user() &&
               $this->user()->driverProfile &&
               $this->user()->driverProfile->id === $ride->driver_id;
    }

    public function rules(): array
    {
        return [
            'pickup_latitude' => ['required', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['required', 'numeric', 'between:-180,180'],
            'pickup_photo_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'driver_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_latitude.required' => 'Current location is required to start the ride.',
            'pickup_latitude.between' => 'Invalid latitude coordinates.',
            'pickup_longitude.required' => 'Current location is required to start the ride.',
            'pickup_longitude.between' => 'Invalid longitude coordinates.',
        ];
    }
}
