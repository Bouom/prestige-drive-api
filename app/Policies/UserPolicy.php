<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        // Only admins can list all users
        return $user->isAdmin();
    }

    /**
     * Determine if the user can view the specific user.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile or admins can view any
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        // Only admins can create users directly
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile or admins can update any
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admins can delete users (cannot delete self)
        return $user->isAdmin() && $user->id !== $model->id;
    }

    /**
     * Determine if the user can restore the user.
     */
    public function restore(User $user, User $model): bool
    {
        // Only admins can restore users
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only admins can force delete users
        return $user->isAdmin();
    }

    /**
     * Determine if the user can change password.
     */
    public function changePassword(User $user, User $model): bool
    {
        // Users can change their own password or admins can change any
        return $user->id === $model->id || $user->isAdmin();
    }

    /**
     * Determine if the user can upload avatar.
     */
    public function uploadAvatar(User $user, User $model): bool
    {
        // Users can upload their own avatar or admins can upload any
        return $user->id === $model->id || $user->isAdmin();
    }
}
