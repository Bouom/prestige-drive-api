<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RideQuoteResource extends JsonResource
{
    private ?array $pricingDetails = null;

    public function __construct($resource, mixed $pricing = null)
    {
        parent::__construct($resource);
        $this->pricingDetails = is_array($pricing) ? $pricing : null;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trip_type' => $this->tripType ? [
                'id' => $this->tripType->id,
                'name' => $this->tripType->name,
                'display_name' => $this->tripType->display_name,
            ] : null,
            'trip_purpose' => $this->trip_purpose,

            'pickup' => [
                'address' => $this->pickup_address,
                'latitude' => (float) $this->pickup_latitude,
                'longitude' => (float) $this->pickup_longitude,
            ],
            'dropoff' => [
                'address' => $this->dropoff_address,
                'latitude' => (float) $this->dropoff_latitude,
                'longitude' => (float) $this->dropoff_longitude,
            ],

            'vehicle' => [
                'brand' => $this->vehicleBrand ? [
                    'id' => $this->vehicleBrand->id,
                    'name' => $this->vehicleBrand->name,
                ] : null,
                'model' => $this->vehicleModel ? [
                    'id' => $this->vehicleModel->id,
                    'name' => $this->vehicleModel->name,
                ] : null,
            ],

            'is_round_trip' => $this->is_round_trip,
            'passenger_count' => $this->passenger_count,
            'scheduled_at' => $this->scheduled_at,
            'return_scheduled_at' => $this->return_scheduled_at,

            'estimates' => [
                'distance_km' => (float) $this->estimated_distance_km,
                'duration_min' => $this->estimated_duration_min,
            ],

            'discount_code' => $this->discount_code,
            'discount_amount' => (float) $this->discount_amount,
            'customer_type' => $this->customer_type,
            'estimated_price' => (float) $this->estimated_price,
            'pricing' => $this->pricingDetails,
            'converted_to_ride_id' => $this->converted_to_ride_id,
            'is_converted' => $this->converted_to_ride_id !== null,
            'is_expired' => $this->expires_at && $this->expires_at->isPast(),
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}
