<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RouteResource extends JsonResource
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
            'local_id' => $this->local_id,
            'line_id' => $this->line_id,
            'grade' => $this->grade,
            'color' => $this->colorToHex(),
            'comment' => $this->comment,
            'picture' => $this->picture(),
            'filtered_picture' => $this->filteredPicture(),
            'circle' => $this->circle(),
            'path_line' => $this->pathLine(),
            'thumbnail' => $this->thumbnail(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
