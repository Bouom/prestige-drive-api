<?php

namespace App\Policies;

use App\Models\DriverProfile;
use App\Models\User;

class DriverProfilePolicy
{
    /**
     * Determine if the user can view any driver profiles.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view driver listings
        return true;
    }

    /**
     * Determine if the user can view the driver profile.
     */
    public function view(User $user, DriverProfile $driverProfile): bool
    {
        // Anyone can view public driver profiles
        // Driver can view their own full profile
        // Admins can view any profile
        return $driverProfile->is_verified
            || $user->id === $driverProfile->user_id
            || $user->isAdmin();
    }

    /**
     * Determine if the user can create driver profiles.
     */
    public function create(User $user): bool
    {
        // User must be active and not already a driver
        return $user->is_active && ! $user->driverProfile()->exists();
    }

    /**
     * Determine if the user can update the driver profile.
     */
    public function update(User $user, DriverProfile $driverProfile): bool
    {
        // Driver can update their own profile or admins can update any
        return $user->id === $driverProfile->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can delete the driver profile.
     */
    public function delete(User $user, DriverProfile $driverProfile): bool
    {
        // Only admins can delete driver profiles
        return $user->isAdmin();
    }

    /**
     * Determine if the user can verify the driver.
     */
    public function verify(User $user, DriverProfile $driverProfile): bool
    {
        // Only admins can verify drivers
        return $user->isAdmin();
    }

    /**
     * Determine if the user can update availability.
     */
    public function updateAvailability(User $user, DriverProfile $driverProfile): bool
    {
        // Only the driver can toggle their own availability
        // Must be verified to be available
        return $user->id === $driverProfile->user_id && $driverProfile->is_verified;
    }

    /**
     * Determine if the user can upload documents.
     */
    public function uploadDocuments(User $user, DriverProfile $driverProfile): bool
    {
        // Driver can upload their own documents or admins
        return $user->id === $driverProfile->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can update location.
     */
    public function updateLocation(User $user, DriverProfile $driverProfile): bool
    {
        // Only the driver can update their own location
        return $user->id === $driverProfile->user_id && $driverProfile->is_available;
    }

    /**
     * Determine if the user can view earnings.
     */
    public function viewEarnings(User $user, DriverProfile $driverProfile): bool
    {
        // Driver can view their own earnings or admins
        return $user->id === $driverProfile->user_id || $user->isAdmin();
    }
}
