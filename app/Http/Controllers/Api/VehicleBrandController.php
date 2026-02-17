<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VehicleBrandResource;
use App\Models\TripType;
use App\Models\VehicleBrand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VehicleBrandController extends BaseController
{
    /**
     * Display a listing of vehicle brands.
     *
     * @unauthenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = VehicleBrand::query();

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->active !== null) {
            $query->where('is_active', $request->boolean('active'));
        }

        $brands = $query->orderBy('name', 'asc')->paginate((int) ($request->per_page ?? 15));

        return VehicleBrandResource::collection($brands);
    }

    /**
     * List all active trip types.
     *
     * @unauthenticated
     */
    public function tripTypes(): JsonResponse
    {
        $tripTypes = TripType::active()->ordered()->get(['id', 'name', 'display_name', 'description', 'icon', 'color']);

        return $this->sendResponse($tripTypes, 'Types de trajet récupérés avec succès.');
    }

    /**
     * Display the specified vehicle brand.
     *
     * @unauthenticated
     */
    public function show(string $id): VehicleBrandResource
    {
        $brand = VehicleBrand::with(['vehicleModels'])->findOrFail($id);

        return new VehicleBrandResource($brand);
    }
}
