<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\PromoCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromoCodeController extends BaseController
{
    /**
     * Display a listing of promo codes.
     */
    public function index(Request $request)
    {
        $query = PromoCode::with('creator');

        // Filter by active status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search by code or description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $promoCodes = $query->orderBy('created_at', 'desc')->paginate(15);

        return $this->sendResponse($promoCodes, 'Promo codes retrieved successfully.');
    }

    /**
     * Store a newly created promo code.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes,code'],
            'description' => ['nullable', 'string', 'max:255'],
            'discounts' => ['required', 'array', 'min:1'],
            'discounts.*.discount_type' => ['required', 'in:percentage,fixed'],
            'discounts.*.discount_value' => ['required', 'numeric', 'min:0'],
            'target_type' => ['required', 'in:company,individual,all,corporate'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ]);

        // For now, take the first discount (can be extended later for multiple)
        $firstDiscount = $request->discounts[0];
        $discountType = $firstDiscount['discount_type'];
        $discountValue = $firstDiscount['discount_value'];

        // Validate discount value based on type
        if ($discountType === 'percentage' && $discountValue > 100) {
            return $this->sendError('Percentage discount cannot exceed 100%.', [], 422);
        }

        $promoCode = PromoCode::create([
            'code' => strtoupper($request->code),
            'description' => $request->description,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'target_type' => $request->target_type === 'corporate' ? 'company' : $request->target_type,
            'starts_at' => $request->start_date,
            'max_uses' => $request->usage_limit,
            'is_active' => true,
            'expires_at' => $request->end_date,
            'created_by' => auth('api')->id(),
        ]);

        return $this->sendResponse($promoCode->load('creator'), 'Promo code created successfully.', 201);
    }

    /**
     * Display the specified promo code.
     */
    public function show(PromoCode $promoCode)
    {
        return $this->sendResponse($promoCode->load('creator'), 'Promo code retrieved successfully.');
    }

    /**
     * Update the specified promo code.
     */
    public function update(Request $request, PromoCode $promoCode)
    {
        $request->validate([
            'description' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['sometimes', 'in:percentage,fixed'],
            'discount_value' => ['sometimes', 'numeric', 'min:0'],
            'target_type' => ['sometimes', 'in:company,individual,all'],
            'starts_at' => ['nullable', 'date'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        // Validate discount value based on type
        if ($request->has('discount_type') || $request->has('discount_value')) {
            $discountType = $request->discount_type ?? $promoCode->discount_type;
            $discountValue = $request->discount_value ?? $promoCode->discount_value;

            if ($discountType === 'percentage' && $discountValue > 100) {
                return $this->sendError('Percentage discount cannot exceed 100%.', [], 422);
            }
        }

        $promoCode->update($request->only([
            'description',
            'discount_type',
            'discount_value',
            'target_type',
            'starts_at',
            'max_uses',
            'expires_at',
        ]));

        return $this->sendResponse($promoCode->load('creator'), 'Promo code updated successfully.');
    }

    /**
     * Activate the specified promo code.
     */
    public function activate(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => true]);

        return $this->sendResponse($promoCode, 'Promo code activated successfully.');
    }

    /**
     * Deactivate the specified promo code.
     */
    public function deactivate(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => false]);

        return $this->sendResponse($promoCode, 'Promo code deactivated successfully.');
    }

    /**
     * Remove the specified promo code.
     */
    public function destroy(PromoCode $promoCode)
    {
        // Check if promo code has been used
        if ($promoCode->used_count > 0) {
            return $this->sendError('Cannot delete promo code that has been used.', [], 422);
        }

        $promoCode->delete();

        return $this->sendResponse(null, 'Promo code deleted successfully.');
    }
}
