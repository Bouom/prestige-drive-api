<?php

namespace App\Http\Controllers\Api;

use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Stripe;

class PaymentMethodController extends BaseController
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Display a listing of payment methods.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $paymentMethods = PaymentMethod::where('user_id', $user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($paymentMethods, 'Moyens de paiement récupérés avec succès.');
    }

    /**
     * Store a newly created payment method.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = $request->user();

        DB::beginTransaction();
        try {
            $stripePaymentMethod = StripePaymentMethod::retrieve($request->payment_method_id);

            if (! $stripePaymentMethod->customer) {
                $stripePaymentMethod->attach([
                    'customer' => $user->stripe_customer_id,
                ]);
            }

            $isFirst = PaymentMethod::where('user_id', $user->id)->count() === 0;

            $paymentMethod = PaymentMethod::create([
                'user_id' => $user->id,
                'gateway' => 'stripe',
                'gateway_payment_method_id' => $stripePaymentMethod->id,
                'type' => $stripePaymentMethod->type,
                'card_brand' => $stripePaymentMethod->card->brand ?? null,
                'card_last_four' => $stripePaymentMethod->card->last4 ?? null,
                'card_exp_month' => $stripePaymentMethod->card->exp_month ?? null,
                'card_exp_year' => $stripePaymentMethod->card->exp_year ?? null,
                'is_default' => $isFirst,
            ]);

            DB::commit();

            return $this->sendResponse($paymentMethod, 'Moyen de paiement ajouté avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de l\'ajout du moyen de paiement.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'card_exp_month' => 'sometimes|integer|min:1|max:12',
            'card_exp_year' => 'sometimes|integer|min:'.date('Y'),
        ]);

        $paymentMethod = PaymentMethod::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethod->gateway_payment_method_id);
            $stripePaymentMethod->update([
                'card' => [
                    'exp_month' => $request->card_exp_month ?? $paymentMethod->card_exp_month,
                    'exp_year' => $request->card_exp_year ?? $paymentMethod->card_exp_year,
                ],
            ]);

            $paymentMethod->update([
                'card_exp_month' => $request->card_exp_month ?? $paymentMethod->card_exp_month,
                'card_exp_year' => $request->card_exp_year ?? $paymentMethod->card_exp_year,
            ]);

            DB::commit();

            return $this->sendResponse($paymentMethod, 'Moyen de paiement mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la mise à jour du moyen de paiement.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified payment method.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $paymentMethod = PaymentMethod::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethod->gateway_payment_method_id);
            $stripePaymentMethod->detach();

            if ($paymentMethod->is_default) {
                $newDefault = PaymentMethod::where('user_id', $request->user()->id)
                    ->where('id', '!=', $paymentMethod->id)
                    ->first();

                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }

            $paymentMethod->delete();

            DB::commit();

            return $this->sendResponse([], 'Moyen de paiement supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la suppression du moyen de paiement.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Set a payment method as default.
     */
    public function setDefault(Request $request, string $id): JsonResponse
    {
        $paymentMethod = PaymentMethod::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            PaymentMethod::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);

            $paymentMethod->update(['is_default' => true]);

            DB::commit();

            return $this->sendResponse($paymentMethod, 'Moyen de paiement par défaut mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la définition du moyen de paiement par défaut.', ['error' => $e->getMessage()], 500);
        }
    }
}
