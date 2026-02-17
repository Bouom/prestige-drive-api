<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleModelResource extends JsonResource
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

            // Brand
            'brand' => $this->when($this->vehicleBrand, [
                'id' => $this->vehicleBrand->id,
                'name' => $this->vehicleBrand->name,
                'logo_url' => $this->vehicleBrand->logo_url,
            ]),

            // Model Details
            'full_name' => $this->full_name,
            'category' => $this->category,
            'body_type' => $this->body_type,
            'seats' => $this->seats,
            'doors' => $this->doors,

            // Technical Specifications
            'fuel_types' => $this->fuel_types,
            'transmission_types' => $this->transmission_types,
            'engine_options' => $this->engine_options,

            // Production Years
            'production_start_year' => $this->production_start_year,
            'production_end_year' => $this->production_end_year,
            'is_current' => $this->is_current,

            // Media
            'image_url' => $this->image_url,
            'thumbnail_url' => $this->thumbnail_url,

            // Description
            'description' => $this->description,
            'features' => $this->features,

            // Status
            'is_active' => $this->is_active,

            // Statistics
            'vehicles_count' => $this->when($this->vehicles_count !== null, $this->vehicles_count),

            // Ordering
            'sort_order' => $this->sort_order,

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
