<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends JsonResource
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
            'document_type' => new DocumentTypeResource($this->whenLoaded('documentType')),
            'file_name' => $this->file_name,
            'file_url' => $this->file_path ? Storage::disk('public')->url($this->file_path) : null,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'document_number' => $this->document_number,
            'issue_date' => $this->issue_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'is_verified' => $this->is_verified,
            'verifier' => new UserResource($this->whenLoaded('verifier')),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'verification_notes' => $this->verification_notes,
            'is_expired' => $this->expiry_date ? $this->expiry_date->isPast() : false,
            'expires_soon' => $this->expiry_date ? $this->expiry_date->diffInDays(now()) <= 30 : false,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
