<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingResource extends JsonResource
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
            'difficulty_level' => $this->difficulty_level,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'exercises' => ExerciseResource::collection($this->whenLoaded('exercises')),
        ];
    }
}
