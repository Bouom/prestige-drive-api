<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RideWaypointResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,

            // Order in the route
            'order' => $this->order,
            'sequence_number' => $this->sequence_number,

            // Location
            'address' => $this->address,
            'coordinates' => [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ],

            // Location Details
            'place_name' => $this->place_name,
            'place_type' => $this->place_type,
            'postal_code' => $this->postal_code,
            'city' => $this->city,

            // Stop Information
            'stop_duration_minutes' => $this->stop_duration_minutes,
            'notes' => $this->notes,
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,

            // Arrival & Departure
            'estimated_arrival' => $this->estimated_arrival?->toIso8601String(),
            'actual_arrival' => $this->actual_arrival?->toIso8601String(),
            'departure_time' => $this->departure_time?->toIso8601String(),

            // Status
            'status' => $this->status,
            'is_completed' => $this->is_completed,
            'completed_at' => $this->completed_at?->toIso8601String(),

            // Distance from previous waypoint
            'distance_from_previous_km' => $this->when($this->distance_from_previous_km,
                (float) $this->distance_from_previous_km
            ),
            'duration_from_previous_min' => $this->when($this->duration_from_previous_min,
                $this->duration_from_previous_min
            ),

            // Photos
            'photo_url' => $this->photo_url,

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
