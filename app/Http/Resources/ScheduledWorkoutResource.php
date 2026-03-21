<?php

namespace App\Http\Resources;

use App\Models\ScheduledWorkout;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ScheduledWorkout
 */
class ScheduledWorkoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'scheduled_workout_id' => $this->id,
            'training_id' => $this->training->id,
            'name' => $this->training->name,
            'difficulty' => $this->training->difficulty_level,
            'status' => $this->status,
        ];
    }
}
