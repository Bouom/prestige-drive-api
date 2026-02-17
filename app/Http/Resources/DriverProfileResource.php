<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,

            // User Reference
            'user' => UserResource::make($this->whenLoaded('user')),

            // License Information
            'license' => [
                'number' => $this->license_number,
                'type' => $this->when($this->licenseType, [
                    'name' => $this->licenseType->name,
                    'display_name' => $this->licenseType->display_name,
                ]),
                'issue_date' => $this->license_issued_at?->format('Y-m-d'),
                'expiry_date' => $this->license_expires_at?->format('Y-m-d'),
                'is_valid' => $this->license_expires_at && $this->license_expires_at->isFuture(),
            ],

            // Professional Details
            'professional_card_number' => $this->professional_card_number,
            'professional_card_expiry' => null,
            'years_of_experience' => $this->years_experience,

            // Bank Information (only for owner or admin)
            'bank_details' => $this->when(
                $request->user() && ($request->user()->id === $this->user_id || $request->user()->isAdmin()),
                [
                    'iban' => $this->iban,
                    'bic' => $this->bic,
                    'account_holder_name' => $this->bank_account_holder,
                    'bank_name' => null,
                ]
            ),

            // Tax Information (only for owner or admin)
            'tax_info' => $this->when(
                $request->user() && ($request->user()->id === $this->user_id || $request->user()->isAdmin()),
                [
                    'siret_number' => null,
                    'vat_number' => null,
                ]
            ),

            // Availability
            'availability' => [
                'is_available' => $this->is_available,
                'is_on_duty' => $this->is_on_ride,
                'current_location' => $this->when($this->current_latitude && $this->current_longitude, [
                    'latitude' => (float) $this->current_latitude,
                    'longitude' => (float) $this->current_longitude,
                    'last_updated' => $this->location_updated_at?->toIso8601String(),
                ]),
            ],

            // Ratings & Statistics
            'statistics' => [
                'total_rides' => $this->total_rides,
                'completed_rides' => $this->completed_rides,
                'cancelled_rides' => $this->cancelled_rides,
                'average_rating' => 0.0,
                'total_ratings' => 0,
                'acceptance_rate' => (float) $this->acceptance_rate,
                'completion_rate' => $this->total_rides > 0
                    ? round(($this->completed_rides / $this->total_rides) * 100, 2)
                    : 0.0,
            ],

            // Earnings (only for owner or admin)
            'earnings' => $this->when(
                $request->user() && ($request->user()->id === $this->user_id || $request->user()->isAdmin()),
                [
                    'total_earnings' => (float) $this->total_earnings,
                    'pending_payout' => (float) $this->pending_payout,
                    'total_payouts' => 0.0,
                ]
            ),

            // Status
            'status' => $this->is_verified ? 'verified' : 'pending',
            'is_verified' => $this->is_verified,
            'is_background_checked' => false,
            'verification_notes' => $this->when(
                $request->user() && $request->user()->isAdmin(),
                null
            ),

            // Associated Company
            'company' => CompanyResource::make($this->whenLoaded('company')),

            // Vehicle
            'vehicle' => VehicleResource::make($this->whenLoaded('vehicle')),

            // Documents
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),

            // Timestamps
            'verified_at' => $this->verified_at?->toIso8601String(),
            'background_check_date' => null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
