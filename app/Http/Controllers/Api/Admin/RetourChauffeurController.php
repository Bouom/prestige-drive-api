<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\RetourChauffeur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RetourChauffeurController extends BaseController
{
    /**
     * List all retour chauffeur fee entries.
     *
     * @tags Admin - Retour Chauffeur
     *
     * @authenticated
     */
    public function index(Request $request)
    {
        $query = RetourChauffeur::query()->orderBy('created_at', 'desc');

        if ($request->filled('active_only') && $request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        return $query->paginate($request->input('per_page', 15));
    }

    /**
     * Create a new retour chauffeur fee entry.
     *
     * @tags Admin - Retour Chauffeur
     *
     * @authenticated
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0.01|max:99999.99',
        ]);

        $retour = RetourChauffeur::create([
            'montant' => $validated['montant'],
            'is_active' => false,
        ]);

        return $this->sendResponse($retour, 'Frais retour chauffeur créé avec succès.');
    }

    /**
     * Activate a retour chauffeur fee (deactivates all others).
     *
     * @tags Admin - Retour Chauffeur
     *
     * @authenticated
     */
    public function activate(int $id): JsonResponse
    {
        $retour = RetourChauffeur::findOrFail($id);

        $retour->activate();

        return $this->sendResponse($retour->fresh(), 'Frais retour chauffeur activé avec succès.');
    }

    /**
     * Delete a retour chauffeur fee entry.
     *
     * @tags Admin - Retour Chauffeur
     *
     * @authenticated
     */
    public function destroy(int $id): JsonResponse
    {
        $retour = RetourChauffeur::findOrFail($id);

        if ($retour->is_active) {
            return $this->sendError('Impossible de supprimer le frais retour chauffeur actif.', [], 422);
        }

        $retour->delete();

        return $this->sendResponse([], 'Frais retour chauffeur supprimé avec succès.');
    }
}
