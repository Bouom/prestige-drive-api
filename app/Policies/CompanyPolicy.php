<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine if the user can view any companies.
     */
    public function viewAny(User $user): bool
    {
        // Admins can view all, or company users can view their companies
        return $user->isAdmin() || $user->companies()->exists();
    }

    /**
     * Determine if the user can view the company.
     */
    public function view(User $user, Company $company): bool
    {
        // Admins can view any, or user is member of the company
        return $user->isAdmin() || $user->companies()->where('companies.id', $company->id)->exists();
    }

    /**
     * Determine if the user can create companies.
     */
    public function create(User $user): bool
    {
        // Active users can register a company
        return $user->is_active;
    }

    /**
     * Determine if the user can update the company.
     */
    public function update(User $user, Company $company): bool
    {
        // Admins can update any, or company admins can update their company
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user is company admin
        return $user->companies()
            ->where('companies.id', $company->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    /**
     * Determine if the user can delete the company.
     */
    public function delete(User $user, Company $company): bool
    {
        // Only system admins can delete companies
        return $user->isAdmin();
    }

    /**
     * Determine if the user can restore the company.
     */
    public function restore(User $user, Company $company): bool
    {
        // Only admins can restore companies
        return $user->isAdmin();
    }

    /**
     * Determine if the user can permanently delete the company.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        // Only admins can force delete companies
        return $user->isAdmin();
    }

    /**
     * Determine if the user can verify the company.
     */
    public function verify(User $user, Company $company): bool
    {
        // Only admins can verify companies
        return $user->isAdmin();
    }

    /**
     * Determine if the user can manage company users.
     */
    public function manageUsers(User $user, Company $company): bool
    {
        // Admins or company admins can manage users
        if ($user->isAdmin()) {
            return true;
        }

        return $user->companies()
            ->where('companies.id', $company->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    /**
     * Determine if the user can upload documents for the company.
     */
    public function uploadDocuments(User $user, Company $company): bool
    {
        // Admins or company members with admin role
        if ($user->isAdmin()) {
            return true;
        }

        return $user->companies()
            ->where('companies.id', $company->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }
}
