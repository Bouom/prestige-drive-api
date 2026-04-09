<?php

namespace App\Http\Requests\Ride;

use Illuminate\Foundation\Http\FormRequest;

class StoreRideRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Trip Type
            'trip_type_id' => ['required', 'integer', 'exists:trip_types,id'],

            // Pickup Information
            'pickup_address' => ['required', 'string', 'max:500'],
            'pickup_latitude' => ['required', 'numeric', 'between:-90,90'],
            'pickup_longitude' => ['required', 'numeric', 'between:-180,180'],

            // Dropoff Information
            'dropoff_address' => ['required', 'string', 'max:500'],
            'dropoff_latitude' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_longitude' => ['required', 'numeric', 'between:-180,180'],

            // Timing (optional: if not set, the admin/team will confirm the schedule)
            'scheduled_at' => ['sometimes', 'nullable', 'date'],

            // Distance & Duration (from route calculation)
            'estimated_distance_km' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'estimated_duration_min' => ['required', 'integer', 'min:1', 'max:1440'],

            // Passengers
            'passenger_count' => ['required', 'integer', 'min:1', 'max:8'],
            'has_luggage' => ['sometimes', 'boolean'],
            'requires_child_seat' => ['sometimes', 'boolean'],

            // Round Trip
            'is_round_trip' => ['sometimes', 'boolean'],
            'return_scheduled_at' => ['required_if:is_round_trip,true', 'nullable', 'date'],

            // Company Booking
            'company_id' => ['sometimes', 'nullable', 'integer', 'exists:companies,id'],

            // Pricing (passed from quote, not recalculated here)
            'base_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'total_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'final_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],

            // Notes
            'customer_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],

            // Payment
            'payment_method_id' => ['sometimes', 'nullable', 'integer', 'exists:payment_methods,id'],
            'discount_code' => ['sometimes', 'nullable', 'string', 'max:50'],

            // Confirmation
            'requires_date_confirmation' => ['sometimes', 'boolean'],

            // Link to quote
            'quote_id' => ['sometimes', 'nullable', 'integer', 'exists:ride_quotes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'trip_type_id.required' => 'Please select a trip type.',
            'trip_type_id.exists' => 'The selected trip type is invalid.',
            'pickup_address.required' => 'Pickup address is required.',
            'dropoff_address.required' => 'Dropoff address is required.',
            'pickup_latitude.required' => 'Pickup location coordinates are required.',
            'pickup_latitude.between' => 'Invalid pickup latitude coordinates.',
            'pickup_longitude.between' => 'Invalid pickup longitude coordinates.',
            'dropoff_latitude.between' => 'Invalid dropoff latitude coordinates.',
            'dropoff_longitude.between' => 'Invalid dropoff longitude coordinates.',
            'scheduled_at.required' => 'Please specify when you need the ride.',
            'scheduled_at.after' => 'Ride must be scheduled for a future time.',
            'passenger_count.required' => 'Please specify the number of passengers.',
            'passenger_count.min' => 'At least one passenger is required.',
            'passenger_count.max' => 'Maximum 8 passengers allowed.',
            'return_scheduled_at.required_if' => 'Return time is required for round trips.',
            'return_scheduled_at.after' => 'Return time must be after pickup time.',
            'estimated_distance_km.required' => 'Estimated distance is required.',
            'estimated_duration_min.required' => 'Estimated duration is required.',
        ];
    }
}
