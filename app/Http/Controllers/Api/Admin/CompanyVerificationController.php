<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyVerificationController extends BaseController
{
    public function __construct(
        private NotificationService $notificationService,
    ) {}

    /**
     * List companies with optional verification filter.
     *
     * @tags Admin - Company Verification
     *
     * @authenticated
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Company::query()
            ->with(['documents']);

        $status = $request->input('is_verified', 'pending');
        if ($status === 'pending') {
            $query->where('is_verified', false);
        } elseif ($status === 'verified') {
            $query->where('is_verified', true);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('legal_name', 'like', "%{$search}%")
                    ->orWhere('trade_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $companies = $query->orderBy('created_at', 'asc')
            ->paginate($request->input('per_page', 15));

        return CompanyResource::collection($companies);
    }

    /**
     * Show company detail.
     *
     * @tags Admin - Company Verification
     *
     * @authenticated
     */
    public function show(string $uuid): JsonResponse
    {
        $company = Company::where('uuid', $uuid)
            ->with(['documents', 'drivers.user', 'vehicles'])
            ->firstOrFail();

        return $this->sendResponse(
            new CompanyResource($company),
            'Société récupérée avec succès.'
        );
    }

    /**
     * Approve a company.
     */
    public function approve(Request $request, string $uuid): JsonResponse
    {
        $company = Company::where('uuid', $uuid)->firstOrFail();

        $company->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $request->user()->id,
        ]);

        $this->notificationService->sendCompanyVerifiedNotification($company);

        return $this->sendResponse(
            new CompanyResource($company),
            'Société approuvée avec succès.'
        );
    }

    /**
     * Reject a company.
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $company = Company::where('uuid', $uuid)->firstOrFail();

        $company->update([
            'is_verified' => false,
            'metadata' => array_merge($company->metadata ?? [], [
                'rejection_reason' => $request->reason,
                'rejected_at' => now()->toIso8601String(),
                'rejected_by' => $request->user()->id,
            ]),
        ]);

        return $this->sendResponse(
            new CompanyResource($company),
            'Société refusée.'
        );
    }
}
