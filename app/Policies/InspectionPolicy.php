<?php

namespace App\Policies;

use App\Models\User;

class InspectionPolicy
{
    /**
     * Determine whether the user can inspect and read operational inspection modules.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Staff']) || $user->can('view products');
    }

    /**
     * Determine whether the user can verify or mutate inspection status.
     */
    public function verify(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Staff']) || $user->can('manage products');
    }

    /**
     * Determine whether the user can delete operational inspection records.
     */
    public function delete(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Staff']) || $user->can('manage products');
    }

    /**
     * Determine whether the user can export operational inspection datasets.
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Staff']) || $user->can('export data');
    }
}
