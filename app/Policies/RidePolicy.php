<?php

namespace App\Policies;

use App\Models\Ride;
use App\Models\User;

class RidePolicy
{
    /**
     * Determine if the user can view any rides.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view rides (filtered by controller)
        return true;
    }

    /**
     * Determine if the user can view the ride.
     */
    public function view(User $user, Ride $ride): bool
    {
        // User can view if they are the customer, driver, or admin
        return $user->id === $ride->customer_id
            || $user->id === $ride->driver?->user_id
            || $user->isAdmin();
    }

    /**
     * Determine if the user can create rides.
     */
    public function create(User $user): bool
    {
        // Any active user can create a ride
        return $user->is_active;
    }

    /**
     * Determine if the user can update the ride.
     */
    public function update(User $user, Ride $ride): bool
    {
        // Only admins can update rides
        // Or customer before driver is assigned
        return $user->isAdmin()
            || ($user->id === $ride->customer_id && is_null($ride->driver_id));
    }

    /**
     * Determine if the user can cancel the ride.
     */
    public function cancel(User $user, Ride $ride): bool
    {
        // Customer can cancel before ride starts
        // Driver can cancel before ride starts
        // Admin can cancel anytime
        if ($user->isAdmin()) {
            return true;
        }

        if (in_array($ride->status, ['completed', 'cancelled_by_customer', 'cancelled_by_driver', 'cancelled_by_admin'])) {
            return false;
        }

        return $user->id === $ride->customer_id
            || $user->id === $ride->driver?->user_id;
    }

    /**
     * Determine if the user can start the ride.
     */
    public function start(User $user, Ride $ride): bool
    {
        // Only assigned driver can start
        return $user->id === $ride->driver?->user_id
            && in_array($ride->status, ['accepted', 'arrived']);
    }

    /**
     * Determine if the user can complete the ride.
     */
    public function complete(User $user, Ride $ride): bool
    {
        // Only assigned driver can complete
        return $user->id === $ride->driver?->user_id
            && $ride->status === 'in_progress';
    }

    /**
     * Determine if the user can delete the ride.
     */
    public function delete(User $user, Ride $ride): bool
    {
        // Only admins can delete rides
        return $user->isAdmin();
    }

    /**
     * Determine if the user can assign a driver to the ride.
     */
    public function assignDriver(User $user, Ride $ride): bool
    {
        // Only admins can manually assign drivers
        return $user->isAdmin();
    }

    /**
     * Determine if the user can pay for the ride.
     */
    public function pay(User $user, Ride $ride): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Only the customer can pay, and only when payment is still pending
        return $user->id === $ride->customer_id
            && in_array($ride->payment_status, ['pending', 'unpaid', null]);
    }
}
