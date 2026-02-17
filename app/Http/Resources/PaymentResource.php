<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $isAdmin = $request->user()?->isAdmin();

        return [
            'uuid' => $this->uuid,
            'amount' => number_format($this->amount, 2, '.', ''),
            'currency' => $this->currency,
            'payment_method' => new PaymentMethodResource($this->whenLoaded('paymentMethod')),
            'status' => $this->status,
            'description' => $this->description,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'failure_reason' => $this->failure_reason,
            'refunded' => $this->refunded,
            'refunded_amount' => $this->when($this->refunded, number_format($this->refunded_amount ?? 0, 2, '.', '')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // User info (admin only)
            'user' => $this->when($isAdmin && $this->relationLoaded('user'), function () {
                return $this->user ? [
                    'uuid' => $this->user->uuid,
                    'name' => $this->user->first_name.' '.$this->user->last_name,
                    'email' => $this->user->email,
                ] : null;
            }),

            // Admin-only fields
            'stripe_payment_intent_id' => $this->when($isAdmin, $this->stripe_payment_intent_id),
            'stripe_charge_id' => $this->when($isAdmin, $this->stripe_charge_id),
            'metadata' => $this->when($isAdmin, $this->metadata),

            // Relationships
            'payable' => $this->when($this->relationLoaded('payable'), function () {
                if ($this->payable_type === 'App\\Models\\Ride') {
                    return new RideResource($this->payable);
                }

                return $this->payable;
            }),
            'refunds' => RefundResource::collection($this->whenLoaded('refunds')),
        ];
    }
}
