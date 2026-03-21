<?php

namespace App\Http\Resources;

use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Exercise
 */
class ExerciseResource extends JsonResource
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
            'name' => $this->name,
            'target_muscle' => $this->target_muscle,
            'base_multiplier' => $this->base_multiplier,
            // Extract pivot data ONLY when this exercise is loaded through a Training
            'default_sets' => $this->when(isset($this->pivot), function () {
                return $this->pivot->default_sets;
            }),
            'default_reps' => $this->when(isset($this->pivot), function () {
                return $this->pivot->default_reps;
            }),
        ];
    }
}
