<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SectorResource;
use App\Models\Area;
use App\Models\Sector;

class SectorController extends Controller
{
    /**
     * Display a listing of the resource for an area.
     */
    public function index(Area $area)
    {
        $sectors = $area->sectors;
        return SectorResource::collection($sectors);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sector $sector)
    {
        return new SectorResource($sector);
    }
}
