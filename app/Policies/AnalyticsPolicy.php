<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class AnalyticsPolicy
{
    /**
     * Determine whether the user can view pro stats.
     */
    public function viewProStats(User $user): bool
    {
        return $user->role === UserRole::PRO;
    }
}
