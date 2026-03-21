<?php

namespace App\Policies;

use App\Models\User;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Handled by before() for ADMIN
    }
}
