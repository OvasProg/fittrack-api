<?php

namespace App\Policies;

use App\Models\ScheduledWorkout;
use App\Models\User;

class ScheduledWorkoutPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ScheduledWorkout $scheduledWorkout): bool
    {
        return $user->id === $scheduledWorkout->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ScheduledWorkout $scheduledWorkout): bool
    {
        return $user->id === $scheduledWorkout->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ScheduledWorkout $scheduledWorkout): bool
    {
        return $user->id === $scheduledWorkout->user_id;
    }
}
