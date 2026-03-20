<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
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
            'admin' => $this->admin,
            'action' => $this->action,
            'details' => json_decode($this->details),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'human_readable_time' => $this->created_at->diffForHumans(),
        ];
    }
}
