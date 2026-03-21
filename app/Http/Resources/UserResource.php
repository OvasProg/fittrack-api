<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role,
            'biometrics' => $this->when(isset($this->age), [
                'age' => $this->age,
                'weight' => $this->weight,
                'height' => $this->height,
                'experience_level' => $this->experience_level,
                'training_days' => $this->training_days,
            ]),
            'created_at' => $this->when(isset($this->created_at), function () {
                return $this->created_at->format('Y-m-d H:i:s');
            }),
            'deleted_at' => $this->when(isset($this->deleted_at), function () {
                return $this->deleted_at->format('Y-m-d H:i:s');
            }),
        ];
    }
}
