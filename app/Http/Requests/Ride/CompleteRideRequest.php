<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class CompleteRideRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only the assigned driver can complete the ride
        $ride = $this->route('ride');

        return $this->user() &&
               $this->user()->driverProfile &&
               $this->user()->driverProfile->id === $ride->driver_id;
    }

    public function rules(): array
    {
        return [
            'dropoff_latitude' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_longitude' => ['required', 'numeric', 'between:-180,180'],
            'actual_distance_km' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'actual_duration_min' => ['required', 'integer', 'min:1', 'max:1440'],
            'dropoff_photo_url' => ['sometimes', 'nullable', 'string', 'max:500', 'url'],
            'driver_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'dropoff_latitude.required' => 'Dropoff location is required to complete the ride.',
            'dropoff_latitude.between' => 'Invalid latitude coordinates.',
            'dropoff_longitude.required' => 'Dropoff location is required to complete the ride.',
            'dropoff_longitude.between' => 'Invalid longitude coordinates.',
            'actual_distance_km.required' => 'Actual distance traveled is required.',
            'actual_duration_min.required' => 'Actual ride duration is required.',
        ];
    }
}
