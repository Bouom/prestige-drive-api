<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\PricingRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingRuleController extends BaseController
{
    /**
     * Display a listing of pricing rules.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PricingRule::query()->with('tripType');

        if ($request->boolean('active_only')) {
            $query->active();
        }

        if ($request->filled('trip_type_id')) {
            $query->where('trip_type_id', $request->trip_type_id);
        }

        $rules = $query->orderBy('created_at', 'desc')->paginate(15);

        return JsonResource::collection($rules);
    }

    /**
     * Display the specified pricing rule.
     */
    public function show(int $id): JsonResource
    {
        $rule = PricingRule::with('tripType')->findOrFail($id);

        return new JsonResource($rule);
    }

    /**
     * Store a newly created pricing rule.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'trip_type_id' => 'nullable|exists:trip_types,id',
            'base_rate_per_km' => 'required|numeric|min:0',
            'minimum_fare' => 'required|numeric|min:0',
            'base_fee' => 'sometimes|numeric|min:0',
            'distance_tiers' => 'sometimes|array',
            'time_multipliers' => 'sometimes|array',
            'surge_enabled' => 'sometimes|boolean',
            'max_surge_multiplier' => 'sometimes|numeric|min:1',
            'return_fee_base' => 'sometimes|numeric|min:0',
            'return_fee_per_km' => 'sometimes|numeric|min:0',
            'platform_commission_rate' => 'required|numeric|min:0|max:100',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $rule = PricingRule::create($validated);

        return $this->sendResponse(
            new JsonResource($rule->load('tripType')),
            'Règle de tarification créée avec succès.'
        );
    }

    /**
     * Update the specified pricing rule.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $rule = PricingRule::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'trip_type_id' => 'nullable|exists:trip_types,id',
            'base_rate_per_km' => 'sometimes|numeric|min:0',
            'minimum_fare' => 'sometimes|numeric|min:0',
            'base_fee' => 'sometimes|numeric|min:0',
            'distance_tiers' => 'sometimes|array',
            'time_multipliers' => 'sometimes|array',
            'surge_enabled' => 'sometimes|boolean',
            'max_surge_multiplier' => 'sometimes|numeric|min:1',
            'return_fee_base' => 'sometimes|numeric|min:0',
            'return_fee_per_km' => 'sometimes|numeric|min:0',
            'platform_commission_rate' => 'sometimes|numeric|min:0|max:100',
            'valid_from' => 'sometimes|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        $rule->update($validated);

        return $this->sendResponse(
            new JsonResource($rule->load('tripType')),
            'Règle de tarification mise à jour avec succès.'
        );
    }

    /**
     * Remove the specified pricing rule.
     */
    public function destroy(int $id): JsonResponse
    {
        $rule = PricingRule::findOrFail($id);

        $rule->delete();

        return $this->sendResponse([], 'Règle de tarification supprimée avec succès.');
    }
}
