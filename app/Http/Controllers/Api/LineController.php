<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\LineResource;
use App\Models\Line;
use App\Models\Sector;

class LineController extends Controller
{
    /**
     * Display a listing of the resource for a sector.
     */
    public function index(Sector $sector)
    {
        $lines = $sector->lines;
        return LineResource::collection($lines);
    }

    /**
     * Display the specified resource.
     */
    public function show(Line $line)
    {
        return new LineResource($line);
    }
}
