<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VehicleModelResource;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VehicleModelController extends BaseController
{
    /**
     * Display a listing of vehicle models.
     *
     * @unauthenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = VehicleModel::query()->with(['vehicleBrand']);

        if ($request->brand_id) {
            $query->where('vehicle_brand_id', $request->brand_id);
        }

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->active !== null) {
            $query->where('is_active', $request->boolean('active'));
        }

        $models = $query->orderBy('name', 'asc')->paginate((int) ($request->per_page ?? 15));

        return VehicleModelResource::collection($models);
    }

    /**
     * Display the specified vehicle model.
     *
     * @unauthenticated
     */
    public function show(string $id): VehicleModelResource
    {
        $model = VehicleModel::with(['vehicleBrand'])->findOrFail($id);

        return new VehicleModelResource($model);
    }
}
