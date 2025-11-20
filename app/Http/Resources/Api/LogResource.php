<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
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
            'route_id' => $this->route_id,
            'comment' => $this->comment,
            'type' => $this->type,
            'way' => $this->way,
            'grade' => $this->grade,
            'created_at' => $this->created_at,
            'is_verified' => ($this->verified_by !== null),
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'profile_photo_url' => $this->user->profile_photo_url,
                ];
            }),
        ];
    }
}
