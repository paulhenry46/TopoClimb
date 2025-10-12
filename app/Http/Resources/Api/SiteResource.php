<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
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
            'slug' => $this->slug,
            'address' => $this->address,
            'description' => $this->description,
            'profile_picture' => $this->profile_picture(),
            'banner' => $this->banner(),
            'default_cotation' => $this->default_cotation,
            'grading_system' => $this->cotations(true),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
