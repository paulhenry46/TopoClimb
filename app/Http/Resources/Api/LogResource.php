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
            'comments' => $this->comment,
            'type' => $this->type,
            'way' => $this->way,
            'grade' => $this->grade,
            'created_at' => $this->created_at,
            'is_verified' => ($this->verified_by !== NULL),
            'user_name' => $this->whenLoaded('user', function () { return $this->user->name; }),
            'user_pp_url' => $this->whenLoaded('user', function () { return $this->user->profile_photo_url; }),
        ];
    }
}
