<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CompanyController extends BaseController
{
    /**
     * Display a listing of companies.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Company::class);

        $companies = Company::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('legal_name', 'like', "%{$search}%")
                        ->orWhere('trade_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return CompanyResource::collection($companies);
    }

    /**
     * Display the specified company.
     */
    public function show(string $uuid): CompanyResource
    {
        $company = Company::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $company);

        return new CompanyResource($company->load(['driverProfiles', 'vehicles']));
    }

    /**
     * Store a newly created company.
     */
    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $this->authorize('create', Company::class);

        DB::beginTransaction();
        try {
            $company = Company::create($request->validated());

            DB::commit();

            return $this->sendResponse(
                new CompanyResource($company),
                'Société créée avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la création de la société.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified company.
     */
    public function update(UpdateCompanyRequest $request, string $uuid): JsonResponse
    {
        $company = Company::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $company);

        DB::beginTransaction();
        try {
            $company->update($request->validated());
            DB::commit();

            return $this->sendResponse(
                new CompanyResource($company),
                'Société mise à jour avec succès.'
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la mise à jour de la société.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified company.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $company = Company::where('uuid', $uuid)->firstOrFail();
        $this->authorize('delete', $company);

        DB::beginTransaction();
        try {
            $company->delete();
            DB::commit();

            return $this->sendResponse([], 'Société supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->sendError('Échec de la suppression de la société.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload company documents (KBIS, insurance, etc.).
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

        $company = Company::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $company);

        $fileStorageService = app(FileStorageService::class);

        $document = $fileStorageService->uploadDocument(
            $request->file('document'),
            $company,
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
                'company' => new CompanyResource($company),
            ],
            'Document téléchargé avec succès.'
        );
    }
}
