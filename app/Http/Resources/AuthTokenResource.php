<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for authentication token responses.
 */
class AuthTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'email' => $this->resource['email'] ?? null,
            'token_type' => $this->resource['token_type'],
            'expires_in' => $this->resource['expires_in'],
            'token' => $this->resource['token'],
            'refresh_token' => $this->resource['refresh_token'],
            'client_type' => $this->resource['client_type'],
        ];
    }
}
