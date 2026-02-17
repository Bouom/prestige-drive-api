<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins with pricing management permission can update pricing rules
        return $this->user() && $this->user()->can('manage-pricing');
    }

    public function rules(): array
    {
        return [
            // Trip Type
            'trip_type_id' => ['sometimes', 'integer', 'exists:trip_types,id'],

            // Base Rates
            'base_price' => ['sometimes', 'numeric', 'min:0', 'max:9999.99'],
            'price_per_km' => ['sometimes', 'numeric', 'min:0', 'max:999.99'],
            'price_per_min' => ['sometimes', 'numeric', 'min:0', 'max:99.99'],

            // Minimum Charges
            'minimum_charge' => ['sometimes', 'numeric', 'min:0', 'max:9999.99'],

            // Distance-based Rules
            'free_distance_km' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:999.99'],
            'long_distance_threshold_km' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999.99'],
            'long_distance_multiplier' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:10'],

            // Time-based Surcharges
            'night_surcharge_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'night_start_hour' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:23'],
            'night_end_hour' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:23'],
            'weekend_surcharge_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'holiday_surcharge_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],

            // Platform Fees
            'platform_fee_percent' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'platform_fee_fixed' => ['sometimes', 'numeric', 'min:0', 'max:999.99'],

            // Cancellation Fees
            'cancellation_fee' => ['sometimes', 'numeric', 'min:0', 'max:999.99'],
            'cancellation_window_min' => ['sometimes', 'integer', 'min:0', 'max:1440'],

            // Return Trip
            'return_trip_discount_percent' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],

            // Status
            'is_active' => ['sometimes', 'boolean'],

            // Validity Period
            'valid_from' => ['sometimes', 'nullable', 'date'],
            'valid_until' => ['sometimes', 'nullable', 'date', 'after:valid_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'trip_type_id.exists' => 'The selected trip type is invalid.',
            'base_price.min' => 'Base price cannot be negative.',
            'base_price.max' => 'Base price exceeds maximum allowed.',
            'price_per_km.min' => 'Price per kilometer cannot be negative.',
            'price_per_min.min' => 'Price per minute cannot be negative.',
            'minimum_charge.min' => 'Minimum charge cannot be negative.',
            'long_distance_multiplier.max' => 'Long distance multiplier cannot exceed 10.',
            'night_surcharge_percent.max' => 'Night surcharge cannot exceed 100%.',
            'night_start_hour.min' => 'Night start hour must be between 0 and 23.',
            'night_start_hour.max' => 'Night start hour must be between 0 and 23.',
            'night_end_hour.min' => 'Night end hour must be between 0 and 23.',
            'night_end_hour.max' => 'Night end hour must be between 0 and 23.',
            'weekend_surcharge_percent.max' => 'Weekend surcharge cannot exceed 100%.',
            'holiday_surcharge_percent.max' => 'Holiday surcharge cannot exceed 100%.',
            'platform_fee_percent.max' => 'Platform fee percentage cannot exceed 100%.',
            'return_trip_discount_percent.max' => 'Return trip discount cannot exceed 100%.',
            'valid_until.after' => 'Valid until date must be after valid from date.',
        ];
    }
}
