<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
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
            'ride' => new RideResource($this->whenLoaded('ride')),
            'reviewer' => new UserResource($this->whenLoaded('reviewer')),
            'reviewee' => new UserResource($this->whenLoaded('reviewee')),
            'rating_overall' => number_format($this->rating_overall, 1),
            'rating_punctuality' => $this->rating_punctuality,
            'rating_professionalism' => $this->rating_professionalism,
            'rating_vehicle_cleanliness' => $this->rating_vehicle_cleanliness,
            'rating_driving' => $this->rating_driving,
            'rating_communication' => $this->rating_communication,
            'comment' => $this->comment,
            'response' => $this->response,
            'responded_at' => $this->responded_at?->toIso8601String(),
            'is_visible' => $this->is_visible,
            'is_verified' => $this->is_verified,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
