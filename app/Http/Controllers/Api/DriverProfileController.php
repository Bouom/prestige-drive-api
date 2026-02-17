<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Driver\RegisterDriverRequest;
use App\Http\Requests\Driver\UpdateDriverProfileRequest;
use App\Http\Resources\DriverProfileResource;
use App\Models\DriverProfile;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class DriverProfileController extends BaseController
{
    /**
     * Display a listing of drivers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = DriverProfile::query()
            ->with(['user', 'vehicle.vehicleModel.vehicleBrand', 'licenseType', 'company']);

        if ($request->available !== null) {
            $query->where('is_available', $request->boolean('available'));
        }

        if ($request->verified !== null) {
            $query->where('is_verified', $request->boolean('verified'));
        }

        $drivers = $query->orderBy('created_at', 'desc')->paginate(15);

        return DriverProfileResource::collection($drivers);
    }

    /**
     * Display the specified driver profile.
     */
    public function show(string $uuid): DriverProfileResource
    {
        $driver = DriverProfile::whereHas('user', fn ($q) => $q->where('uuid', $uuid))
            ->with(['user', 'vehicle.vehicleModel.vehicleBrand', 'licenseType', 'company'])
            ->firstOrFail();

        $this->authorize('view', $driver);

        return new DriverProfileResource($driver);
    }

    /**
     * Create a new driver profile.
     */
    public function store(RegisterDriverRequest $request): JsonResponse
    {
        $this->authorize('create', DriverProfile::class);

        DB::beginTransaction();
        try {
            $driver = DriverProfile::create(array_merge(
                $request->validated(),
                ['user_id' => $request->user()->id]
            ));

            DB::commit();

            return $this->sendResponse(
                new DriverProfileResource($driver->load(['user', 'licenseType'])),
                'Profil chauffeur créé avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la création du profil chauffeur.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the driver's profile.
     */
    public function update(UpdateDriverProfileRequest $request, string $uuid): JsonResponse
    {
        $driver = DriverProfile::whereHas('user', fn ($q) => $q->where('uuid', $uuid))
            ->firstOrFail();
        $this->authorize('update', $driver);

        DB::beginTransaction();
        try {
            $driver->update($request->validated());
            DB::commit();

            return $this->sendResponse(
                new DriverProfileResource($driver->load(['user', 'licenseType'])),
                'Profil chauffeur mis à jour avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la mise à jour du profil chauffeur.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload driver documents (license, insurance, etc.).
     */
    public function uploadDocument(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'document_type_id' => 'required|integer|exists:document_types,id',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'issued_at' => 'sometimes|nullable|date',
            'expires_at' => 'sometimes|nullable|date',
            'document_number' => 'sometimes|nullable|string|max:100',
        ]);

        $driver = DriverProfile::whereHas('user', fn ($q) => $q->where('uuid', $uuid))
            ->firstOrFail();
        $this->authorize('update', $driver);

        $fileStorageService = app(FileStorageService::class);

        $document = $fileStorageService->uploadDocument(
            $request->file('document'),
            $driver,
            $request->integer('document_type_id'),
            [
                'issued_at' => $request->input('issued_at'),
                'expires_at' => $request->input('expires_at'),
                'document_number' => $request->input('document_number'),
            ]
        );

        return $this->sendResponse(
            [
                'document' => $document,
                'driver' => new DriverProfileResource($driver),
            ],
            'Document téléchargé avec succès.'
        );
    }

    /**
     * Update driver availability status.
     */
    public function updateAvailability(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'available' => 'required|boolean',
        ]);

        $driver = DriverProfile::whereHas('user', fn ($q) => $q->where('uuid', $uuid))
            ->firstOrFail();
        $this->authorize('update', $driver);

        $driver->update([
            'is_available' => $request->boolean('available'),
        ]);

        return $this->sendResponse(
            new DriverProfileResource($driver),
            'Disponibilité mise à jour avec succès.'
        );
    }
}
