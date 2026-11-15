<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view audit log records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']) || $user->can('view audit logs');
    }

    /**
     * Determine whether the user can view a single audit log entry.
     */
    public function view(User $user, Activity $activity): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can export audit trail data.
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }
}
