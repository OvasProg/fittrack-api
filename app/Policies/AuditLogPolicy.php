<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function viewAny(User $user): bool
    {
        return false; // Handled by before() for ADMIN
    }
}
