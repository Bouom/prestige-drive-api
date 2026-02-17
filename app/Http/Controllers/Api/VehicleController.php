<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VehicleResource;
use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class VehicleController extends BaseController
{
    /**
     * Display a listing of vehicles.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Vehicle::query()
            ->with(['vehicleModel.vehicleBrand', 'currentDriver.user', 'company']);

        if ($request->company_uuid) {
            $company = Company::where('uuid', $request->company_uuid)->first();
            if ($company) {
                $query->where('company_id', $company->id);
            }
        } elseif ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->model_id) {
            $query->where('vehicle_model_id', $request->model_id);
        }

        if ($request->available !== null) {
            $query->where('is_available', $request->boolean('available'));
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('license_plate', 'like', "%{$request->search}%")
                    ->orWhere('vin', 'like', "%{$request->search}%");
            });
        }

        // Only show own vehicles for drivers
        if ($request->user()->isDriver()) {
            $driverProfile = $request->user()->driverProfile;
            if ($driverProfile) {
                $query->where('current_driver_id', $driverProfile->id);
            }
        }

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(15);

        return VehicleResource::collection($vehicles);
    }

    /**
     * Display the specified vehicle.
     */
    public function show(string $uuid): VehicleResource
    {
        $vehicle = Vehicle::where('uuid', $uuid)
            ->with(['vehicleModel.vehicleBrand', 'currentDriver.user', 'company'])
            ->firstOrFail();

        $this->authorize('view', $vehicle);

        return new VehicleResource($vehicle);
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vehicle_model_id' => 'required|exists:vehicle_models,id',
            'company_id' => 'nullable|exists:companies,id',
            'company_uuid' => 'nullable|string|exists:companies,uuid',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'year' => 'required|integer|min:2000|max:'.(date('Y') + 1),
            'color' => 'required|string|max:50',
            'fuel_type' => 'sometimes|string|in:gasoline,diesel,electric,hybrid,plugin_hybrid',
            'transmission' => 'sometimes|string|in:automatic,manual',
            'passenger_capacity' => 'required|integer|min:1|max:8',
            'luggage_capacity' => 'sometimes|integer|min:0',
            'vehicle_class' => 'sometimes|string|in:standard,premium,luxury,van',
        ]);

        // Resolve company_uuid to company_id
        if (! empty($validated['company_uuid'])) {
            $company = Company::where('uuid', $validated['company_uuid'])->first();
            if ($company) {
                $validated['company_id'] = $company->id;
            }
            unset($validated['company_uuid']);
        }

        $this->authorize('create', Vehicle::class);

        DB::beginTransaction();
        try {
            $vehicle = Vehicle::create($validated);

            DB::commit();

            return $this->sendResponse(
                new VehicleResource($vehicle->load('vehicleModel.vehicleBrand')),
                'Véhicule créé avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la création du véhicule.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $vehicle = Vehicle::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'vehicle_model_id' => 'sometimes|exists:vehicle_models,id',
            'license_plate' => 'sometimes|string|max:20|unique:vehicles,license_plate,'.$vehicle->id,
            'year' => 'sometimes|integer|min:2000|max:'.(date('Y') + 1),
            'color' => 'sometimes|string|max:50',
            'fuel_type' => 'sometimes|string|in:gasoline,diesel,electric,hybrid,plugin_hybrid',
            'transmission' => 'sometimes|string|in:automatic,manual',
            'passenger_capacity' => 'sometimes|integer|min:1|max:8',
            'luggage_capacity' => 'sometimes|integer|min:0',
            'vehicle_class' => 'sometimes|string|in:standard,premium,luxury,van',
            'is_active' => 'sometimes|boolean',
            'is_available' => 'sometimes|boolean',
        ]);

        DB::beginTransaction();
        try {
            $vehicle->update($validated);
            DB::commit();

            return $this->sendResponse(
                new VehicleResource($vehicle->load('vehicleModel.vehicleBrand')),
                'Véhicule mis à jour avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la mise à jour du véhicule.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $vehicle = Vehicle::where('uuid', $uuid)->firstOrFail();
        $this->authorize('delete', $vehicle);

        DB::beginTransaction();
        try {
            // Check if vehicle has active rides
            if ($vehicle->rides()->whereIn('status', ['pending', 'confirmed', 'in_progress'])->exists()) {
                return $this->sendError('Impossible de supprimer un véhicule avec des courses actives.', [], 422);
            }

            $vehicle->delete();
            DB::commit();

            return $this->sendResponse([], 'Véhicule supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la suppression du véhicule.', ['error' => $e->getMessage()], 500);
        }
    }
}
