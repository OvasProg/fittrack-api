<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can update the target user's role.
     */
    public function updateRole(User $user, User $targetUser): bool
    {
        return $user->id !== $targetUser->id && $user->role === UserRole::ADMIN;
    }
}
