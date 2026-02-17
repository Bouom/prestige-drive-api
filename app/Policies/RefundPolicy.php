<?php

namespace App\Policies;

use App\Models\Refund;
use App\Models\User;

class RefundPolicy
{
    /**
     * Determine if the user can view any refunds.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view their refund history
        return true;
    }

    /**
     * Determine if the user can view the refund.
     */
    public function view(User $user, Refund $refund): bool
    {
        // User can view refunds for their own payments, or admins can view any
        return $user->id === $refund->payment->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can create refunds.
     */
    public function create(User $user): bool
    {
        // Only admins can create refunds directly
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete the refund.
     */
    public function delete(User $user, Refund $refund): bool
    {
        // Only admins can delete refunds
        return $user->isAdmin();
    }
}
