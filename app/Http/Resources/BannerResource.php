<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
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
            'uuid' => (string) $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'description' => null,
            'image_path' => $this->image_url,
            'image_url' => $this->image_url,
            'link_url' => $this->cta_url,
            'link_text' => $this->cta_text,
            'position' => $this->placement,
            'order' => $this->sort_order,
            'is_active' => $this->is_active,
            'start_date' => $this->starts_at?->toIso8601String(),
            'end_date' => $this->ends_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
