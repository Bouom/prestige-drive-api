<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RideResource extends JsonResource
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
            'booking_reference' => $this->booking_reference,

            // Customer
            'customer' => [
                'id' => $this->customer->uuid,
                'name' => $this->customer->full_name,
                'phone' => $this->customer->phone,
                'avatar' => $this->customer->avatar_url,
            ],

            // Driver (if assigned)
            'driver' => $this->when($this->driver, function () {
                return [
                    'id' => $this->driver->user->uuid,
                    'name' => $this->driver->user->full_name,
                    'phone' => $this->driver->user->phone,
                    'avatar' => $this->driver->user->avatar_url,
                    'license_number' => $this->driver->license_number,
                    'rating' => (float) $this->driver->user->average_rating,
                ];
            }),

            // Vehicle (if assigned)
            'vehicle' => $this->when($this->vehicle, function () {
                return [
                    'id' => $this->vehicle->uuid,
                    'brand' => $this->vehicle->vehicleModel->vehicleBrand->name,
                    'model' => $this->vehicle->vehicleModel->name,
                    'license_plate' => $this->vehicle->license_plate,
                    'color' => $this->vehicle->color,
                    'year' => $this->vehicle->year,
                ];
            }),

            // Trip Details
            'trip' => [
                'type' => [
                    'name' => $this->tripType->name,
                    'display_name' => $this->tripType->display_name,
                ],
                'pickup' => [
                    'address' => $this->pickup_address,
                    'coordinates' => [
                        'latitude' => (float) $this->pickup_latitude,
                        'longitude' => (float) $this->pickup_longitude,
                    ],
                ],
                'dropoff' => [
                    'address' => $this->dropoff_address,
                    'coordinates' => [
                        'latitude' => (float) $this->dropoff_latitude,
                        'longitude' => (float) $this->dropoff_longitude,
                    ],
                ],
                'waypoints' => RideWaypointResource::collection($this->whenLoaded('waypoints')),
            ],

            // Schedule & Timing
            'schedule' => [
                'scheduled_at' => $this->scheduled_at->toIso8601String(),
                'accepted_at' => $this->accepted_at?->toIso8601String(),
                'started_at' => $this->started_at?->toIso8601String(),
                'completed_at' => $this->completed_at?->toIso8601String(),
                'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            ],

            // Distance & Duration
            'metrics' => [
                'estimated_distance_km' => (float) $this->estimated_distance_km,
                'estimated_duration_min' => $this->estimated_duration_min,
                'actual_distance_km' => $this->when($this->actual_distance_km, (float) $this->actual_distance_km),
                'actual_duration_min' => $this->when($this->actual_duration_min, $this->actual_duration_min),
            ],

            // Passengers & Options
            'details' => [
                'passenger_count' => $this->passenger_count,
                'has_luggage' => $this->has_luggage,
                'requires_child_seat' => $this->requires_child_seat,
                'is_round_trip' => $this->is_round_trip,
                'return_scheduled_at' => $this->return_scheduled_at?->toIso8601String(),
            ],

            // Pricing
            'pricing' => [
                'base_price' => (float) $this->base_price,
                'return_fee' => (float) $this->return_fee,
                'surcharge' => (float) $this->surcharge,
                'discount_amount' => (float) $this->discount_amount,
                'discount_code' => $this->discount_code,
                'total_price' => (float) $this->total_price,
                'platform_fee' => (float) $this->platform_fee,
                'taxes' => (float) $this->taxes,
                'final_price' => (float) $this->final_price,
                'driver_earnings' => $this->when($this->driver_earnings, (float) $this->driver_earnings),
            ],

            // Status
            'status' => $this->status,
            'payment_status' => $this->payment_status,

            // Notes
            'notes' => $this->when($request->user(), [
                'customer_notes' => $this->customer_notes,
                'driver_notes' => $this->when($this->driver_notes && $request->user()->isDriver(), $this->driver_notes),
                'admin_notes' => $this->when($this->admin_notes && $request->user()->isAdmin(), $this->admin_notes),
                'cancellation_reason' => $this->cancellation_reason,
            ]),

            // Media
            'photos' => [
                'pickup' => $this->pickup_photo_url,
                'dropoff' => $this->dropoff_photo_url,
            ],

            // Review
            'review' => ReviewResource::make($this->whenLoaded('review')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
