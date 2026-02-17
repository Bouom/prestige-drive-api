<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NewsArticleResource extends JsonResource
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
            'uuid' => $this->slug,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image_url,
            'featured_image_url' => $this->featured_image_url,
            'author' => new UserResource($this->whenLoaded('author')),
            'category' => $this->category,
            'tags' => $this->tags ?? [],
            'status' => $this->status,
            'is_featured' => false,
            'views_count' => $this->view_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Relationships
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
