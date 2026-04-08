<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Anyone can get a quote (even unauthenticated users)
    }

    public function rules(): array
    {
        return [
            // Trip Type
            'trip_type_id' => ['required', 'integer', 'exists:trip_types,id'],
            'trip_purpose' => ['sometimes', 'string', 'in:personal,professional,vulnerable'],

            // Route Information
            'pickup_address' => ['required', 'string', 'max:500'],
            'pickup_latitude' => ['required', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_address' => ['required', 'string', 'max:500'],
            'dropoff_latitude' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_longitude' => ['required', 'numeric', 'between:-180,180'],

            // Distance & Duration
            'estimated_distance_km' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'estimated_duration_min' => ['required', 'integer', 'min:1', 'max:1440'],

            // Vehicle (optional — one of id or name for "Autre")
            'vehicle_brand_id' => ['sometimes', 'nullable', 'integer', 'exists:vehicle_brands,id'],
            'vehicle_brand_name' => ['required_without:vehicle_brand_id', 'nullable', 'string', 'max:100'],
            'vehicle_model_id' => ['sometimes', 'nullable', 'integer', 'exists:vehicle_models,id'],
            'vehicle_model_name' => ['required_without:vehicle_model_id', 'nullable', 'string', 'max:100'],

            // Scheduling
            'scheduled_at' => ['sometimes', 'nullable', 'date', 'after:now'],
            'return_scheduled_at' => ['sometimes', 'nullable', 'date', 'after:scheduled_at'],

            // Additional Options
            'passenger_count' => ['sometimes', 'integer', 'min:1', 'max:8'],
            'is_round_trip' => ['sometimes', 'boolean'],
            'discount_code' => ['sometimes', 'nullable', 'string', 'max:50'],
            'customer_type' => ['sometimes', 'nullable', 'string', 'in:company,individual'],
            'guest_token' => ['sometimes', 'nullable', 'string', 'max:36'],
        ];
    }

    public function messages(): array
    {
        return [
            'trip_type_id.required' => 'Please select a trip type.',
            'trip_type_id.exists' => 'The selected trip type is invalid.',
            'pickup_latitude.required' => 'Pickup location is required.',
            'pickup_latitude.between' => 'Invalid pickup latitude coordinates.',
            'pickup_longitude.between' => 'Invalid pickup longitude coordinates.',
            'dropoff_latitude.required' => 'Dropoff location is required.',
            'dropoff_latitude.between' => 'Invalid dropoff latitude coordinates.',
            'dropoff_longitude.between' => 'Invalid dropoff longitude coordinates.',
            'estimated_distance_km.required' => 'Estimated distance is required for quote calculation.',
            'estimated_duration_min.required' => 'Estimated duration is required for quote calculation.',
            'scheduled_at.after' => 'Scheduled time must be in the future.',
        ];
    }
}
