<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'name' => $this->trade_name ?? $this->legal_name,
            'legal_name' => $this->legal_name,
            'trade_name' => $this->trade_name,

            // Contact Information
            'contact' => [
                'email' => $this->email,
                'phone' => $this->phone,
                'website' => $this->website,
            ],

            // Address
            'address' => [
                'street' => $this->address,
                'postal_code' => $this->postal_code,
                'city' => $this->city,
                'country' => $this->country,
            ],

            // Legal Information
            'legal_info' => [
                'registration_number' => $this->registration_number,
                'siret' => null,
                'vat_number' => $this->vat_number,
                'legal_form' => null,
            ],

            // Bank Information (only for authorized users)
            'bank_details' => $this->when(
                $request->user() && ($this->isOwner($request->user()) || $request->user()->isAdmin()),
                [
                    'iban' => $this->iban,
                    'bic' => $this->bic,
                    'account_holder_name' => null,
                    'bank_name' => null,
                ]
            ),

            // Billing Contact
            'billing_contact' => $this->when($this->billing_contact_name, [
                'name' => $this->billing_contact_name,
                'email' => $this->billing_contact_email,
                'phone' => $this->billing_contact_phone,
            ]),

            // Logo & Branding
            'logo_url' => $this->logo_url,
            'description' => $this->description,

            // Account Manager
            'account_manager' => UserResource::make($this->whenLoaded('accountManager')),

            // Statistics
            'statistics' => [
                'total_rides' => null,
                'active_drivers' => $this->active_drivers,
                'total_drivers' => $this->total_drivers,
                'total_vehicles' => $this->total_vehicles,
            ],

            // Balance (only for authorized users)
            'balance' => $this->when(
                $request->user() && ($this->isOwner($request->user()) || $request->user()->isAdmin()),
                0.0
            ),

            // Contract Information
            'contract' => $this->when($this->contract_start_date, [
                'start_date' => $this->contract_start_date?->format('Y-m-d'),
                'end_date' => $this->contract_end_date?->format('Y-m-d'),
                'type' => $this->contract_type,
            ]),

            // Status
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,

            // Relationships
            'drivers' => DriverProfileResource::collection($this->whenLoaded('drivers')),
            'vehicles' => VehicleResource::collection($this->whenLoaded('vehicles')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),

            // Timestamps
            'verified_at' => $this->verified_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Metadata
            'metadata' => $this->when($this->metadata, $this->metadata),
        ];
    }
}
