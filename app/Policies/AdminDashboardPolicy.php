<?php

namespace App\Policies;

use App\Models\User;

class AdminDashboardPolicy
{
    /**
     * Determine whether the user can view the admin dashboard.
     */
    public function view(User $user): bool
    {
        return false; // Handled by before() for ADMIN
    }
}
