<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource
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
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'amount' => number_format($this->amount, 2, '.', ''),
            'currency' => $this->currency,
            'reason' => $this->reason,
            'stripe_refund_id' => $this->when($isAdmin, $this->stripe_refund_id),
            'status' => $this->status,
            'processed_at' => $this->processed_at?->toIso8601String(),
            'failed_at' => $this->failed_at?->toIso8601String(),
            'failure_reason' => $this->failure_reason,
            'requested_by' => $this->requested_by,
            'requester' => new UserResource($this->whenLoaded('requester')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
