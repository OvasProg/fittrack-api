<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'training_name' => $this->training ? $this->training->name : 'Custom Workout',
            'date' => $this->completed_at ? $this->completed_at->format('M d, Y') : null,
            'duration_minutes' => $this->started_at && $this->completed_at
                ? $this->started_at->diffInMinutes($this->completed_at)
                : 0,
        ];
    }
}
