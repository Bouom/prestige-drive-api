<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    /**
     * Determine if the user can view any vehicles.
     */
    public function viewAny(User $user): bool
    {
        // Admins and drivers can view vehicles
        return $user->isAdmin() || $user->driverProfile()->exists();
    }

    /**
     * Determine if the user can view the vehicle.
     */
    public function view(User $user, Vehicle $vehicle): bool
    {
        // Admins can view any vehicle
        // Drivers can view their own vehicles
        // Company admins can view their company's vehicles
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user owns the vehicle (as driver)
        if ($user->driverProfile && $vehicle->current_driver_id === $user->driverProfile->id) {
            return true;
        }

        // Check if vehicle belongs to user's company
        if ($vehicle->company_id && $user->companies()->where('companies.id', $vehicle->company_id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create vehicles.
     */
    public function create(User $user): bool
    {
        // Drivers can add vehicles or admins
        // Company admins can add company vehicles
        return $user->isAdmin()
            || $user->driverProfile()->exists()
            || $user->companies()->exists();
    }

    /**
     * Determine if the user can update the vehicle.
     */
    public function update(User $user, Vehicle $vehicle): bool
    {
        // Admins can update any vehicle
        if ($user->isAdmin()) {
            return true;
        }

        // Driver can update their own vehicle
        if ($user->driverProfile && $vehicle->current_driver_id === $user->driverProfile->id) {
            return true;
        }

        // Company admin can update company vehicles
        if ($vehicle->company_id) {
            return $user->companies()
                ->where('companies.id', $vehicle->company_id)
                ->wherePivot('role', 'admin')
                ->exists();
        }

        return false;
    }

    /**
     * Determine if the user can delete the vehicle.
     */
    public function delete(User $user, Vehicle $vehicle): bool
    {
        // Same logic as update
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->driverProfile && $vehicle->current_driver_id === $user->driverProfile->id) {
            return true;
        }

        if ($vehicle->company_id) {
            return $user->companies()
                ->where('companies.id', $vehicle->company_id)
                ->wherePivot('role', 'admin')
                ->exists();
        }

        return false;
    }

    /**
     * Determine if the user can restore the vehicle.
     */
    public function restore(User $user, Vehicle $vehicle): bool
    {
        // Only admins can restore vehicles
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the vehicle.
     */
    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        // Only admins can force delete vehicles
        return $user->isAdmin();
    }

    /**
     * Determine if the user can verify the vehicle.
     */
    public function verify(User $user, Vehicle $vehicle): bool
    {
        // Only admins can verify vehicles
        return $user->isAdmin();
    }

    /**
     * Determine if the user can upload vehicle documents.
     */
    public function uploadDocuments(User $user, Vehicle $vehicle): bool
    {
        // Same as update - owner or company admin can upload documents
        return $this->update($user, $vehicle);
    }
}
