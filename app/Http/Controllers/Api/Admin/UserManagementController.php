<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends BaseController
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->with(['userType', 'driverProfile', 'companies']);

        if ($request->filled('user_type_id')) {
            $query->where('user_type_id', $request->user_type_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('is_verified')) {
            if ($request->boolean('is_verified')) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate($request->input('per_page', 15));

        return UserResource::collection($users);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return $this->sendResponse(
            new UserResource($user->load(['userType', 'driverProfile', 'companies', 'rides', 'payments'])),
            'Utilisateur récupéré avec succès.'
        );
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['sometimes', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female,other,prefer_not_to_say',
            'language' => 'sometimes|string|size:2',
            'timezone' => 'sometimes|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return $this->sendResponse(
            new UserResource($user->load('userType')),
            'Utilisateur mis à jour avec succès.'
        );
    }

    /**
     * Activate a user.
     */
    public function activate(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update(['is_active' => true]);

        return $this->sendResponse(
            new UserResource($user),
            'Utilisateur activé avec succès.'
        );
    }

    /**
     * Deactivate a user.
     */
    public function deactivate(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return $this->sendResponse(
            new UserResource($user),
            'Utilisateur désactivé avec succès.'
        );
    }

    /**
     * Verify user's email.
     */
    public function verifyEmail(User $user): JsonResponse
    {
        $this->authorize('update', $user);

        if ($user->email_verified_at) {
            return $this->sendError('L\'adresse e-mail est déjà vérifiée.', [], 422);
        }

        $user->update(['email_verified_at' => now()]);

        return $this->sendResponse(
            new UserResource($user),
            'Adresse e-mail vérifiée avec succès.'
        );
    }

    /**
     * Reset user's password.
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $this->authorize('changePassword', $user);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();

        return $this->sendResponse([], 'Mot de passe réinitialisé avec succès.');
    }

    /**
     * Delete the specified user (soft delete).
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $activeRides = $user->rides()
            ->whereNotIn('status', ['completed', 'cancelled_by_customer', 'cancelled_by_driver', 'cancelled_by_admin'])
            ->count();

        if ($activeRides > 0) {
            return $this->sendError(
                'Impossible de supprimer un utilisateur avec des courses actives.',
                ['active_rides' => $activeRides],
                422
            );
        }

        $user->delete();

        return $this->sendResponse([], 'Utilisateur supprimé avec succès.');
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);

        $this->authorize('restore', $user);

        $user->restore();

        return $this->sendResponse(
            new UserResource($user),
            'Utilisateur restauré avec succès.'
        );
    }

    /**
     * Permanently delete a user.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);

        $this->authorize('forceDelete', $user);

        $user->forceDelete();

        return $this->sendResponse([], 'Utilisateur supprimé définitivement.');
    }

    /**
     * Get user activity summary.
     */
    public function activity(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $totalRides = $user->rides()->count();
        $completedRides = $user->rides()->where('status', 'completed')->count();
        $totalSpent = $user->payments()->where('status', 'completed')->sum('amount');
        $lastLogin = $user->last_login_at;
        $accountAge = $user->created_at->diffInDays(now());

        return $this->sendResponse([
            'user_id' => $user->id,
            'total_rides' => $totalRides,
            'completed_rides' => $completedRides,
            'cancelled_rides' => $user->rides()
                ->whereIn('status', ['cancelled_by_customer', 'cancelled_by_driver', 'cancelled_by_admin'])
                ->count(),
            'total_spent' => round($totalSpent, 2),
            'average_spent_per_ride' => $completedRides > 0 ? round($totalSpent / $completedRides, 2) : 0,
            'last_login' => $lastLogin?->toIso8601String(),
            'account_age_days' => $accountAge,
            'is_active' => $user->is_active,
            'is_verified' => ! is_null($user->email_verified_at),
        ], 'Activité de l\'utilisateur récupérée avec succès.');
    }
}
