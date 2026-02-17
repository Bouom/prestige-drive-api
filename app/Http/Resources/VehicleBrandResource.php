<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleBrandResource extends JsonResource
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
            'logo_url' => $this->logo_url,
            'description' => $this->description,
            'country_of_origin' => $this->country_of_origin,
            'website' => $this->website,

            // Status
            'is_active' => $this->is_active,
            'is_premium' => $this->is_premium,

            // Statistics
            'models_count' => $this->when($this->models_count !== null, $this->models_count),
            'vehicles_count' => $this->when($this->vehicles_count !== null, $this->vehicles_count),

            // Relationships
            'models' => VehicleModelResource::collection($this->whenLoaded('models')),

            // Ordering
            'sort_order' => $this->sort_order,

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
