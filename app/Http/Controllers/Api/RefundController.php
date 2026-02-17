<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\RefundResource;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Stripe\Refund as StripeRefund;
use Stripe\Stripe;

class RefundController extends BaseController
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Display a listing of refunds.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Refund::query()
            ->whereHas('payment', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['payment']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $refunds = $query->orderBy('created_at', 'desc')->paginate(15);

        return RefundResource::collection($refunds);
    }

    /**
     * Display the specified refund.
     */
    public function show(int $id): RefundResource
    {
        $refund = Refund::with(['payment'])->findOrFail($id);

        $this->authorize('view', $refund);

        return new RefundResource($refund);
    }

    /**
     * Store a newly created refund.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'payment_uuid' => 'required|string|exists:payments,uuid',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
            'type' => 'required|in:full,partial',
        ]);

        $payment = Payment::where('uuid', $request->payment_uuid)->firstOrFail();
        $this->authorize('refund', $payment);

        if ($payment->status !== 'succeeded') {
            return $this->sendError('Le paiement doit être complété pour demander un remboursement.', [], 422);
        }

        $totalRefunded = Refund::where('payment_id', $payment->id)
            ->where('status', 'succeeded')
            ->sum('amount');

        $maxRefundable = $payment->amount - $totalRefunded;

        if ($request->amount > $maxRefundable) {
            return $this->sendError("Le montant du remboursement ne peut pas dépasser {$maxRefundable}.", [], 422);
        }

        DB::beginTransaction();
        try {
            $stripePaymentIntentId = $payment->metadata['stripe_payment_intent_id'] ?? null;

            if (! $stripePaymentIntentId) {
                return $this->sendError('Aucune intention de paiement Stripe trouvée pour ce paiement.', [], 422);
            }

            $stripeRefund = StripeRefund::create([
                'payment_intent' => $stripePaymentIntentId,
                'amount' => $request->amount * 100,
                'reason' => 'requested_by_customer',
                'metadata' => [
                    'user_id' => $request->user()->id,
                    'payment_id' => $payment->id,
                ],
            ]);

            $refund = Refund::create([
                'payment_id' => $payment->id,
                'refund_id' => $stripeRefund->id,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'status' => $stripeRefund->status === 'succeeded' ? 'succeeded' : 'pending',
                'processed_at' => $stripeRefund->status === 'succeeded' ? now() : null,
                'processed_by' => $request->user()->id,
            ]);

            // Update payment status if fully refunded
            if ($request->type === 'full' || ($totalRefunded + $request->amount) >= $payment->amount) {
                $payment->update(['status' => 'refunded', 'refunded_at' => now()]);
            }

            DB::commit();

            return $this->sendResponse(
                new RefundResource($refund),
                'Remboursement traité avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec du traitement du remboursement.', ['error' => $e->getMessage()], 500);
        }
    }
}
