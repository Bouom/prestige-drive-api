<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\DriverProfileResource;
use App\Models\DriverProfile;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DriverVerificationController extends BaseController
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    /**
     * List drivers with optional verification filter.
     *
     * @tags Admin - Driver Verification
     *
     * @authenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = DriverProfile::query()
            ->with(['user', 'licenseType', 'documents']);

        $status = $request->input('is_verified', 'pending');
        if ($status === 'pending') {
            $query->where('is_verified', false);
        } elseif ($status === 'verified') {
            $query->where('is_verified', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $drivers = $query->orderBy('created_at', 'asc')
            ->paginate($request->input('per_page', 15));

        return DriverProfileResource::collection($drivers);
    }

    /**
     * Show driver detail.
     *
     * @tags Admin - Driver Verification
     *
     * @authenticated
     */
    public function show(string $uuid): JsonResponse
    {
        $driver = DriverProfile::whereHas('user', fn ($q) => $q->where('uuid', $uuid))
            ->with(['user', 'licenseType', 'documents', 'vehicle'])
            ->firstOrFail();

        return $this->sendResponse(
            new DriverProfileResource($driver),
            'Chauffeur récupéré avec succès.'
        );
    }

    /**
     * Approve a driver.
     */
    public function approve(Request $request, string $uuid): JsonResponse
    {
        $driver = DriverProfile::whereHas('user', fn ($q) => $q->where('uuid', $uuid))
            ->firstOrFail();

        $driver->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        $this->notificationService->sendDriverVerifiedNotification($driver->user);

        return $this->sendResponse(
            new DriverProfileResource($driver->load('user')),
            'Chauffeur approuvé avec succès.'
        );
    }

    /**
     * Reject a driver.
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $driver = DriverProfile::whereHas('user', fn ($q) => $q->where('uuid', $uuid))
            ->firstOrFail();

        $driver->update([
            'is_verified' => false,
            'metadata' => array_merge($driver->metadata ?? [], [
                'rejection_reason' => $request->reason,
                'rejected_at' => now()->toIso8601String(),
                'rejected_by' => $request->user()->id,
            ]),
        ]);

        return $this->sendResponse(
            new DriverProfileResource($driver->load('user')),
            'Chauffeur refusé.'
        );
    }
}
