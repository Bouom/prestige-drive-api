<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends BaseController
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->with('userType')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->type, fn ($query, $type) => $query->where('user_type_id', $type))
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return UserResource::collection($users);
    }

    /**
     * Display the specified user.
     */
    public function show(string $uuid): UserResource
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $this->authorize('view', $user);

        return new UserResource($user->load('userType'));
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateProfileRequest $request, string $uuid): JsonResponse
    {
        $user = User::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $user);

        $user->update($request->validated());

        return $this->sendResponse(
            new UserResource($user),
            'Utilisateur mis à jour avec succès.'
        );
    }

    /**
     * Upload user avatar.
     */
    public function uploadAvatar(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $user);

        // Delete old avatar if exists
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar_url' => $path]);

        return $this->sendResponse(
            new UserResource($user),
            'Avatar téléchargé avec succès.'
        );
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('uuid', $uuid)->firstOrFail();
        $this->authorize('update', $user);

        // Verify current password
        if (! Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Le mot de passe actuel est incorrect.', [], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return $this->sendResponse([], 'Mot de passe mis à jour avec succès.');
    }
}
