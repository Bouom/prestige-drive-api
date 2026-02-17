<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TripTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'description' => $this->description,

            // Icon & Visual
            'icon' => $this->icon,
            'icon_url' => $this->icon_url,
            'color' => $this->color,
            'image_url' => $this->image_url,

            // Pricing Configuration
            'pricing' => [
                'base_rate' => (float) $this->base_rate,
                'per_km_rate' => (float) $this->per_km_rate,
                'per_minute_rate' => (float) $this->per_minute_rate,
                'minimum_fare' => (float) $this->minimum_fare,
                'booking_fee' => (float) $this->booking_fee,
                'cancellation_fee' => (float) $this->cancellation_fee,
            ],

            // Surcharges
            'surcharges' => [
                'night_rate_multiplier' => (float) $this->night_rate_multiplier,
                'peak_rate_multiplier' => (float) $this->peak_rate_multiplier,
                'weekend_rate_multiplier' => (float) $this->weekend_rate_multiplier,
                'holiday_rate_multiplier' => (float) $this->holiday_rate_multiplier,
            ],

            // Time Windows for Surcharges
            'night_hours' => $this->when($this->night_start_time, [
                'start' => $this->night_start_time,
                'end' => $this->night_end_time,
            ]),
            'peak_hours' => $this->when($this->peak_start_time, [
                'start' => $this->peak_start_time,
                'end' => $this->peak_end_time,
            ]),

            // Features & Requirements
            'features' => [
                'allows_waypoints' => $this->allows_waypoints,
                'max_waypoints' => $this->max_waypoints,
                'requires_advance_booking' => $this->requires_advance_booking,
                'min_advance_hours' => $this->min_advance_hours,
                'max_advance_days' => $this->max_advance_days,
                'supports_round_trip' => $this->supports_round_trip,
                'requires_driver_approval' => $this->requires_driver_approval,
            ],

            // Limits
            'limits' => [
                'max_distance_km' => $this->max_distance_km,
                'min_distance_km' => $this->min_distance_km,
                'max_duration_hours' => $this->max_duration_hours,
                'max_passengers' => $this->max_passengers,
            ],

            // Vehicle Requirements
            'vehicle_requirements' => [
                'allowed_categories' => $this->allowed_vehicle_categories,
                'min_vehicle_year' => $this->min_vehicle_year,
            ],

            // Availability
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'available_days' => $this->available_days,

            // Statistics
            'statistics' => [
                'total_rides' => $this->when($this->total_rides !== null, $this->total_rides),
                'average_rating' => $this->when($this->average_rating !== null, (float) $this->average_rating),
            ],

            // Display Order
            'sort_order' => $this->sort_order,

            // SEO
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
