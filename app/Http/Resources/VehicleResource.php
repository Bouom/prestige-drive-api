<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $vehicleModel = $this->relationLoaded('vehicleModel') ? $this->vehicleModel : null;
        $brand = $vehicleModel?->relationLoaded('vehicleBrand') ? $vehicleModel->vehicleBrand : null;
        $features = is_array($this->features) ? $this->features : [];
        $photos = is_array($this->photos) ? $this->photos : [];

        return [
            'id' => $this->uuid,

            // Vehicle Identity
            'license_plate' => $this->license_plate,
            'vin' => $this->when(
                $request->user() && $request->user()->isAdmin(),
                $this->vin
            ),

            // Make & Model
            'brand' => $brand ? [
                'id' => $brand->id,
                'name' => $brand->name,
                'logo_url' => $brand->logo_url ?? null,
            ] : null,
            'model' => $vehicleModel ? [
                'id' => $vehicleModel->id,
                'name' => $vehicleModel->name,
            ] : null,
            'year' => $this->year,

            // Physical Attributes
            'color' => $this->color,
            'seats' => $this->passenger_capacity,
            'luggage_capacity' => $this->luggage_capacity,
            'category' => $this->vehicle_class,

            // Features & Options (stored as JSON)
            'features' => $features,

            // Technical Details
            'technical' => [
                'fuel_type' => $this->fuel_type,
                'transmission' => $this->transmission,
            ],

            // Mileage & Service
            'total_km' => $this->total_km,
            'last_service_date' => $this->last_maintenance_at?->format('Y-m-d'),
            'next_service_date' => $this->next_maintenance_at?->format('Y-m-d'),

            // Insurance
            'insurance' => [
                'company' => $this->insurance_company,
                'policy_number' => $this->insurance_policy_number,
                'expiry_date' => $this->insurance_expires_at?->format('Y-m-d'),
                'is_valid' => $this->insurance_expires_at && $this->insurance_expires_at->isFuture(),
            ],

            // Registration
            'registration_number' => $this->registration_number,

            // Ownership
            'company' => CompanyResource::make($this->whenLoaded('company')),

            // Status & Availability
            'status' => $this->current_status,
            'is_active' => $this->is_active,
            'is_available' => $this->is_available,

            // Media
            'photos' => [
                'primary' => $photos[0] ?? null,
                'gallery' => $photos,
            ],

            // Documents
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Metadata
            'metadata' => $this->when($this->metadata, $this->metadata),
        ];
    }
}
