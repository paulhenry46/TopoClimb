<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AreaResource extends JsonResource
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
            'type' => $this->type,
            'site_id' => $this->site_id,
            'banner' => $this->banner(),
            'svg_schema' => $this->svgSchema(),
            'edited_svg_schema' => $this->editedSvgSchema(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
