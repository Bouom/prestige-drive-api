<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Ride;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends BaseController
{
    public function __construct()
    {
        $sandbox = config('services.stripe.sandbox');
        $apiKey = $sandbox
            ? config('services.stripe.test_secret', config('services.stripe.secret'))
            : config('services.stripe.secret');
        Stripe::setApiKey($apiKey);
    }

    /**
     * Display a listing of payments.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Payment::query()->with(['user', 'payable']);

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(15);

        return PaymentResource::collection($payments);
    }

    /**
     * Display the specified payment.
     */
    public function show(string $uuid): PaymentResource
    {
        $payment = Payment::where('uuid', $uuid)
            ->with(['user', 'payable'])
            ->firstOrFail();

        $this->authorize('view', $payment);

        return new PaymentResource($payment);
    }

    /**
     * Create a payment intent for a ride.
     */
    public function createIntent(Request $request): JsonResponse
    {
        $request->validate([
            'ride_uuid' => 'required|string|exists:rides,uuid',
            'amount' => 'required|numeric|min:1',
            'currency' => 'string|in:eur,usd',
        ]);

        $ride = Ride::where('uuid', $request->ride_uuid)->firstOrFail();
        $this->authorize('pay', $ride);

        DB::beginTransaction();
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100,
                'currency' => $request->currency ?? 'eur',
                'metadata' => [
                    'ride_id' => $ride->id,
                    'user_id' => $request->user()->id,
                ],
                'payment_method_types' => ['card'],
            ]);

            $payment = Payment::create([
                'payable_type' => Ride::class,
                'payable_id' => $ride->id,
                'user_id' => $request->user()->id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'eur',
                'status' => 'pending',
                'gateway' => 'stripe',
                'metadata' => [
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'stripe_client_secret' => $paymentIntent->client_secret,
                ],
            ]);

            DB::commit();

            return $this->sendResponse([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'payment' => new PaymentResource($payment),
            ], 'Intention de paiement créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la création de l\'intention de paiement.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Confirm a payment.
     */
    public function confirm(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            $payment = Payment::whereJsonContains('metadata->stripe_payment_intent_id', $request->payment_intent_id)
                ->firstOrFail();

            $this->authorize('view', $payment);

            if ($paymentIntent->status === 'succeeded') {
                $payment->update([
                    'status' => 'succeeded',
                    'captured_at' => now(),
                ]);

                // Update ride payment status
                if ($payment->payable instanceof Ride) {
                    $payment->payable->update(['payment_status' => 'paid']);
                }

                DB::commit();

                return $this->sendResponse(
                    new PaymentResource($payment),
                    'Paiement confirmé avec succès.'
                );
            } else {
                return $this->sendError('Paiement non complété.', ['status' => $paymentIntent->status], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la confirmation du paiement.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a Stripe Checkout Session for a ride.
     */
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $request->validate([
            'ride_uuid' => 'required|string|exists:rides,uuid',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        $ride = Ride::where('uuid', $request->ride_uuid)->firstOrFail();
        $this->authorize('pay', $ride);

        $amount = $ride->final_price ?? $ride->total_price ?? 0;
        if ($amount <= 0) {
            return $this->sendError('Montant de la course invalide.', [], 422);
        }

        DB::beginTransaction();
        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => (int) round($amount * 100),
                        'product_data' => [
                            'name' => 'Course VTC — '.($ride->booking_reference ?? $ride->uuid),
                            'description' => $ride->pickup_address.' → '.$ride->dropoff_address,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $request->success_url.'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $request->cancel_url,
                'metadata' => [
                    'ride_id' => $ride->id,
                    'ride_uuid' => $ride->uuid,
                    'user_id' => $request->user()->id,
                ],
            ]);

            $payment = Payment::create([
                'payable_type' => Ride::class,
                'payable_id' => $ride->id,
                'user_id' => $request->user()->id,
                'amount' => $amount,
                'currency' => 'eur',
                'status' => 'pending',
                'gateway' => 'stripe',
                'metadata' => [
                    'stripe_checkout_session_id' => $session->id,
                ],
            ]);

            DB::commit();

            return $this->sendResponse([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
                'payment' => new PaymentResource($payment),
            ], 'Session de paiement créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la création de la session de paiement.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify a Stripe Checkout Session after redirect.
     */
    public function verifyCheckoutSession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $session = \Stripe\Checkout\Session::retrieve($request->session_id);

            $payment = Payment::whereJsonContains('metadata->stripe_checkout_session_id', $request->session_id)->first();

            if (! $payment) {
                return $this->sendError('Session de paiement introuvable.', [], 404);
            }

            $this->authorize('view', $payment);

            if ($session->payment_status === 'paid') {
                $payment->update([
                    'status' => 'succeeded',
                    'captured_at' => now(),
                ]);

                if ($payment->payable instanceof Ride) {
                    $payment->payable->update(['payment_status' => 'paid']);
                }

                DB::commit();

                return $this->sendResponse([
                    'payment' => new PaymentResource($payment->fresh()),
                    'ride' => $payment->payable instanceof Ride
                        ? ['uuid' => $payment->payable->uuid, 'booking_reference' => $payment->payable->booking_reference]
                        : null,
                ], 'Paiement confirmé avec succès.');
            }

            DB::rollBack();

            return $this->sendError(
                'Paiement non complété.',
                ['status' => $session->payment_status],
                422
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la vérification du paiement.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get payment history for authenticated user.
     */
    public function history(Request $request): AnonymousResourceCollection
    {
        $payments = Payment::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'succeeded')
            ->with(['user', 'payable'])
            ->orderBy('captured_at', 'desc')
            ->paginate(15);

        return PaymentResource::collection($payments);
    }
}
