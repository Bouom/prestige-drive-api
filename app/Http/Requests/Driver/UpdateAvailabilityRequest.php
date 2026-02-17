<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated and have a driver profile
        return $this->user() && $this->user()->driverProfile;
    }

    public function rules(): array
    {
        return [
            'is_available' => ['required', 'boolean'],
            'current_latitude' => ['required_if:is_available,true', 'nullable', 'numeric', 'between:-90,90'],
            'current_longitude' => ['required_if:is_available,true', 'nullable', 'numeric', 'between:-180,180'],
            'heading' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:359'],
        ];
    }

    public function messages(): array
    {
        return [
            'is_available.required' => 'Availability status is required.',
            'current_latitude.required_if' => 'Current location is required when going online.',
            'current_latitude.between' => 'Invalid latitude coordinates.',
            'current_longitude.required_if' => 'Current location is required when going online.',
            'current_longitude.between' => 'Invalid longitude coordinates.',
            'heading.min' => 'Heading must be between 0 and 359 degrees.',
            'heading.max' => 'Heading must be between 0 and 359 degrees.',
        ];
    }
}
