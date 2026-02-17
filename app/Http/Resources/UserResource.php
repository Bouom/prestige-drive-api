<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar_url' => $this->avatar_url,

            // User Type
            'user_type' => new UserTypeResource($this->whenLoaded('userType')),

            // Profile Info
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'language' => $this->language,
            'timezone' => $this->timezone,

            // Location
            'location' => [
                'address' => $this->address,
                'postal_code' => $this->postal_code,
                'city' => $this->city,
                'country' => $this->country,
                'coordinates' => $this->when($this->latitude && $this->longitude, [
                    'latitude' => (float) $this->latitude,
                    'longitude' => (float) $this->longitude,
                ]),
            ],

            // Ratings
            'ratings' => [
                'average' => (float) $this->average_rating,
                'total' => $this->total_ratings,
            ],

            // Status
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'email_verified' => ! is_null($this->email_verified_at),
            'phone_verified' => ! is_null($this->phone_verified_at),

            // Relationships (loaded conditionally)
            'driver_profile' => DriverProfileResource::make($this->whenLoaded('driverProfile')),
            'companies' => CompanyResource::collection($this->whenLoaded('companies')),

            // Timestamps
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Metadata
            'metadata' => $this->when($this->metadata, $this->metadata),
        ];
    }
}
