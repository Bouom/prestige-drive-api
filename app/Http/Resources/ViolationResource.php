<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ViolationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'driver' => new UserResource($this->whenLoaded('driver')),
            'ride' => new RideResource($this->whenLoaded('ride')),
            'violation_type' => $this->violation_type,
            'description' => $this->description,
            'severity' => $this->severity,
            'fine_amount' => $this->fine_amount ? number_format($this->fine_amount, 2, '.', '') : null,
            'points' => $this->points,
            'status' => $this->status,
            'reported_by' => $this->reported_by,
            'reporter' => new UserResource($this->whenLoaded('reporter')),
            'reported_at' => $this->reported_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'resolution_notes' => $this->resolution_notes,
            'evidence' => $this->evidence,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Relationships
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
