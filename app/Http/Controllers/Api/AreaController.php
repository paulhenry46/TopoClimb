<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AreaResource;
use App\Models\Area;
use App\Models\Site;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource for a site.
     */
    public function index(Site $site)
    {
        $areas = $site->areas;
        return AreaResource::collection($areas);
    }

    /**
     * Display the specified resource.
     */
    public function show(Area $area)
    {
        return new AreaResource($area);
    }
}
