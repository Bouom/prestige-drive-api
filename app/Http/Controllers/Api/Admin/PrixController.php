<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Prix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrixController extends BaseController
{
    /**
     * List all prix entries.
     *
     * @tags Admin - Prix
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $query = Prix::query()->orderBy('created_at', 'desc');

        if ($request->filled('active_only') && $request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        return $query->paginate($request->input('per_page', 15));
    }

    /**
     * Create a new prix entry.
     *
     * @tags Admin - Prix
     *
     * @authenticated
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01|max:9999.99',
        ]);

        $prix = Prix::create([
            'montant' => $validated['montant'],
            'is_active' => false,
        ]);

        return $this->sendResponse($prix, 'Prix créé avec succès.');
    }

    /**
     * Activate a prix entry (deactivates all others).
     *
     * @tags Admin - Prix
     *
     * @authenticated
     */
    public function activate(int $id): JsonResponse
    {
        $prix = Prix::findOrFail($id);

        $prix->activate();

        return $this->sendResponse($prix->fresh(), 'Prix activé avec succès.');
    }

    /**
     * Delete a prix entry.
     *
     * @tags Admin - Prix
     *
     * @authenticated
     */
    public function destroy(int $id): JsonResponse
    {
        $prix = Prix::findOrFail($id);

        if ($prix->is_active) {
            return $this->sendError('Impossible de supprimer le prix actif.', [], 422);
        }

        $prix->delete();

        return $this->sendResponse([], 'Prix supprimé avec succès.');
    }
}
