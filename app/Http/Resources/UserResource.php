<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'print_name' => $this->print_name,
            'username' => $this->username,
            'is_active' => $this->is_active,
            'doctor_id' => $this->doctor_id,
            'created_at' => $this->created_at->format('d.m.Y'),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ];
    }
}
