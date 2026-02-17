<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine if the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view their payment history
        return true;
    }

    /**
     * Determine if the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        // User can view their own payments or admins can view any
        return $user->id === $payment->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can create payments.
     */
    public function create(User $user): bool
    {
        // All active users can make payments
        return $user->is_active;
    }

    /**
     * Determine if the user can update the payment.
     */
    public function update(User $user, Payment $payment): bool
    {
        // Only admins can update payments (e.g., mark as verified)
        return $user->isAdmin();
    }

    /**
     * Determine if the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        // Only admins can delete payments
        return $user->isAdmin();
    }

    /**
     * Determine if the user can request a refund.
     */
    public function refund(User $user, Payment $payment): bool
    {
        // User can request refund for their own payments
        // Payment must be completed and not already refunded
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $payment->user_id
            && $payment->status === 'completed'
            && ! $payment->refund()->exists();
    }

    /**
     * Determine if the user can view receipt.
     */
    public function viewReceipt(User $user, Payment $payment): bool
    {
        // User can view their own receipts or admins can view any
        return $user->id === $payment->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can process the payment.
     */
    public function process(User $user, Payment $payment): bool
    {
        // Only admins can manually process payments
        return $user->isAdmin();
    }
}
