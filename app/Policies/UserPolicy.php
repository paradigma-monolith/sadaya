<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can list application accounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']) || $user->can('manage users');
    }

    /**
     * Determine whether the user can manage another account.
     */
    public function manage(User $user): bool
    {
        return $user->hasRole('Super Admin') || $user->can('manage users');
    }
}
